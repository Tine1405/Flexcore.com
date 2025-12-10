<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php'; // required for $conn

// -------------------- HANDLE FORM SUBMISSIONS --------------------

// ADD MEMBER
if (isset($_POST['add_member'])) {
    $fullname = $_POST['fullname'];
    $membership_type = $_POST['membership_type'];
    $start_date = $_POST['start_date'];
    $expiry_date = $_POST['expiry_date'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("INSERT INTO members (fullname, membership_type, start_date, expiry_date, contact) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $fullname, $membership_type, $start_date, $expiry_date, $contact);
    $stmt->execute();
    $stmt->close();

    header("Location: Dashboard.php");
    exit;
}

// EDIT MEMBER
if (isset($_POST['edit_member'])) {
    $original_name = $_POST['original_name'];
    $fullname = $_POST['fullname'];
    $membership_type = $_POST['membership_type'];
    $start_date = $_POST['start_date'];
    $expiry_date = $_POST['expiry_date'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("UPDATE members SET fullname=?, membership_type=?, start_date=?, expiry_date=?, contact=? WHERE fullname=?");
    $stmt->bind_param("ssssss", $fullname, $membership_type, $start_date, $expiry_date, $contact, $original_name);
    $stmt->execute();
    $stmt->close();

    header("Location: Dashboard.php");
    exit;
}

// DELETE MEMBER
if (isset($_POST['delete_member'])) {
    $fullname = $_POST['fullname'];
    $stmt = $conn->prepare("DELETE FROM members WHERE fullname=?");
    $stmt->bind_param("s", $fullname);
    $stmt->execute();
    $stmt->close();

    header("Location: Dashboard.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Gym Registration Dashboard</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
/* HEADER */
.header { display:flex; justify-content:space-between; align-items:center; padding:20px; background:linear-gradient(to right,#000080,#000); color:white; }
.manage-btn { background:white; color:#000080; border:none; padding:8px 15px; border-radius:5px; font-weight:bold; cursor:pointer; }
.manage-btn:hover { background:#f1f1f1; }

/* SIDEBAR */
#sidebar { width:200px; background:#f1f1f1; height:100%; position:fixed; left:0; top:70px; overflow-y:auto; padding-top:10px; }
#sidebar a { display:block; color:black; padding:12px 20px; text-decoration:none; }
#sidebar a:hover { background:#444; color:white; }

/* MAIN CONTENT */
#content { margin-left:220px; padding:20px; }

/* RESPONSIVE */
#toggleBtn { display:none; background:#000080; color:white; padding:10px; border:none; font-size:18px; margin-left:10px; cursor:pointer; border-radius:5px; }
@media (max-width:768px){ #sidebar { left:-220px; } #sidebar.active{ left:0; } #content{ margin-left:0; } #toggleBtn{ display:inline-block; } }

/* POPUPS */
.popup-bg { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:none; justify-content:center; align-items:center; z-index:999; }
.popup-box { background:white; width:90%; max-width:450px; padding:20px; border-radius:10px; }
.close-btn { float:right; font-size:25px; cursor:pointer; }
</style>
</head>
<body>

<!-- HEADER -->
<div class="header">
    <div class="logo">FLEXCORE</div>
    <button id="toggleBtn" onclick="toggleSidebar()">☰ Menu</button>
    <div>
        <button class="manage-btn" onclick="document.getElementById('popup').style.display='flex'">ADD MEMBER</button>
        <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
    </div>
</div>

<!-- SIDEBAR -->
<div id="sidebar">
    <a href="dashboard.php">Home</a>
    <a href="trainer.php">trainer</a>
    <a href="announcement.php">Announcements</a>
    <a href="#">About</a>
</div>

<!-- MAIN CONTENT -->
<div id="content">
    <h2>Member List</h2>
    <div class="table-responsive mt-4">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th>Name</th>
                    <th>Membership Type</th>
                    <th>Status</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT * FROM members ORDER BY fullname ASC";
            $result = $conn->query($sql);

            if (!$result) {
                echo "<tr><td colspan='5' class='text-danger text-center'>SQL Error: {$conn->error}</td></tr>";
            } elseif ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $status = (strtotime($row['expiry_date']) >= time()) ?
                        "<span class='badge bg-success'>Active</span>" :
                        "<span class='badge bg-danger'>Expired</span>";
                    echo "<tr>
                        <td>{$row['fullname']}</td>
                        <td>{$row['membership_type']}</td>
                        <td class='text-center'>{$status}</td>
                        <td>{$row['contact']}</td>
                        <td class='text-center'>
                            <button class='btn btn-warning btn-sm'
                                onclick=\"openEditPopup(
                                    '{$row['fullname']}',
                                    '{$row['membership_type']}',
                                    '{$row['start_date']}',
                                    '{$row['expiry_date']}',
                                    '{$row['contact']}'
                                )\">Edit</button>
                            <button class='btn btn-danger btn-sm'
                                onclick=\"openDeletePopup('{$row['fullname']}')\">Delete</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>No members found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD MEMBER POPUP -->
<div class="popup-bg" id="popup">
    <div class="popup-box">
        <span class="close-btn" onclick="document.getElementById('popup').style.display='none'">&times;</span>
        <h3>Add Member</h3>
        <form method="POST">
            <input type="text" name="fullname" placeholder="Full Name" class="form-control mb-2" required>
            <select name="membership_type" class="form-select mb-2" required>
                <option value="Weekly">Weekly</option>
                <option value="Monthly">Monthly</option>
                <option value="Annual">Annual</option>
            </select>
            <input type="date" name="start_date" class="form-control mb-2" required>
            <input type="date" name="expiry_date" class="form-control mb-2" required>
            <input type="text" name="contact" placeholder="Contact Number" class="form-control mb-2" required>
            <button type="submit" name="add_member" class="btn btn-primary w-100">Add Member</button>
        </form>
    </div>
</div>

<!-- EDIT MEMBER POPUP -->
<div class="popup-bg" id="editPopup">
    <div class="popup-box">
        <span class="close-btn" onclick="document.getElementById('editPopup').style.display='none'">&times;</span>
        <h3>Edit Member</h3>
        <form method="POST">
            <input type="hidden" name="original_name" id="editOriginalName">
            <input type="text" name="fullname" id="editFullname" class="form-control mb-2" required>
            <select name="membership_type" id="editType" class="form-select mb-2" required>
                <option value="Weekly">Weekly</option>
                <option value="Monthly">Monthly</option>
                <option value="Annual">Annual</option>
            </select>
            <input type="date" name="start_date" id="editStart" class="form-control mb-2" required>
            <input type="date" name="expiry_date" id="editExpiry" class="form-control mb-2" required>
            <input type="text" name="contact" id="editContact" class="form-control mb-2" required>
            <button type="submit" name="edit_member" class="btn btn-primary w-100">Update Member</button>
        </form>
    </div>
</div>

<!-- DELETE MEMBER POPUP -->
<div class="popup-bg" id="deletePopup">
    <div class="popup-box text-center">
        <span class="close-btn" onclick="document.getElementById('deletePopup').style.display='none'">&times;</span>
        <h4>Delete <span id="deleteMemberName"></span>?</h4>
        <form method="POST">
            <input type="hidden" name="fullname" id="deleteFullname">
            <button type="submit" name="delete_member" class="btn btn-danger mt-2 me-2">Yes</button>
            <button type="button" class="btn btn-secondary mt-2" onclick="document.getElementById('deletePopup').style.display='none'">Cancel</button>
        </form>
    </div>
</div>

<script>
function toggleSidebar() { document.getElementById("sidebar").classList.toggle("active"); }
function openEditPopup(name,type,start,expiry,contact){
    document.getElementById('editPopup').style.display='flex';
    document.getElementById('editFullname').value=name;
    document.getElementById('editType').value=type;
    document.getElementById('editStart').value=start;
    document.getElementById('editExpiry').value=expiry;
    document.getElementById('editContact').value=contact;
    document.getElementById('editOriginalName').value=name;
}
function openDeletePopup(name){
    document.getElementById('deletePopup').style.display='flex';
    document.getElementById('deleteMemberName').innerText=name;
    document.getElementById('deleteFullname').value=name;
}
</script>

</body>
</html>

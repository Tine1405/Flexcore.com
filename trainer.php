<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php'; // required for $conn

// -------------------- HANDLE FORM SUBMISSIONS --------------------

// ADD TRAINER
if (isset($_POST['add_trainer'])) {
    $fullname = $_POST['fullname'];
    $specialty = $_POST['specialty'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("INSERT INTO trainers (fullname, specialty, contact) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $fullname, $specialty, $contact);
    $stmt->execute();
    $stmt->close();

    header("Location: trainer.php");
    exit;
}

// EDIT TRAINER
if (isset($_POST['edit_trainer'])) {
    $original_name = $_POST['original_name'];
    $fullname = $_POST['fullname'];
    $specialty = $_POST['specialty'];
    $contact = $_POST['contact'];

    $stmt = $conn->prepare("UPDATE trainers SET fullname=?, specialty=?, contact=? WHERE fullname=?");
    $stmt->bind_param("ssss", $fullname, $specialty, $contact, $original_name);
    $stmt->execute();
    $stmt->close();

    header("Location: trainer.php");
    exit;
}

// DELETE TRAINER
if (isset($_POST['delete_trainer'])) {
    $fullname = $_POST['fullname'];
    $stmt = $conn->prepare("DELETE FROM trainers WHERE fullname=?");
    $stmt->bind_param("s", $fullname);
    $stmt->execute();
    $stmt->close();

    header("Location: trainer.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Trainer Dashboard</title>

<!-- Bootstrap 5 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<style>
.header { display:flex; justify-content:space-between; align-items:center; padding:20px; background:linear-gradient(to right,#000080,#000); color:white; }
.manage-btn { background:white; color:#000080; border:none; padding:8px 15px; border-radius:5px; font-weight:bold; cursor:pointer; }
.manage-btn:hover { background:#f1f1f1; }
#sidebar { width:200px; background:#f1f1f1; height:100%; position:fixed; left:0; top:70px; overflow-y:auto; padding-top:10px; }
#sidebar a { display:block; color:black; padding:12px 20px; text-decoration:none; }
#sidebar a:hover { background:#444; color:white; }
#content { margin-left:220px; padding:20px; }
#toggleBtn { display:none; background:#000080; color:white; padding:10px; border:none; font-size:18px; margin-left:10px; cursor:pointer; border-radius:5px; }
@media (max-width:768px){ #sidebar { left:-220px; } #sidebar.active{ left:0; } #content{ margin-left:0; } #toggleBtn{ display:inline-block; } }
.popup-bg { position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); display:none; justify-content:center; align-items:center; z-index:999; }
.popup-box { background:white; width:90%; max-width:450px; padding:20px; border-radius:10px; }
.close-btn { float:right; font-size:25px; cursor:pointer; }
</style>
</head>
<body>

<div class="header">
    <div class="logo">FLEXCORE</div>
    <button id="toggleBtn" onclick="toggleSidebar()">☰ Menu</button>
    <div>
        <button class="manage-btn" onclick="document.getElementById('popup').style.display='flex'">ADD TRAINER</button>
        <a href="logout.php" class="btn btn-danger ms-2">Logout</a>
    </div>
</div>

<div id="sidebar">
    <a href="Dashboard.php">Home</a>
    <a href="trainer.php">Trainer</a>
    <a href="announcement.php">Announcements</a>
    <a href="#">About</a>
</div>

<div id="content">
    <h2>Trainer List</h2>
    <div class="table-responsive mt-4">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th>Name</th>
                    <th>Specialty</th>
                    <th>Contact</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT * FROM trainers ORDER BY fullname ASC";
            $result = $conn->query($sql);

            if (!$result) {
                echo "<tr><td colspan='4' class='text-danger text-center'>SQL Error: {$conn->error}</td></tr>";
            } elseif ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['fullname']}</td>
                        <td>{$row['specialty']}</td>
                        <td>{$row['contact']}</td>
                        <td class='text-center'>
                            <button class='btn btn-warning btn-sm'
                                onclick=\"openEditPopup('{$row['fullname']}','{$row['specialty']}','{$row['contact']}')\">Edit</button>
                            <button class='btn btn-danger btn-sm'
                                onclick=\"openDeletePopup('{$row['fullname']}')\">Delete</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No trainers found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD TRAINER POPUP -->
<div class="popup-bg" id="popup">
    <div class="popup-box">
        <span class="close-btn" onclick="document.getElementById('popup').style.display='none'">&times;</span>
        <h3>Add Trainer</h3>
        <form method="POST">
            <input type="text" name="fullname" placeholder="Full Name" class="form-control mb-2" required>
            <input type="text" name="specialty" placeholder="Specialty" class="form-control mb-2" required>
            <input type="text" name="contact" placeholder="Contact Number" class="form-control mb-2" required>
            <button type="submit" name="add_trainer" class="btn btn-primary w-100">Add Trainer</button>
        </form>
    </div>
</div>

<!-- EDIT TRAINER POPUP -->
<div class="popup-bg" id="editPopup">
    <div class="popup-box">
        <span class="close-btn" onclick="document.getElementById('editPopup').style.display='none'">&times;</span>
        <h3>Edit Trainer</h3>
        <form method="POST">
            <input type="hidden" name="original_name" id="editOriginalName">
            <input type="text" name="fullname" id="editFullname" class="form-control mb-2" required>
            <input type="text" name="specialty" id="editSpecialty" class="form-control mb-2" required>
            <input type="text" name="contact" id="editContact" class="form-control mb-2" required>
            <button type="submit" name="edit_trainer" class="btn btn-primary w-100">Update Trainer</button>
        </form>
    </div>
</div>

<!-- DELETE TRAINER POPUP -->
<div class="popup-bg" id="deletePopup">
    <div class="popup-box text-center">
        <span class="close-btn" onclick="document.getElementById('deletePopup').style.display='none'">&times;</span>
        <h4>Delete <span id="deleteTrainerName"></span>?</h4>
        <form method="POST">
            <input type="hidden" name="fullname" id="deleteFullname">
            <button type="submit" name="delete_trainer" class="btn btn-danger mt-2 me-2">Yes</button>
            <button type="button" class="btn btn-secondary mt-2" onclick="document.getElementById('deletePopup').style.display='none'">Cancel</button>
        </form>
    </div>
</div>

<script>
function toggleSidebar() { document.getElementById("sidebar").classList.toggle("active"); }
function openEditPopup(name,specialty,contact){
    document.getElementById('editPopup').style.display='flex';
    document.getElementById('editFullname').value=name;
    document.getElementById('editSpecialty').value=specialty;
    document.getElementById('editContact').value=contact;
    document.getElementById('editOriginalName').value=name;
}
function openDeletePopup(name){
    document.getElementById('deletePopup').style.display='flex';
    document.getElementById('deleteTrainerName').innerText=name;
    document.getElementById('deleteFullname').value=name;
}
</script>

</body>
</html>

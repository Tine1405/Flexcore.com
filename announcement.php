<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}

include 'db.php'; // required for $conn

// -------------------- HANDLE FORM SUBMISSIONS --------------------

// ADD ANNOUNCEMENT
if (isset($_POST['add_announcement'])) {
    $title = $_POST['title'];
    $message = $_POST['message'];
    $date = date('Y-m-d');

    $stmt = $conn->prepare("INSERT INTO announcements (title, message, date_posted) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $title, $message, $date);
    $stmt->execute();
    $stmt->close();

    header("Location: announcement.php");
    exit;
}

// EDIT ANNOUNCEMENT
if (isset($_POST['edit_announcement'])) {
    $id = $_POST['id'];
    $title = $_POST['title'];
    $message = $_POST['message'];

    $stmt = $conn->prepare("UPDATE announcements SET title=?, message=? WHERE id=?");
    $stmt->bind_param("ssi", $title, $message, $id);
    $stmt->execute();
    $stmt->close();

    header("Location: announcement.php");
    exit;
}

// DELETE ANNOUNCEMENT
if (isset($_POST['delete_announcement'])) {
    $id = $_POST['id'];
    $stmt = $conn->prepare("DELETE FROM announcements WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();

    header("Location: announcement.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Announcements Dashboard</title>

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
.popup-box { background:white; width:90%; max-width:500px; padding:20px; border-radius:10px; }
.close-btn { float:right; font-size:25px; cursor:pointer; }
</style>
</head>
<body>

<div class="header">
    <div class="logo">FLEXCORE</div>
    <button id="toggleBtn" onclick="toggleSidebar()">☰ Menu</button>
    <div>
        <button class="manage-btn" onclick="document.getElementById('popup').style.display='flex'">ADD ANNOUNCEMENT</button>
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
    <h2>Announcements</h2>
    <div class="table-responsive mt-4">
        <table class="table table-hover table-bordered align-middle">
            <thead class="table-primary text-center">
                <tr>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Date Posted</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $sql = "SELECT * FROM announcements ORDER BY date_posted DESC";
            $result = $conn->query($sql);

            if (!$result) {
                echo "<tr><td colspan='4' class='text-danger text-center'>SQL Error: {$conn->error}</td></tr>";
            } elseif ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>
                        <td>{$row['title']}</td>
                        <td>{$row['message']}</td>
                        <td class='text-center'>{$row['date_posted']}</td>
                        <td class='text-center'>
                            <button class='btn btn-warning btn-sm'
                                onclick=\"openEditPopup('{$row['id']}','".htmlspecialchars($row['title'], ENT_QUOTES)."','".htmlspecialchars($row['message'], ENT_QUOTES)."')\">Edit</button>
                            <button class='btn btn-danger btn-sm'
                                onclick=\"openDeletePopup('{$row['id']}','".htmlspecialchars($row['title'], ENT_QUOTES)."')\">Delete</button>
                        </td>
                    </tr>";
                }
            } else {
                echo "<tr><td colspan='4' class='text-center'>No announcements found.</td></tr>";
            }
            ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ADD ANNOUNCEMENT POPUP -->
<div class="popup-bg" id="popup">
    <div class="popup-box">
        <span class="close-btn" onclick="document.getElementById('popup').style.display='none'">&times;</span>
        <h3>Add Announcement</h3>
        <form method="POST">
            <input type="text" name="title" placeholder="Title" class="form-control mb-2" required>
            <textarea name="message" placeholder="Message" class="form-control mb-2" rows="4" required></textarea>
            <button type="submit" name="add_announcement" class="btn btn-primary w-100">Add Announcement</button>
        </form>
    </div>
</div>

<!-- EDIT ANNOUNCEMENT POPUP -->
<div class="popup-bg" id="editPopup">
    <div class="popup-box">
        <span class="close-btn" onclick="document.getElementById('editPopup').style.display='none'">&times;</span>
        <h3>Edit Announcement</h3>
        <form method="POST">
            <input type="hidden" name="id" id="editId">
            <input type="text" name="title" id="editTitle" class="form-control mb-2" required>
            <textarea name="message" id="editMessage" class="form-control mb-2" rows="4" required></textarea>
            <button type="submit" name="edit_announcement" class="btn btn-primary w-100">Update Announcement</button>
        </form>
    </div>
</div>

<!-- DELETE ANNOUNCEMENT POPUP -->
<div class="popup-bg" id="deletePopup">
    <div class="popup-box text-center">
        <span class="close-btn" onclick="document.getElementById('deletePopup').style.display='none'">&times;</span>
        <h4>Delete <span id="deleteAnnouncementTitle"></span>?</h4>
        <form method="POST">
            <input type="hidden" name="id" id="deleteId">
            <button type="submit" name="delete_announcement" class="btn btn-danger mt-2 me-2">Yes</button>
            <button type="button" class="btn btn-secondary mt-2" onclick="document.getElementById('deletePopup').style.display='none'">Cancel</button>
        </form>
    </div>
</div>

<script>
function toggleSidebar() { document.getElementById("sidebar").classList.toggle("active"); }
function openEditPopup(id,title,message){
    document.getElementById('editPopup').style.display='flex';
    document.getElementById('editId').value=id;
    document.getElementById('editTitle').value=title;
    document.getElementById('editMessage').value=message;
}
function openDeletePopup(id,title){
    document.getElementById('deletePopup').style.display='flex';
    document.getElementById('deleteAnnouncementTitle').innerText=title;
    document.getElementById('deleteId').value=id;
}
</script>

</body>
</html>

<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<?php 
include "db.php";

$id = $_GET['id'];
$result = mysqli_query($conn, "SELECT * FROM members WHERE id = $id");
$member = mysqli_fetch_assoc($result);

if ($_SERVER['REQUEST_METHOD'] === "POST") {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $age = $_POST['age'];

    mysqli_query($conn, "UPDATE members 
                         SET fullname='$name', email='$email', age='$age'
                         WHERE id=$id");

    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Edit Member</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h2>Edit Member</h2>

    <form method="POST" class="mt-4 p-4 bg-white border rounded shadow">

        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" 
                   value="<?= $member['fullname'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="<?= $member['email'] ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-control"
                   value="<?= $member['age'] ?>" required>
        </div>

        <button type="submit" class="btn btn-warning w-100">Update</button>
        <a href="index.php" class="btn btn-secondary w-100 mt-2">Back</a>

    </form>

</div>

</body>
</html>

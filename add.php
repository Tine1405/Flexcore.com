<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<?php include "db.php"; ?>

<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = $_POST['fullname'];
    $email = $_POST['email'];
    $age = $_POST['age'];

    mysqli_query($conn, "INSERT INTO members (fullname, email, age) 
                         VALUES ('$name', '$email', '$age')");
    header("Location: index.php");
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Add Member</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <h2>Add New Member</h2>
    <form method="POST" class="mt-4 p-4 bg-white border rounded shadow">

        <div class="mb-3">
            <label class="form-label">Full Name</label>
            <input type="text" name="fullname" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" name="email" class="form-control" required>
        </div>

        <div class="mb-3">
            <label class="form-label">Age</label>
            <input type="number" name="age" class="form-control" required>
        </div>

        <button type="submit" class="btn btn-primary w-100">Save Member</button>
        <a href="index.php" class="btn btn-secondary w-100 mt-2">Back</a>

    </form>
</div>

</body>
</html>

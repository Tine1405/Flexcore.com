<?php
session_start();
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    // SQL Query
    $sql = "SELECT * FROM admins WHERE username = ?";

    $stmt = mysqli_prepare($conn, $sql);

    if (!$stmt) {
        die("SQL Prepare Failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {

        if ($password === $row['password']) {
            $_SESSION['admin'] = $row['username'];
            header("Location: dashboard.php");
            exit;
        } else {
            $error = "Incorrect password!";
        }

    } else {
        $error = "Username not found!";
    }

    mysqli_stmt_close($stmt);
}

header("Location: login.php?error=" . urlencode($error));
exit;
?>

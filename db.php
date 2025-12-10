<?php
$conn = mysqli_connect("localhost", "root", "", "gymdb");

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>

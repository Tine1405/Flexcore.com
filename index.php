<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit;
}
?>

<?php include "db.php"; ?>

<!DOCTYPE html>
<html>
<head>
    <title>Gym Members</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
</head>

<body class="bg-light">

<div class="container mt-5">

    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Gym Membership List</h2>
        <a href="add.php" class="btn btn-primary">+ Add Member</a>
    </div>

    <table class="table table-striped table-bordered">
        <thead class="table-dark">
            <tr>
                <th>ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Age</th>
                <th>Created</th>
                <th>Actions</th>
            </tr>
        </thead>

        <tbody>
            <?php
            $result = mysqli_query($conn, "SELECT * FROM members ORDER BY id DESC");
            while ($row = mysqli_fetch_assoc($result)):
            ?>
            <tr>
                <td><?= $row['id'] ?></td>
                <td><?= $row['fullname'] ?></td>
                <td><?= $row['email'] ?></td>
                <td><?= $row['age'] ?></td>
                <td><?= $row['created_at'] ?></td>
                <td>
                    <a href="edit.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                    <a href="delete.php?id=<?= $row['id'] ?>" class="btn btn-danger btn-sm"
                       onclick="return confirm('Delete this member?')">Delete</a>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>

    </table>

</div>

</body>
</html>

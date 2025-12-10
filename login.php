<?php
session_start();
include "db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user = $_POST['username'];
    $pass = md5($_POST['password']);

    $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$user' AND password='$pass'");

    if (mysqli_num_rows($query) === 1) {
        $_SESSION['admin'] = $user;
        header("Location: dashboard.php");
    } else {
        $error = "Incorrect username or password.";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Login</title>
    <link rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

<style>
/* === Modern Black → Navy Style === */
body {
    margin: 0;
    padding: 0;
    font-family: "Poppins", sans-serif;

    height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;

    background: linear-gradient(135deg, #000000 0%, #001a33 50%, #003366 100%);
    color: #e8f0ff;
}

/* Glass Card */
.login-container {
    width: 360px;
    padding: 40px 30px;

    background: rgba(255,255,255,0.07);
    border-radius: 16px;
    backdrop-filter: blur(10px);

    box-shadow: 0 10px 40px rgba(0,0,0,0.6);
    text-align: center;
}

.login-container h2 {
    margin-bottom: 20px;
    font-weight: 600;
    color: #dbe7ff;
}

/* Inputs */
.input-group {
    text-align: left;
    margin-bottom: 16px;
}

.input-group label {
    font-size: 14px;
    color: #c7d7ff;
}

.input-group input {
    width: 100%;
    padding: 12px;
    margin-top: 6px;

    border-radius: 10px;
    border: none;
    outline: none;

    background: rgba(255,255,255,0.12);
    color: #fff;

    transition: 0.2s ease-in-out;
}

.input-group input:focus {
    background: rgba(255,255,255,0.18);
    box-shadow: 0 0 8px rgba(0,150,255,0.35);
}

/* Button */
.btn-login {
    width: 100%;
    padding: 12px;
    margin-top: 12px;

    border: none;
    border-radius: 10px;

    background: linear-gradient(180deg, #1a4d80, #003366);
    color: white;
    font-size: 16px;
    cursor: pointer;

    transition: 0.2s ease-in-out;
}

.btn-login:hover {
    opacity: 0.9;
    transform: translateY(-2px);
}

/* Footer Links */
.footer {
    margin-top: 18px;
    font-size: 13px;
}

.footer a {
    color: #8cbfff;
    text-decoration: none;
}

.footer a:hover {
    text-decoration: underline;
}

/* Error Alert Override */
.alert-danger {
    width: 360px;
    margin-bottom: 20px;
    text-align: center;
}
</style>

</head>
<body>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($error) ?>
    </div>
<?php endif; ?>

<div class="login-container">
    <h2>Admin Login</h2>

    <form action="" method="POST">
        <div class="input-group">
            <label for="username">Username</label>
            <input type="text"
                id="username"
                name="username"
                placeholder="Enter username"
                required>
        </div>

        <div class="input-group">
            <label for="password">Password</label>
            <input type="password"
                id="password"
                name="password"
                placeholder="Enter password"
                required>
        </div>

        <button type="submit" class="btn-login">Login</button>

        <div class="footer">
            <a href="#">Forgot Password?</a>
        </div>
    </form>
</div>

</body>
</html>

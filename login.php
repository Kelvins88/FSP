<?php
session_start();

if (isset($_SESSION['npk'])) {
    header("Location: dosen.php");
    exit();
} elseif (isset($_SESSION['nrp'])) {
    header("Location: mahasiswa.php");
    exit();
} elseif (isset($_SESSION['isadmin']) && $_SESSION['isadmin'] === true) {
    header("Location: admin.php");
    exit();
}

$errMsg = "";
if (isset($_GET['err']) && $_GET['err'] === 'WRONG') {
    $errMsg = "Salah IDUSER / PASSWORD";
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <?php include 'inc/head.php'; ?>
    <style>

        .login-container{width:100%;max-width:420px;margin:40px auto;padding:20px;border-radius:8px}
        h2{text-align:center;margin-top:0}
        .error{color:#e53935;text-align:center;margin-bottom:12px}
        button{background:var(--accent);border:none;color:#fff;padding:10px;border-radius:6px}
    </style>
</head>
<body>

<div class="login-container panel">

    <h2>Login</h2>

    <?php if ($errMsg): ?>
        <div class="error"><?= $errMsg ?></div>
    <?php endif; ?>

    <form method="post" action="login_proses.php">
        <label>Username:</label>
        <input type="text" name="username" required>

        <label>Password:</label>
        <input type="password" name="password" required>

        <button type="submit">Login</button>
    </form>

</div>

</body>
</html>

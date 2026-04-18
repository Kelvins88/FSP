<?php
session_start();
require_once "class/admin.php";
if (!isset($_SESSION['isadmin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ganti_pwd.php");
    exit();
}

$old_password = trim($_POST['old_password'] ?? '');
$new_password = trim($_POST['new_password'] ?? '');
$confirm_password = trim($_POST['confirm_password'] ?? '');
$message = '';
$success = false;

if (empty($old_password) || empty($new_password) || empty($confirm_password)) {
    $message = "Semua field wajib diisi.";
} elseif ($new_password !== $confirm_password) {
    $message = "Konfirmasi password tidak cocok.";
} else {
    try {
        $admin = new Admin();
        $username = $_SESSION['username'];
        $stored_hash = $admin->getPassword($username);

        if (!$stored_hash || !password_verify($old_password, $stored_hash)) {
            $message = "Password lama salah!";
        } elseif ($admin->updatePassword($username, $new_password)) {
            $success = true;
            session_destroy();
            $message = "Password berhasil diganti. Silakan login kembali.";
        } else {
            $message = "Terjadi kesalahan saat mengganti password.";
        }
    } catch (Exception $e) {
        $message = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Hasil Ganti Password</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f7f9fc;
            padding: 40px;
        }
        .container {
            max-width: 400px;
            margin: auto;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }
        h2 {
            color: #333;
        }
        p {
            font-size: 15px;
            color: #444;
        }
        button {
            background: #007bff;
            color: white;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
        }
        button:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Ganti Password Admin</h2>
    <p><?= htmlspecialchars($message) ?></p>

    <?php if ($success): ?>
        <form action="login.php" method="get">
            <button type="submit">Login Ulang</button>
        </form>
    <?php else: ?>
        <form action="ganti_pwd.php" method="get">
            <button type="submit">Kembali</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
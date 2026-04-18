<?php
session_start();

if (!isset($_SESSION['npk']) && !isset($_SESSION['isadmin']) && !isset($_SESSION['nrp'])) {
    header("Location: login.php");
    exit();
}

?>

<!DOCTYPE html>
<head>
    <?php include "inc/head.php"; ?>
    <title>Ganti Password</title>
    <style>
        body { padding: 40px; display: flex; flex-direction: column; align-items: center; }
        form { background: var(--panel); border: 1px solid var(--chat-border); padding: 30px; border-radius: 12px; width: 350px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        h2 { color: var(--text); margin-bottom: 25px; }
        label { color: var(--text); font-weight: 600; font-size: 14px; }
        input[type="password"] { 
            width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; 
            border-radius: 6px; border: 1px solid var(--chat-border); 
            background: var(--bg); color: var(--text); box-sizing: border-box;
        }
        button { 
            width: 100%; padding: 12px; background: var(--accent); color: white; 
            border: none; border-radius: 6px; cursor: pointer; font-weight: bold; 
            transition: background 0.2s;
        }
        button:hover { opacity: 0.9; }
    </style>
</head>
<body>
<h2>Ganti Password</h2>

<form method="post" action="ganti_admin_proses.php">
    <label>Password Lama:</label>
    <input type="password" name="old_password" required>

    <label>Password Baru:</label>
    <input type="password" name="new_password" required>

    <label>Konfirmasi Password Baru:</label>
    <input type="password" name="confirm_password" required>

    <button type="submit">Ganti Password</button>
</form>


</body>
</html>

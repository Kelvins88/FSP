<?php
session_start();
require_once "class/Mahasiswa.php";

if (isset($_SESSION['isadmin'])) {
    header("Location: admin.php");
    exit();
} elseif (isset($_SESSION['npk'])) {
    header("Location: dosen.php");
    exit();
} elseif (!isset($_SESSION['npk']) && !isset($_SESSION['isadmin']) && !isset($_SESSION['nrp'])) {
    header("Location: login.php");
    exit();
}

$mhs = new Mahasiswa();
?>
<!DOCTYPE html>
<html>
<head>
<title>Mahasiswa Dashboard</title>
<?php include "inc/head.php"; ?>
<style>
    body {
        margin: 0;
        padding: 20px;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
        background: var(--bg);
        color: var(--text);
    }

    .top-nav {
        background: var(--panel);
        padding: 12px 18px;
        border-radius: 8px;
        margin-bottom: 22px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: 1px solid var(--chat-border);
    }

    .top-nav .left-text {
        font-size: 15px;
        color: var(--text);
    }

    .top-nav a {
        text-decoration: none;
        font-weight: bold;
        margin-left: 10px;
        padding: 6px 12px;
        border-radius: 5px;
        transition: 0.2s;
    }

    .top-nav a:hover {
        background: var(--bg);
    }

    .top-nav .pw   { color: var(--text); }
    .top-nav .grp  { color: var(--accent); }
    .top-nav .out  { color: #dc3545; }

    @media (max-width: 768px) {
        .top-nav {
            flex-direction: column;
            gap: 10px;
            align-items: flex-start;
        }

        .top-nav .right-links {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .top-nav a {
            margin-left: 0;
        }
    }
</style>

</head>
<body>

<div class="top-nav">
    <div class="left-text">
        Login sebagai: <b><?= htmlspecialchars($_SESSION['username']) ?></b>
    </div>

    <div class="right-links">
        <a href="ganti_pwd.php" class="pw">Ganti Password</a>
        <a href="mahasiswa_group.php" class="grp">Group</a>
        <a href="logout.php" class="out">Logout</a>
    </div>
</div>

</body>
</html>

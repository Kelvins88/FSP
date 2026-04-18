<?php
session_start();

if (!isset($_SESSION['isadmin'])) {
    if (isset($_SESSION['npk'])) {
        header("Location: dosen.php");
        exit();
    } elseif (isset($_SESSION['nrp'])) {
        header("Location: mahasiswa.php");
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <?php include "inc/head.php"; ?>
    <style>
        body {
            margin: 0;
            padding: 20px;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        h1 {
            color: var(--text);
            margin-bottom: 30px;
        }

        .menu {
            display: grid;
            gap: 15px;
            margin-bottom: 30px;
        }

        .button {
            display: block;
            padding: 15px 20px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            text-align: center;
            font-weight: 500;
            transition: opacity 0.2s;
        }

        .button:hover {
            opacity: 0.9;
        }

        .logout {
            text-align: center;
            margin-top: 30px;
        }

        .logout a {
            color: var(--accent);
            text-decoration: none;
            padding: 10px 20px;
            border: 1px solid var(--accent);
            border-radius: 6px;
            display: inline-block;
        }

        .logout a:hover {
            background: var(--accent);
            color: white;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            h1 {
                font-size: 24px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Selamat Datang, <?= htmlspecialchars($_SESSION['username']); ?></h1>

    <div class="menu">
        <a href="dosen_data.php" class="button">Kelola Data Dosen</a>
        <a href="mahasiswa_data.php" class="button">Kelola Data Mahasiswa</a>
        <a href="ganti_admin.php" class="button">Ganti Password Admin</a>
    </div>

    <div class="logout">
        <a href="logout.php">Logout</a>
    </div>
</div>

</body>
</html>
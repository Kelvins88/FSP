<?php
session_start();
require_once "class/koneksi.php";
require_once "class/dosen.php";

if (!isset($_SESSION['npk'])) {
    header("Location: login.php");
    exit();
}

$username = $_SESSION['username'];
$dosen = new Dosen();

$idgrup = intval($_GET['id'] ?? 0);
$group = $dosen->getGroupById($idgrup, $username);

if (!$group) {
    die("Anda tidak memiliki akses untuk grup ini.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama']);
    $jenis = trim($_POST['jenis'] ?? '');
    $deskripsi = trim($_POST['deskripsi']);

    if ($jenis !== 'Publik' && $jenis !== 'Privat') {
        die("Jenis grup tidak valid. Harus 'Publik' atau 'Privat'.");
    }

    $update = $dosen->updateGroup($idgrup, $nama, $jenis, $deskripsi, $username);

    if ($update) {
        header("Location: dosen_group_detail.php?id=" . $idgrup);
        exit();
    } else {
        echo "Gagal memperbarui data grup!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Group</title>
    <?php include "inc/head.php"; ?>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 26px;
        }


        .page-container {
            width: 100%;
            display: flex;
            justify-content: center;
            margin-top: 10px;
        }


        .form-box {
            background: var(--panel);
            width: 500px;  
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid var(--chat-border);
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        input[type="text"],
        select,
        textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid var(--chat-border);
            border-radius: 6px;
            font-size: 14px;
            background: var(--bg);
            color: var(--text);
            box-sizing: border-box;
        }

        textarea {
            resize: vertical;
        }

        input[type="submit"] {
            background: var(--accent);
            color: white;
            padding: 10px 18px;
            border: none;
            font-size: 15px;
            border-radius: 6px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        input[type="submit"]:hover {
            opacity: 0.9;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            font-weight: bold;
            color: var(--accent);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<h2>Edit Group</h2>

<div class="page-container">
    <div class="form-box">
        <form method="POST">

            <label>Nama Group:</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($group['nama']) ?>" required>

            <label>Jenis Group:</label>
            <select name="jenis" required>
                <option value="Publik" <?= $group['jenis']=="Publik"?"selected":"" ?>>Publik</option>
                <option value="Privat" <?= $group['jenis']=="Privat"?"selected":"" ?>>Privat</option>
            </select>

            <label>Deskripsi:</label>
            <textarea name="deskripsi" rows="4" required><?= htmlspecialchars($group['deskripsi']) ?></textarea>

            <input type="submit" value="Update">
        </form>
    </div>
</div>

<a href="dosen_group_detail.php?id=<?= $idgrup ?>" class="back-link">← Kembali ke Detail</a>

</body>
</html>

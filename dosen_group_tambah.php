<?php
session_start();
require_once "class/koneksi.php";
require_once "class/dosen.php";

if (!isset($_SESSION['npk'])) {
    header("Location: login.php");
    exit();
}

$dosen = new Dosen();
$username = $_SESSION['username'];

$error = "";
$success = "";

if (isset($_POST['buat'])) {

    $nama = trim($_POST['nama']);
    $deskripsi = trim($_POST['deskripsi']);
    $jenis = trim($_POST['jenis'] ?? '');

    if ($nama == "") {
        $error = "Nama grup wajib diisi.";
    } elseif ($jenis !== 'Publik' && $jenis !== 'Privat') {
        $error = "Jenis grup harus 'Publik' atau 'Privat'.";
    } else {

        list($ok, $result) = $dosen->buatGrup($username, $nama, $deskripsi, $jenis);

        if ($ok) {
            header("Location: dosen_group_detail.php?id=" . $result);
            exit();
        } else {
            $error = $result;
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buat Group Baru</title>
    <?php include "inc/head.php"; ?>
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif; 
            padding: 20px; 
            background: var(--bg);
            color: var(--text);
        }

        .container {
            max-width: 700px;
            margin: auto;
        }

        .card {
            background: var(--panel);
            padding: 20px;
            margin-top: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid var(--chat-border);
        }

        h2 {
            margin: 0 0 15px 0;
        }

        .btn {
            padding: 8px 14px;
            background: var(--accent);
            color: white;
            border-radius: 4px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        input[type=text], textarea, select {
            width: 100%;
            padding: 8px;
            margin-top: 6px;
            border: 1px solid var(--chat-border);
            border-radius: 5px;
            background: var(--bg);
            color: var(--text);
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
            resize: vertical;
        }

        .error {
            color: #dc3545;
            margin-bottom: 10px;
            padding: 10px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 4px;
        }

        a.back {
            text-decoration: none;
            color: var(--accent);
            font-weight: bold;
        }

        a.back:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .card {
                padding: 15px;
            }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card">
        <h2>Buat Grup Baru</h2>

        <?php if ($error): ?>
            <p class="error"><?= $error ?></p>
        <?php endif; ?>

        <form method="POST">
            
            <label>Nama Grup:</label>
            <input type="text" name="nama" required>

            <br><br>

            <label>Deskripsi:</label>
            <textarea name="deskripsi" required></textarea>

            <br><br>

            <label>Jenis Grup:</label>
            <select name="jenis" required>
                <option value="Publik" selected>Publik</option>
                <option value="Privat">Privat</option>
            </select>

            <br><br>

            <button type="submit" name="buat" class="btn">Buat</button>
        </form>

        <br>
        <a href="dosen_group.php" class="back">Kembali</a>
    </div>

</div>

</body>
</html>

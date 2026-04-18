<?php
session_start();
require_once "class/koneksi.php";
require_once "class/dosen.php";

if (!isset($_SESSION['npk'])) {
    header("Location: login.php");
    exit();
}

$dosen = new Dosen();

$idgrup = intval($_GET['idgrup'] ?? 0);

if ($idgrup <= 0) {
    die("ID grup tidak valid.");
}

if (!$dosen->cekGrupMilikDosen($idgrup, $_SESSION['npk'])) {
    die("Akses tidak diizinkan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $tanggal = trim($_POST['tanggal']);
    $keterangan = trim($_POST['keterangan']);
    $jenis = trim($_POST['jenis']);
    $poster_ext = null;

    if (!empty($_FILES['poster']['tmp_name'])) {
        $upload_dir = "./upload/event";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = $_FILES['poster']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $valid_ext)) {
            $poster_ext = $ext;
        }
    }

    $ok = $dosen->addEvent($idgrup, $judul, $tanggal, $keterangan, $jenis, $poster_ext);

    if ($ok) {
        if ($poster_ext) {
            $last_id = $dosen->getConnection()->insert_id;
            $new_path = "./upload/event/" . $last_id . "." . $poster_ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], $new_path);
        }
        header("Location: dosen_group_detail.php?id=" . $idgrup);
        exit();
    } else {
        echo "<p style='color:red; text-align:center;'>Gagal menambah event.</p>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include "inc/head.php"; ?>
    <title>Tambah Event</title>

    <style>
        body {
            padding: 0;
        }

        h2 {
            text-align: center;
            margin-top: 40px;
            margin-bottom: 20px;
            font-size: 26px;
            font-weight: 700;
            color: var(--text);
        }

        .page-container {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .form-box {
            background: var(--panel);
            color: var(--text);
            width: 500px;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 6px;
        }

        input[type="text"],
        input[type="datetime-local"],
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
            transition: 0.2s;
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

<h2>Tambah Event</h2>

<div class="page-container">
    <div class="form-box">

        <form method="POST" enctype="multipart/form-data">

            <label>Judul Event:</label>
            <input type="text" name="judul" required>

            <label>Jenis Event:</label>
            <select name="jenis" required>
                <option value="publik">Publik</option>
                <option value="privat">Privat</option>
            </select>

            <label>Tanggal Event:</label>
            <input type="datetime-local" name="tanggal" required>

            <label>Keterangan:</label>
            <textarea name="keterangan" rows="4" required></textarea>

            <label>Poster Event:</label>
            <input type="file" name="poster" accept="image/*" style="margin-bottom:15px;">

            <input type="submit" value="Tambah">
        </form>

    </div>
</div>

<a href="dosen_group_detail.php?id=<?= $idgrup ?>" class="back-link">← Kembali ke Detail</a>


</body>
</html>

<?php
session_start();
require_once "class/koneksi.php";
require_once "class/dosen.php";

if (!isset($_SESSION['npk'])) {
    header("Location: login.php");
    exit();
}

$dosen = new Dosen();

$idevent = intval($_GET['id'] ?? 0);

if ($idevent <= 0) {
    die("ID event tidak valid.");
}

$event = $dosen->getEventById($idevent);

if (!$event) {
    die("Event tidak ditemukan.");
}

if (!$dosen->cekGrupMilikDosen($event['idgrup'], $_SESSION['npk'])) {
    die("Akses tidak diizinkan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $judul = trim($_POST['judul']);
    $tanggal = trim($_POST['tanggal']);
    $keterangan = trim($_POST['keterangan']);
    $jenis = trim($_POST['jenis']);
    $poster_ext = $event['poster_extension'];

    if (!empty($_FILES['poster']['tmp_name'])) {
        $upload_dir = "./upload/event";
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }

        $filename = $_FILES['poster']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $valid_ext = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($ext, $valid_ext)) {
            if ($poster_ext) {
                $old_file = "./upload/event/" . $idevent . "." . $poster_ext;
                if (file_exists($old_file)) {
                    unlink($old_file);
                }
            }
            
            $poster_ext = $ext;
            $new_path = "./upload/event/" . $idevent . "." . $poster_ext;
            move_uploaded_file($_FILES['poster']['tmp_name'], $new_path);
        }
    }

    $ok = $dosen->updateEvent($idevent, $judul, $tanggal, $keterangan, $jenis, $poster_ext);

    if ($ok) {
        header("Location: dosen_group_detail.php?id=" . $event['idgrup']);
        exit();
    } else {
        echo "<p style='color:red; text-align:center;'>Gagal mengupdate event.</p>";
    }
}

$datetimeValue = str_replace(' ', 'T', $event['tanggal']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include "inc/head.php"; ?>
    <title>Edit Event</title>

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

        .current-poster {
            width: 100%;
            max-height: 200px;
            object-fit: contain;
            border-radius: 6px;
            margin-bottom: 15px;
            border: 1px solid var(--chat-border);
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

<h2>Edit Event</h2>

<div class="page-container">
    <div class="form-box">

        <form method="POST" enctype="multipart/form-data">

            <label>Judul Event:</label>
            <input type="text" name="judul" value="<?= htmlspecialchars($event['judul']) ?>" required>

            <label>Jenis Event:</label>
            <select name="jenis" required>
                <option value="publik" <?= $event['jenis'] === 'publik' ? 'selected' : '' ?>>Publik</option>
                <option value="privat" <?= $event['jenis'] === 'privat' ? 'selected' : '' ?>>Privat</option>
            </select>

            <label>Tanggal Event:</label>
            <input type="datetime-local" name="tanggal" value="<?= $datetimeValue ?>" required>

            <label>Keterangan:</label>
            <textarea name="keterangan" rows="4" required><?= htmlspecialchars($event['keterangan']) ?></textarea>

            <label>Poster Saat Ini:</label>
            <?php if ($event['poster_extension']): ?>
                <img src="upload/event/<?= $event['idevent'] . "." . $event['poster_extension'] ?>" class="current-poster" alt="Poster">
            <?php else: ?>
                <p style="margin-bottom: 15px;">Tidak ada poster.</p>
            <?php endif; ?>

            <label>Ganti Poster:</label>
            <input type="file" name="poster" accept="image/*" style="margin-bottom:15px;">

            <input type="submit" value="Update">
        </form>

    </div>
</div>

<a href="dosen_group_detail.php?id=<?= $event['idgrup'] ?>" class="back-link">← Kembali ke Detail</a>


</body>
</html>

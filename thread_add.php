<?php
session_start();
require_once "class/thread.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['idgrup'])) {
    die("ID grup tidak valid");
}

$username = $_SESSION['username'];
$idgrup = (int) $_GET['idgrup'];

$threadObj = new Thread();

if (!$threadObj->isMemberGroup($idgrup, $username)) {
    die("Anda bukan member grup ini.");
}


require_once "class/dosen.php";
$dosenObj = new Dosen();
$isDosen = $dosenObj->isDosen($username);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($isDosen && isset($_POST['status'])) {
        $status = ($_POST['status'] === 'CLOSE') ? 'CLOSE' : 'OPEN';
    } else {
        $status = 'OPEN';
    }

    if ($threadObj->createThread($idgrup, $username, $status)) {
        header("Location: thread.php?idgrup=" . $idgrup);
        exit;
    } else {
        die("Gagal membuat thread.");
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Buat Thread</title>
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
            max-width: 600px;
            margin: 0 auto;
            background: var(--panel);
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
        }

        h3 {
            margin-top: 0;
            color: var(--text);
        }

        p {
            color: var(--muted);
            line-height: 1.6;
        }

        form {
            margin-top: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: var(--text);
        }

        select {
            width: 100%;
            padding: 10px;
            border: 1px solid var(--chat-border);
            border-radius: 6px;
            background: var(--bg);
            color: var(--text);
            font-size: 14px;
            margin-bottom: 20px;
        }

        button[type="submit"] {
            padding: 10px 20px;
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 10px;
        }

        button[type="submit"]:hover {
            opacity: 0.9;
        }

        .btn-cancel {
            display: inline-block;
            padding: 10px 20px;
            background: transparent;
            color: var(--accent);
            border: 1px solid var(--accent);
            border-radius: 6px;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-cancel:hover {
            background: var(--accent);
            color: white;
        }

        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <h3>Buat Thread Baru</h3>

    <p>
        Thread akan dibuat tanpa judul.<br>
        Identitas thread ditentukan oleh <b>pembuat</b> dan <b>waktu pembuatan</b>.
    </p>

    <form method="POST">
        <?php if ($isDosen): ?>
            <label>Status Thread:</label>
            <select name="status">
                <option value="OPEN">OPEN (Diskusi Dibuka)</option>
                <option value="CLOSE">CLOSE (Diskusi Ditutup)</option>
            </select>
        <?php endif; ?>

        <button type="submit">Buat Thread</button>
        <a href="thread.php?idgrup=<?= $idgrup ?>" class="btn-cancel">Batal</a>
    </form>
</div>

</body>
</html>

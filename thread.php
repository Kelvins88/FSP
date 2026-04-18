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

$idgrup   = (int) $_GET['idgrup'];
$username = $_SESSION['username'];

$isDosen     = isset($_SESSION['npk']);
$isMahasiswa = isset($_SESSION['nrp']);

$threadObj = new Thread();

if (!$threadObj->isMemberGroup($idgrup, $username)) {
    die("Anda bukan anggota grup ini.");
}

$threads = $threadObj->getThreadsByGroup($idgrup);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Thread Diskusi</title>
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
            max-width: 900px;
            margin: 0 auto;
        }

        h2 {
            margin-bottom: 20px;
            color: var(--text);
        }

        .thread {
            background: var(--panel);
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 1px 4px rgba(0,0,0,.1);
            border: 1px solid var(--chat-border);
        }

        .thread-info {
            margin-bottom: 10px;
            font-size: 14px;
            color: var(--text);
        }

        .thread-info b {
            color: var(--accent);
        }

        .btn {
            display: inline-block;
            padding: 8px 16px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 13px;
            margin-right: 8px;
            margin-top: 8px;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-danger {
            background: #dc3545;
        }

        .status {
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            display: inline-block;
        }

        .status.OPEN {
            color: #28a745;
            background: rgba(40, 167, 69, 0.1);
        }

        .status.CLOSE {
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: var(--muted);
        }

        .back-link {
            display: inline-block;
            margin-bottom: 20px;
            color: var(--accent);
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            .thread {
                padding: 12px;
            }

            .thread-info {
                font-size: 13px;
            }

            .btn {
                display: block;
                width: 100%;
                text-align: center;
                margin: 0 0 8px 0;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <?php 
    $backPage = $isDosen ? "dosen_group_detail.php" : "mahasiswa_group_detail.php";
    ?>
    <a href="<?= $backPage ?>?id=<?= $idgrup ?>" class="back-link">← Kembali</a>
    <h2>Thread Diskusi</h2>

<a class="btn" href="thread_add.php?idgrup=<?= $idgrup ?>">+ Buat Thread</a>

    <?php if (empty($threads)): ?>
        <div class="empty-state">
            <p>Belum ada thread.</p>
        </div>
    <?php else: ?>
        <?php foreach ($threads as $t): ?>
        <?php
            $status = strtoupper(trim($t['status']));
        ?>
        <div class="thread">
            <div class="thread-info">
                <b>Thread #<?= $t['idthread'] ?></b><br>
                Pembuat: <?= htmlspecialchars($t['nama_pembuat']) ?><br>
                Dibuat: <?= date('d M Y H:i', strtotime($t['tanggal_pembuatan'])) ?><br>
                Status: <span class="status <?= $status ?>"><?= $status ?></span>
            </div>

            <?php if ($status === 'OPEN'): ?>
                <a class="btn" href="chat.php?idthread=<?= $t['idthread'] ?>&idgrup=<?= $idgrup ?>">Masuk Chat</a>
            <?php else: ?>
                <span style="color:#dc3545; font-size:13px;">Thread ditutup</span>
            <?php endif; ?>

            <?php if ($status === 'OPEN' && $t['username_pembuat'] === $username): ?>
                <a class="btn btn-danger"
                   href="thread_status.php?id=<?= $t['idthread'] ?>&idgrup=<?= $idgrup ?>">
                   Tutup Thread
                </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

</body>
</html>

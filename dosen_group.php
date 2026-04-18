<?php
session_start();
require_once "class/dosen.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$dosen = new Dosen();
$username = $_SESSION['username'];

$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$totalGroups = $dosen->countGroupsCreatedBy($username);
$totalPages = ceil($totalGroups / $limit);

$groups = $dosen->getGroupsCreatedByPaged($username, $limit, $offset);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Daftar Group Dosen</title>
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
            max-width: 1200px;
            margin: 0 auto;
        }

        .card {
            background: var(--panel);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            margin-bottom: 25px;
            border: 1px solid var(--chat-border);
        }

        h2 {
            margin-bottom: 15px;
            color: var(--text);
        }

        .btn-primary {
            display: inline-block;
            background: var(--accent);
            color: white;
            padding: 8px 14px;
            border-radius: 5px;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .btn-primary:hover {
            opacity: 0.9;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        th {
            background: var(--accent);
            color: white;
            padding: 10px;
            text-align: left;
        }

        td {
            padding: 10px;
            background: var(--panel);
            border-bottom: 1px solid var(--chat-border);
        }

        tr:hover td {
            background: var(--bg);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 13px;
            text-decoration: none;
            transition: opacity 0.2s;
        }

        .btn-delete:hover {
            opacity: 0.9;
        }

        .pagination {
            text-align: center;
            margin-top: 20px;
        }

        .pagination a, .pagination strong {
            padding: 6px 10px;
            text-decoration: none;
            border-radius: 4px;
            margin: 0 3px;
            display: inline-block;
        }

        .pagination a {
            background: var(--panel);
            border: 1px solid var(--chat-border);
            color: var(--text);
        }

        .pagination strong {
            background: var(--accent);
            color: white;
        }

        .back-btn {
            margin-top: 20px;
            display: inline-block;
            color: var(--accent);
            text-decoration: none;
        }

        .back-btn:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            body {
                padding: 15px;
            }

            table {
                font-size: 14px;
            }

            th, td {
                padding: 8px 5px;
            }

            .pagination a, .pagination strong {
                padding: 5px 8px;
                font-size: 13px;
            }
        }

        @media (max-width: 600px) {
            table {
                display: block;
                overflow-x: auto;
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
    <h2>Group Buatan Anda</h2>

    <a href="dosen_group_tambah.php" class="btn-primary">+ Buat Group Baru</a>

    <table>
        <tr>
            <th>Nama</th>
            <th>Jenis</th>
            <th>Tanggal</th>
            <th>Aksi</th>
            <th>Hapus</th>
        </tr>

        <?php foreach ($groups as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['nama']) ?></td>
            <td><?= htmlspecialchars($row['jenis']) ?></td>
            <td><?= htmlspecialchars($row['tanggal_pembentukan']) ?></td>

            <td>
                <a href="dosen_group_detail.php?id=<?= $row['idgrup'] ?>"
                   style="color:#007bff; font-weight:bold;">
                   Detail
                </a>
            </td>

            <td>
                <a href="delete_group.php?id=<?= $row['idgrup'] ?>" class="btn-delete">
                    Hapus
                </a>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>

    <?php if ($totalPages > 1): ?>
    <div class="pagination" style="text-align:center; margin-top:20px;">
        <?php if ($page > 1): ?>
            <a href="?page=<?= $page - 1 ?>">&laquo; Sebelumnya</a>
        <?php endif; ?>

        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <?php if ($i == $page): ?>
                <strong><?= $i ?></strong>
            <?php else: ?>
                <a href="?page=<?= $i ?>"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>

        <?php if ($page < $totalPages): ?>
            <a href="?page=<?= $page + 1 ?>">Selanjutnya &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <p>Halaman <?= $page ?> dari <?= $totalPages ?> (Total: <?= $totalGroups ?> grup)</p>

    <a href="dosen.php" class="back-btn">← Kembali</a>
</div>
</div>

</body>
</html>

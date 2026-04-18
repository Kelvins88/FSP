<?php
session_start();

if (!isset($_SESSION['nrp'])) {
    header("Location: login.php");
    exit();
}

require_once "class/mahasiswa.php";
$mhs = new Mahasiswa();

$nrp      = $_SESSION['nrp'];
$username = $_SESSION['username'] ?? $nrp;

$joinSuccess = "";
$joinError   = "";

if (isset($_POST['join_grup'])) {
    $idgrup = (int)$_POST['idgrup'];
    $kode   = trim($_POST['kode']);

    list($status, $msg) = $mhs->joinPublicGroup($idgrup, $kode, $username);

    if ($status)  $joinSuccess = $msg;
    else          $joinError   = $msg;
}

if (isset($_GET['leave'])) {
    $idgrup = (int)$_GET['leave'];
    $mhs->leaveGroup($idgrup, $username);

    header("Location: mahasiswa_group.php");
    exit();
}


$pageFollowed = isset($_GET['page_f']) ? max(1, intval($_GET['page_f'])) : 1;
$limit = 10;
$offsetFollowed = ($pageFollowed - 1) * $limit;

$totalFollowed = $mhs->countGroupsFollowed($username);
$totalPagesFollowed = ceil($totalFollowed / $limit);

$groupsFollowed = $mhs->getGroupsFollowedPaged($username, $limit, $offsetFollowed);


$pagePublic = isset($_GET['page_p']) ? max(1, intval($_GET['page_p'])) : 1;
$offsetPublic = ($pagePublic - 1) * $limit;

$totalPublic = $mhs->countPublicGroupsNotJoined($username);
$totalPagesPublic = ceil($totalPublic / $limit);

$groupsPublic = $mhs->getPublicGroupsNotJoinedPaged($username, $limit, $offsetPublic);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Grup Mahasiswa</title>
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

        h2 {
            color: var(--text);
            margin-bottom: 20px;
        }

        .card {
            background: var(--panel);
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            border: 1px solid var(--chat-border);
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
            color: var(--text);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid var(--chat-border);
            padding: 8px;
        }

        th {
            background: var(--accent);
            color: white;
            text-align: left;
        }

        td {
            background: var(--panel);
        }

        tr:hover td {
            background: var(--bg);
        }

        .btn {
            padding: 6px 14px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
            margin-right: 5px;
            transition: opacity 0.2s;
        }

        .btn:hover {
            opacity: 0.9;
        }

        .btn-danger {
            background: #dc3545;
        }

        .alert-success {
            color: #28a745;
            padding: 10px;
            background: rgba(40, 167, 69, 0.1);
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .alert-error {
            color: #dc3545;
            padding: 10px;
            background: rgba(220, 53, 69, 0.1);
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .pagination {
            text-align: center;
            margin-top: 15px;
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

        .back-link {
            color: var(--accent);
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 20px;
        }

        .back-link:hover {
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
                padding: 6px 4px;
            }

            .btn {
                display: block;
                width: 100%;
                text-align: center;
                margin-bottom: 5px;
            }
        }

        @media (max-width: 600px) {
            table {
                display: block;
                overflow-x: auto;
            }

            .card {
                padding: 12px;
            }
        }
    </style>
</head>
<body>

<div class="container">

<h2>Kelola Grup</h2>

<?php if ($joinSuccess): ?>
        <div class="alert-success"><?= htmlspecialchars($joinSuccess) ?></div>
<?php endif; ?>

<?php if ($joinError): ?>
        <div class="alert-error"><?= htmlspecialchars($joinError) ?></div>
<?php endif; ?>

<div class="card">
    <div class="title">Grup yang Anda Ikuti</div>

    <table>
        <tr>
            <th>Nama Grup</th>
            <th>Jenis</th>
            <th>Tanggal</th>
            <th>Aksi</th>
        </tr>

        <?php if (empty($groupsFollowed)): ?>
            <tr><td colspan="4">Belum mengikuti grup apapun.</td></tr>
        <?php else: ?>
            <?php foreach ($groupsFollowed as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['nama']) ?></td>
                    <td><?= htmlspecialchars($g['jenis']) ?></td>
                    <td><?= htmlspecialchars($g['tanggal_pembentukan']) ?></td>
                    <td>
                        <a class="btn" href="mahasiswa_group_detail.php?id=<?= (int)$g['idgrup'] ?>">Detail</a>
                        <a class="btn btn-danger" href="?leave=<?= (int)$g['idgrup'] ?>">Keluar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>
    </table>
    
    <?php if ($totalPagesFollowed > 1): ?>
    <div style="margin-top: 15px; text-align: center;">
        <?php if ($pageFollowed > 1): ?>
            <a href="?page_f=<?= $pageFollowed - 1 ?><?= $pagePublic > 1 ? '&page_p='.$pagePublic : '' ?>" style="margin: 0 5px;">&laquo; Sebelumnya</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPagesFollowed; $i++): ?>
            <?php if ($i == $pageFollowed): ?>
                <strong style="margin: 0 5px;"><?= $i ?></strong>
            <?php else: ?>
                <a href="?page_f=<?= $i ?><?= $pagePublic > 1 ? '&page_p='.$pagePublic : '' ?>" style="margin: 0 5px;"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($pageFollowed < $totalPagesFollowed): ?>
            <a href="?page_f=<?= $pageFollowed + 1 ?><?= $pagePublic > 1 ? '&page_p='.$pagePublic : '' ?>" style="margin: 0 5px;">Selanjutnya &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <p style="margin-top: 10px; font-size: 12px;">Halaman <?= $pageFollowed ?> dari <?= $totalPagesFollowed ?> (Total: <?= $totalFollowed ?> grup)</p>
</div>

<div class="card">
    <div class="title">Grup Publik yang Bisa Anda Join</div>

    <table>
        <tr>
            <th>Nama Grup</th>
            <th>Deskripsi</th>
            <th>Aksi</th>
        </tr>

        <?php if (empty($groupsPublic)): ?>
            <tr><td colspan="3">Tidak ada grup publik yang tersedia atau Anda sudah bergabung semuanya.</td></tr>
        <?php else: ?>
            <?php foreach ($groupsPublic as $g): ?>
                <tr>
                    <td><?= htmlspecialchars($g['nama']) ?></td>
                    <td><?= htmlspecialchars($g['deskripsi']) ?></td>
                    <td>
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="idgrup" value="<?= (int)$g['idgrup'] ?>">
                            <input type="text" name="kode" placeholder="Kode Pendaftaran" required>
                            <button class="btn" name="join_grup">Join</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php endif; ?>

    </table>
    
    <?php if ($totalPagesPublic > 1): ?>
    <div style="margin-top: 15px; text-align: center;">
        <?php if ($pagePublic > 1): ?>
            <a href="?page_p=<?= $pagePublic - 1 ?><?= $pageFollowed > 1 ? '&page_f='.$pageFollowed : '' ?>" style="margin: 0 5px;">&laquo; Sebelumnya</a>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $totalPagesPublic; $i++): ?>
            <?php if ($i == $pagePublic): ?>
                <strong style="margin: 0 5px;"><?= $i ?></strong>
            <?php else: ?>
                <a href="?page_p=<?= $i ?><?= $pageFollowed > 1 ? '&page_f='.$pageFollowed : '' ?>" style="margin: 0 5px;"><?= $i ?></a>
            <?php endif; ?>
        <?php endfor; ?>
        
        <?php if ($pagePublic < $totalPagesPublic): ?>
            <a href="?page_p=<?= $pagePublic + 1 ?><?= $pageFollowed > 1 ? '&page_f='.$pageFollowed : '' ?>" style="margin: 0 5px;">Selanjutnya &raquo;</a>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <p style="margin-top: 10px; font-size: 12px;">Halaman <?= $pagePublic ?> dari <?= $totalPagesPublic ?> (Total: <?= $totalPublic ?> grup)</p>
</div>

    <a href="mahasiswa.php" class="back-link">← Kembali</a>
</div>

</body>
</html>

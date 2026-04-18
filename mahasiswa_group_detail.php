<?php
session_start();
if (!isset($_SESSION['nrp'])) {
    header("Location: login.php");
    exit();
}

require_once "class/koneksi.php"; 
require_once "class/mahasiswa.php"; 

$mahasiswa = new Mahasiswa();

$idgrup = isset($_GET['id']) ? intval($_GET['id']) : 0;
$username = $_SESSION['username'];
$group = $mahasiswa->ambilDetailGrup($idgrup);

if (!$group) {
    die("Grup tidak ditemukan.");
}

$isMember = $mahasiswa->cekMemberGrup($idgrup, $username);
if (!$isMember) {
    echo "<h2>Akses Ditolak</h2>";
    echo "<p>Anda bukan anggota grup ini, sehingga tidak dapat melihat detailnya.</p>";
    echo '<a href="mahasiswa_group.php">Kembali</a>';
    exit();
}

$creator = $mahasiswa->ambilPembuatGrup($group['username_pembuat']);


$event_page = max(1, intval($_GET['event_page'] ?? 1));
$member_page = max(1, intval($_GET['member_page'] ?? 1));
$perPage = 5;


$totalEvents = $mahasiswa->countGroupEvents($idgrup);
$eventOffset = ($event_page - 1) * $perPage;
$events = $mahasiswa->getGroupEventsPaging($idgrup, $perPage, $eventOffset);
$eventPages = ceil($totalEvents / $perPage);


$totalMembers = $mahasiswa->countGroupMembers($idgrup);
$memberOffset = ($member_page - 1) * $perPage;
$members = $mahasiswa->getGroupMembersPaging($idgrup, $perPage, $memberOffset);
$memberPages = ceil($totalMembers / $perPage);

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include "inc/head.php"; ?>
    <title>Detail Grup</title>
    <style>
        body { padding: 20px; }
        .card { background: var(--panel); color: var(--text); padding: 20px; border-radius: 8px; max-width: 800px; margin: auto; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid var(--chat-border); padding: 10px; color: var(--text); }
        th { background: var(--accent); color: white; }
        .btn { display: inline-block; padding: 8px 14px; background: var(--accent); color: white; text-decoration: none; border-radius: 4px; border: none; }
        .btn-danger { background: #dc3545; }
        .pagination { margin-top: 15px; display: flex; flex-wrap: wrap; gap: 5px; }
        .pagination a { padding: 6px 10px; background: var(--panel); color: var(--text); text-decoration: none; border-radius: 4px; border: 1px solid var(--chat-border); }
        .pagination .active { background: var(--accent); color: white; border-color: var(--accent); }
        hr { border: 0; border-top: 1px solid var(--chat-border); margin: 20px 0; }
        
        .table-responsive { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        td { word-break: break-word; }

        .event-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .event-card {
            background: var(--panel);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid var(--chat-border);
            display: flex;
            flex-direction: column;
            text-align: left;
        }
        .event-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        .event-poster {
            position: relative;
            height: 160px;
            background: var(--bg);
        }
        .event-poster img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .no-poster {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--text);
            opacity: 0.6;
            font-size: 14px;
            border-bottom: 1px solid var(--chat-border);
        }
        .event-badge {
            position: absolute;
            top: 10px;
            right: 10px;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: bold;
            color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .event-badge.publik { background: #28a745; }
        .event-badge.privat { background: #6f42c1; }

        .event-info {
            padding: 15px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }
        .event-info h4 {
            margin: 0 0 8px 0;
            font-size: 18px;
            color: var(--text);
        }
        .event-date {
            font-size: 13px;
            color: var(--accent);
            margin-bottom: 10px;
            font-weight: 600;
        }
        .event-desc {
            font-size: 14px;
            color: var(--text);
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
            margin-bottom: 10px;
            line-height: 1.5;
            flex-grow: 1;
        }

        @media (max-width: 600px) {
            .btn { display: block; width: 100%; text-align: center; margin: 0 0 10px 0; }
            table, thead, tbody, th, td, tr { display: block; }
            th { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid var(--chat-border); margin-bottom: 10px; }
            td { border: none; border-bottom: 1px solid var(--chat-border); position: relative; padding-left: 50%; text-align: right; }
            td:before { position: absolute; left: 10px; width: 45%; padding-right: 10px; white-space: nowrap; font-weight: bold; text-align: left; }
            #member-table td:nth-of-type(1):before { content: "Username"; }
            #member-table td:nth-of-type(2):before { content: "Nama"; }
            #member-table td:nth-of-type(3):before { content: "Jenis User"; }
        }
    </style>
</head>
<body>

<div class="card">
    <h2>Detail Grup</h2>

    <p><strong>Nama Grup:</strong> <?= htmlspecialchars($group['nama']) ?></p>
    <p><strong>Deskripsi:</strong> <?= htmlspecialchars($group['deskripsi']) ?></p>
    <p><strong>Jenis:</strong> <?= htmlspecialchars($group['jenis']) ?></p>
    <p><strong>Kode Pendaftaran:</strong> <?= htmlspecialchars($group['kode_pendaftaran']) ?> (hanya untuk grup publik)</p>
    <p><strong>Tanggal Dibuat:</strong> <?= htmlspecialchars($group['tanggal_pembentukan']) ?></p>

    <h3>Pembuat Grup</h3>
    <p><strong>Username:</strong> <?= htmlspecialchars($creator['username']) ?></p>
    <p><strong>Nama:</strong> <?= htmlspecialchars($creator['nama']) ?></p>

    <hr>


    <h3>Event Grup</h3>
<a href="thread.php?idgrup=<?= $idgrup ?>" class="btn" style="background:#198754;">
     Diskusi / Thread
</a>
    <?php if (empty($events)): ?>
        <p>Tidak ada event.</p>
    <?php else: ?>
        <div class="event-grid">
            <?php foreach ($events as $ev): ?>
            <div class="event-card">
                <div class="event-poster">
                    <?php if (!empty($ev['poster_extension'])): ?>
                        <img src="upload/event/<?= $ev['idevent'] . "." . $ev['poster_extension'] ?>" alt="Poster">
                    <?php else: ?>
                        <div class="no-poster"> Tidak ada poster</div>
                    <?php endif; ?>
                    <span class="event-badge <?= strtolower($ev['jenis']) ?>"><?= ucfirst($ev['jenis']) ?></span>
                </div>
                <div class="event-info">
                    <h4><?= htmlspecialchars($ev['judul']) ?></h4>
                    <p class="event-date"> <?= date('d M Y, H:i', strtotime($ev['tanggal'])) ?></p>
                    <p class="event-desc"><?= htmlspecialchars($ev['keterangan'] ?? '') ?></p>
                </div>
            </div>
            <?php endforeach; ?>
        </div>


        <div class="pagination">
            <?php if ($event_page > 1): ?>
                <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page-1 ?>&member_page=<?= $member_page ?>">« Prev</a>
            <?php endif; ?>

            <?php for ($p=1; $p <= $eventPages; $p++): ?>
                <a href="?id=<?= $idgrup ?>&event_page=<?= $p ?>&member_page=<?= $member_page ?>" 
                   class="<?= $p == $event_page ? 'active' : '' ?>">
                    <?= $p ?>
                </a>
            <?php endfor; ?>

            <?php if ($event_page < $eventPages): ?>
                <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page+1 ?>&member_page=<?= $member_page ?>">Next »</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <hr>


    <h3>Anggota Grup (<?= $totalMembers ?>)</h3>

    <div class="table-responsive">
        <table id="member-table">
            <tr>
                <th>Username</th>
                <th>Nama</th>
                <th>Jenis User</th>
            </tr>

            <?php foreach ($members as $m): ?>
            <tr>
                <td><?= htmlspecialchars($m['username']) ?></td>
                <td><?= htmlspecialchars($m['nama']) ?></td>
                <td><?= htmlspecialchars($m['jenis_user']) ?></td>
            </tr>
            <?php endforeach; ?>

        </table>
    </div>


    <div class="pagination">
        <?php if ($member_page > 1): ?>
            <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $member_page-1 ?>">« Prev</a>
        <?php endif; ?>

        <?php for ($p=1; $p <= $memberPages; $p++): ?>
            <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $p ?>" 
               class="<?= $p == $member_page ? 'active' : '' ?>">
                <?= $p ?>
            </a>
        <?php endfor; ?>

        <?php if ($member_page < $memberPages): ?>
            <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $member_page+1 ?>">Next »</a>
        <?php endif; ?>
    </div>

    <br>
    <a href="mahasiswa_group.php" class="btn">Kembali</a>

    <a href="mahasiswa_group.php?leave=<?= $idgrup ?>" 
       class="btn btn-danger">Keluar Grup</a>

</div>


</body>
</html>

<?php
session_start();
require_once "class/koneksi.php";
require_once "class/dosen.php";

if (!isset($_SESSION['npk'])) {
    header("Location: login.php");
    exit();
}

$dosen = new Dosen();
$username_dosen = $_SESSION['username'];
$idgrup = isset($_GET['id']) ? intval($_GET['id']) : 0;

$event_page = max(1, intval($_GET['event_page'] ?? 1));
$member_page = max(1, intval($_GET['member_page'] ?? 1));
$student_page = max(1, intval($_GET['student_page'] ?? 1));

$q = trim($_GET['q'] ?? '');
$perPage = 5;

$group = $dosen->getGroupDetail($idgrup);
if (!$group) {
    die("Grup tidak ditemukan.");
}

$isOwner = ($group['username_pembuat'] === $username_dosen);

$totalEvents = $dosen->countGroupEvents($idgrup);
$eventOffset = ($event_page - 1) * $perPage;
$events = $dosen->getGroupEvents($idgrup, $perPage, $eventOffset);
$eventPages = (int)ceil($totalEvents / $perPage);

$totalMembers = $dosen->countGroupMembers($idgrup);
$memberOffset = ($member_page - 1) * $perPage;
$members = $dosen->getGroupMembers($idgrup, $perPage, $memberOffset);
$memberPages = (int)ceil($totalMembers / $perPage);

$totalStudents = $dosen->countAvailableStudents($idgrup, $q);
$studentOffset = ($student_page - 1) * $perPage;
$students = $dosen->getAvailableStudents($idgrup, $perPage, $studentOffset, $q);
$studentPages = (int)ceil($totalStudents / $perPage);
?>
<!DOCTYPE html>
<html>
<head>
    <?php include "inc/head.php"; ?>
    <title>Detail Grup</title>
    <style>
        body { padding: 20px; }
        .container { max-width: 1000px; margin: auto; }
        .card { background: var(--panel); color: var(--text); padding: 20px; margin-bottom: 25px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid var(--chat-border); padding: 10px; color: var(--text); }
        th { background: var(--accent); color: white; }
        .btn { display: inline-block; padding: 6px 12px; background: var(--accent); color: white; border-radius: 4px; text-decoration: none; border: none; }
        .btn-danger { background: #dc3545; }
        .btn-small { font-size: 12px; padding: 4px 8px; }
        .pagination { margin-top: 10px; display: flex; flex-wrap: wrap; gap: 5px; }
        .pagination a { padding: 6px 10px; background: var(--panel); color: var(--text); text-decoration: none; border-radius: 4px; border: 1px solid var(--chat-border); }
        .pagination .active { background: var(--accent); color: white; border-color: var(--accent); }
        .search-form { margin-top: 10px; margin-bottom: 10px; }
        input[type=text] { background: var(--bg); color: var(--text); border: 1px solid var(--chat-border); padding: 8px; border-radius: 4px; box-sizing: border-box; }
        
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
            margin-bottom: 15px;
            line-height: 1.5;
            flex-grow: 1;
        }
        .event-actions {
            display: flex;
            gap: 8px;
            border-top: 1px solid var(--chat-border);
            padding-top: 12px;
        }

        @media (max-width: 600px) {
            .btn { display: block; width: 100%; text-align: center; margin: 0 0 10px 0; }
            .btn-small { display: block; width: 100%; margin-bottom: 5px; box-sizing: border-box; }
            .search-form input[type=text] { width: 100%; margin-bottom: 10px; }
            .search-form .btn { width: 100%; }
            
            table, thead, tbody, th, td, tr { display: block; }
            th { position: absolute; top: -9999px; left: -9999px; }
            tr { border: 1px solid var(--chat-border); margin-bottom: 10px; }
            td { border: none; border-bottom: 1px solid var(--chat-border); position: relative; padding-left: 50%; text-align: right; overflow-wrap: break-word; }
            td:before { position: absolute; left: 10px; width: 45%; padding-right: 10px; white-space: nowrap; font-weight: bold; text-align: left; }
            
            #member-table td:nth-of-type(1):before { content: "Username"; }
            #member-table td:nth-of-type(2):before { content: "Nama"; }
            #member-table td:nth-of-type(3):before { content: "Jenis"; }
            #member-table td:nth-of-type(4):before { content: "Aksi"; }
            
            #add-student-table td:nth-of-type(1):before { content: "NRP"; }
            #add-student-table td:nth-of-type(2):before { content: "Nama"; }
            #add-student-table td:nth-of-type(3):before { content: "Aksi"; }
        }
    </style>
</head>
<body>

<div class="container">

    <div class="card">
        <h2>Detail Grup</h2>
        <p><strong>Nama Grup:</strong> <?= htmlspecialchars($group['nama']) ?></p>
        <p><strong>Deskripsi:</strong> <?= htmlspecialchars($group['deskripsi']) ?></p>
        <p><strong>Jenis:</strong> <?= htmlspecialchars($group['jenis']) ?></p>
        <p><strong>Kode Pendaftaran:</strong> <?= htmlspecialchars($group['kode_pendaftaran']) ?></p>
        <p><strong>Tanggal Dibuat:</strong> <?= htmlspecialchars($group['tanggal_pembentukan']) ?></p>

<?php if ($isOwner): ?>
    <a href="dosen_group_edit.php?id=<?= $idgrup ?>" class="btn">Edit Grup</a>
<?php endif; ?>

<a href="thread.php?idgrup=<?= $idgrup ?>" class="btn" style="background:#198754;">
     Diskusi / Thread
</a>

    </div>

    <div class="card">
        <h3>Event Grup</h3>

        <?php if ($isOwner): ?>
            <a href="event_add.php?idgrup=<?= $idgrup ?>" class="btn">Tambah Event</a>
        <?php endif; ?>

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
                        <p class="event-date"><?= date('d M Y, H:i', strtotime($ev['tanggal'])) ?></p>
                        <p class="event-desc"><?= htmlspecialchars($ev['keterangan']) ?></p>
                        <div class="event-actions">
                            <?php if ($isOwner): ?>
                                <a class="btn-small" href="event_edit.php?id=<?= $ev['idevent'] ?>">Edit</a>
                                <a class="btn-small btn-danger" href="event_delete.php?id=<?= $ev['idevent'] ?>&grup=<?= $idgrup ?>">Hapus</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="pagination">
                <?php if ($event_page > 1): ?>
                    <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page-1 ?>&member_page=<?= $member_page ?>&student_page=<?= $student_page ?>&q=<?= urlencode($q) ?>">« Prev</a>
                <?php endif; ?>

                <?php for ($p=1;$p<=$eventPages;$p++): ?>
                    <a class="<?= $p==$event_page?'active':'' ?>" 
                       href="?id=<?= $idgrup ?>&event_page=<?= $p ?>&member_page=<?= $member_page ?>&student_page=<?= $student_page ?>&q=<?= urlencode($q) ?>">
                       <?= $p ?>
                    </a>
                <?php endfor; ?>

                <?php if ($event_page < $eventPages): ?>
                    <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page+1 ?>&member_page=<?= $member_page ?>&student_page=<?= $student_page ?>&q=<?= urlencode($q) ?>">Next »</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <h3>Anggota Grup (<?= $totalMembers ?>)</h3>

        <div class="table-responsive">
            <table id="member-table">
                <tr><th>Username</th><th>Nama</th><th>Jenis</th><th>Aksi</th></tr>
                <?php foreach ($members as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['username']) ?></td>
                    <td><?= htmlspecialchars($m['nama']) ?></td>
                    <td><?= htmlspecialchars($m['jenis_user']) ?></td>
                    <td>
                        <?php if ($isOwner && $m['username'] != $group['username_pembuat']): ?>
                            <a class="btn-small btn-danger" href="member_delete.php?u=<?= urlencode($m['username']) ?>&id=<?= $idgrup ?>">Hapus</a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="pagination">
            <?php if ($member_page > 1): ?>
                <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $member_page-1 ?>&student_page=<?= $student_page ?>&q=<?= urlencode($q) ?>">« Prev</a>
            <?php endif; ?>

            <?php for ($p=1;$p<=$memberPages;$p++): ?>
                <a class="<?= $p==$member_page?'active':'' ?>"
                   href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $p ?>&student_page=<?= $student_page ?>&q=<?= urlencode($q) ?>">
                   <?= $p ?>
                </a>
            <?php endfor; ?>

            <?php if ($member_page < $memberPages): ?>
                <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $member_page+1 ?>&student_page=<?= $student_page ?>&q=<?= urlencode($q) ?>">Next »</a>
            <?php endif; ?>
        </div>

        <?php if ($isOwner): ?>
        <h3>Tambah Member</h3>

        <form method="GET" class="search-form">
            <input type="hidden" name="id" value="<?= $idgrup ?>">
            <input type="text" name="q" placeholder="Cari NRP / Nama..." value="<?= htmlspecialchars($q) ?>">
            <button class="btn">Cari</button>
        </form>

        <div class="table-responsive">
            <table id="add-student-table">
                <tr><th>NRP</th><th>Nama</th><th>Aksi</th></tr>
                <?php foreach ($students as $s): ?>
                <tr>
                    <td><?= htmlspecialchars($s['nrp']) ?></td>
                    <td><?= htmlspecialchars($s['nama']) ?></td>
                    <td><a class="btn-small" href="member_add.php?id=<?= $idgrup ?>&nrp=<?= urlencode($s['nrp']) ?>">Tambah</a></td>
                </tr>
                <?php endforeach; ?>
            </table>
        </div>

        <div class="pagination">
            <?php if ($student_page > 1): ?>
                <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $member_page ?>&student_page=<?= $student_page-1 ?>&q=<?= urlencode($q) ?>">« Prev</a>
            <?php endif; ?>

            <?php for ($p=1;$p<=$studentPages;$p++): ?>
                <a class="<?= $p==$student_page?'active':'' ?>"
                   href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $member_page ?>&student_page=<?= $p ?>&q=<?= urlencode($q) ?>">
                   <?= $p ?>
                </a>
            <?php endfor; ?>

            <?php if ($student_page < $studentPages): ?>
                <a href="?id=<?= $idgrup ?>&event_page=<?= $event_page ?>&member_page=<?= $member_page ?>&student_page=<?= $student_page+1 ?>&q=<?= urlencode($q) ?>">Next »</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <a href="dosen_group.php" style="color: var(--accent); font-weight: bold;">Kembali</a>
</div>


</body>
</html>

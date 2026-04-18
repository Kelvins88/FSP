<?php
session_start();

if (!isset($_SESSION['isadmin'])) {
    if (isset($_SESSION['npk'])) {
        header("Location: dosen.php");
        exit();
    } elseif (isset($_SESSION['nrp'])) {
        header("Location: mahasiswa.php");
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}

require_once "class/Mahasiswa.php";
$mhs = new Mahasiswa();

if (isset($_POST['simpan'])) {
    $nrp = trim($_POST['nrp']);
    $nama = trim($_POST['nama']);
    $gender = $_POST['gender'];
    $tgl = $_POST['tanggal_lahir'];
    $angkatan = trim($_POST['angkatan']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($nrp === '' || $nama === '' || $username === '' || $password === '') {
        $error = "Lengkapi semua field wajib.";
    } else {
        try {
            if ($mhs->existsNrp($nrp)) throw new Exception("NRP '$nrp' sudah terdaftar.");
            if ($mhs->existsUsername($username)) throw new Exception("Username '$username' sudah digunakan.");
            $ext = strtolower(pathinfo($_FILES['foto']['name'] ?? '', PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed)) {
                throw new Exception("Format foto tidak valid. Hanya jpg, jpeg, png.");
            }

            if (!$mhs->insert($nrp, $nama, $gender, $tgl, $angkatan, $ext)) {
                throw new Exception("Gagal menyimpan data mahasiswa. Silakan coba lagi.");
            }

            if (!empty($_FILES['foto']['tmp_name'])) {
                $upload_dir = "./upload/mahasiswa";
                if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                    throw new Exception("Tidak bisa membuat folder untuk menyimpan foto.");
                }
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], "$upload_dir/{$nrp}.{$ext}")) {
                    throw new Exception("Gagal mengunggah foto.");
                }
            }

            $mhs->insertAkunMahasiswa($username, $password, $nrp);

            header("Location: mahasiswa_data.php");
            exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? intval($_GET['limit']) : 5;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

$totalData = $mhs->getTotalMahasiswa();

if ($limit === 0 || $limit >= $totalData) {
    $limit = $totalData > 0 ? $totalData : 1;
    $totalPages = 1;
    $page = 1;
} else {
    $totalPages = ceil($totalData / $limit);
}

if ($page > $totalPages) $page = 1;

$offset = ($page - 1) * $limit;
$result = $mhs->getAllWithUsername($limit, $offset);

?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php include "inc/head.php"; ?>
<title>Data Mahasiswa</title>
<style>
body { padding: 20px; }
.container { max-width: 1000px; margin: auto; background: var(--panel); color: var(--text); padding: 20px; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
table { border-collapse: collapse; width: 100%; margin-top: 15px; }
th, td { border: 1px solid var(--chat-border); padding: 8px; text-align: center; color: var(--text); }
th { background: var(--accent); color: white; }
a { text-decoration: none; }
a.btn { padding: 4px 8px; border-radius: 4px; color: white; font-weight: bold; }
a.edit { background: #28a745; }
a.hapus { background: #dc3545; }
a.reset { background: #ffc107; color: black; }
.pagination a { padding: 6px 10px; margin: 0 3px; border-radius: 5px; text-decoration: none; border: 1px solid var(--accent); color: var(--accent); transition: 0.2s; background: var(--bg); }
.pagination a:hover { background: var(--accent); color: white; }
.pagination .active { background: #28a745; color: white; border-color: #28a745; }
.pagination .disabled { opacity: 0.5; pointer-events: none; }
hr { border: 0; border-top: 1px solid var(--chat-border); margin: 20px 0; }
input[type=text], input[type=password], input[type=file], input[type=date], select { background: var(--bg); color: var(--text); border: 1px solid var(--chat-border); padding: 8px; border-radius: 4px; }
input[type=submit] { background: var(--accent); color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; }
</style>
</head>
<body>
<div class="container">
  <div style="margin-bottom: 15px;">
    <b>Login sebagai:</b> <?= htmlspecialchars($_SESSION['username']) ?> |
    <a href="admin.php">Kembali</a> |
    <a href="logout.php" style="color:red;font-weight:bold;">Logout</a>
  </div>

  <h2>Data Mahasiswa</h2>

  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>

  <form method="GET" style="margin-bottom: 10px;" id="limitForm">
    <label>Tampilkan: </label>
    <select name="limit">
      <option value="5"  <?= $limit == 5  ? 'selected' : '' ?>>5</option>
      <option value="10" <?= $limit == 10 ? 'selected' : '' ?>>10</option>
      <option value="25" <?= $limit == 25 ? 'selected' : '' ?>>25</option>
      <option value="50" <?= $limit == 50 ? 'selected' : '' ?>>50</option>
      <option value="0" <?= $limit == $totalData ? 'selected' : '' ?>>Semua</option>
    </select>
    <input type="hidden" name="page" value="<?= $page ?>">
  </form>

  <table>
    <tr>
      <th>NRP</th>
      <th>Nama</th>
      <th>Gender</th>
      <th>Tanggal Lahir</th>
      <th>Angkatan</th>
      <th>Username</th>
      <th>Foto</th>
      <th>Aksi</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <?php $foto = "upload/mahasiswa/{$row['nrp']}.{$row['foto_extention']}"; ?>
      <tr>
        <td><?= htmlspecialchars($row['nrp']) ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['gender']) ?></td>
        <td><?= htmlspecialchars($row['tanggal_lahir']) ?></td>
        <td><?= htmlspecialchars($row['angkatan']) ?></td>
        <td><?= htmlspecialchars($row['username'] ?? '-') ?></td>
        <td><img src="<?= $foto ?>?t=<?= time() ?>" width="80" alt="foto"></td>
        <td>
          <a href="edit_mahasiswa.php?nrp=<?= urlencode($row['nrp']) ?>" class="btn edit">Edit</a>
          <a href="hapus_mahasiswa.php?nrp=<?= urlencode($row['nrp']) ?>" class="btn hapus">Hapus</a>
          <a href="ganti_pwd_admin.php?nrp=<?= urlencode($row['nrp']) ?>" class="btn reset">Reset Password</a>
        </td>
      </tr>
    <?php endwhile; ?>
  </table>

  <div style="margin-top: 15px; text-align:center;">
    <?php if ($totalPages > 1): ?>
      <div class="pagination">
        <a href="?limit=<?= $limit ?>&page=<?= $page-1 ?>" class="<?= $page <= 1 ? 'disabled' : '' ?>">&laquo; Prev</a>
        <?php
          $start = max(1, $page - 2);
          $end   = min($totalPages, $page + 2);
          for ($i = $start; $i <= $end; $i++):
        ?>
          <a href="?limit=<?= $limit ?>&page=<?= $i ?>" class="<?= $i == $page ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
        <a href="?limit=<?= $limit ?>&page=<?= $page+1 ?>" class="<?= $page >= $totalPages ? 'disabled' : '' ?>">Next &raquo;</a>
      </div>
    <?php else: ?>
      <span>Menampilkan semua data (<?= $totalData ?> mahasiswa)</span>
    <?php endif; ?>
  </div>

  <h2>Tambah Mahasiswa</h2>
  <form method="POST" enctype="multipart/form-data" id="formTambah">
    <p>NRP: <input type="text" name="nrp" required></p>
    <p>Nama: <input type="text" name="nama" required></p>
    <p>Gender:
      <label><input type="radio" name="gender" value="1" required> Pria</label>
      <label><input type="radio" name="gender" value="2" required> Wanita</label>
    </p>
    <p>Tanggal Lahir: <input type="date" name="tanggal_lahir" required></p>
    <p>
    Angkatan: 
    <input  type="number" name="angkatan" min="1901" max="<?= date('Y') ?>" 
            title="Masukkan tahun antara 1901 sampai <?= date('Y') ?>" required>
    </p>
    <p>Username: <input type="text" name="username" required></p>
    <p>Password: <input type="password" name="password" required></p>
    <p>Foto: <input type="file" name="foto" accept="image/*" required></p>
    <p><input type="submit" name="simpan" value="Simpan"></p>
  </form>
</div>

<?php include "inc/theme_toggle.php"; ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

  $('.btn.hapus').on('click', function(){
    return confirm('Yakin hapus data ini?');
  });

  $('input[name="angkatan"]').on('input', function(){
    if (this.value.length > 4) {
      this.value = this.value.slice(0,4);
    }
  });

  $('select[name="limit"]').on('change', function(){
    $('#limitForm input[name="page"]').val(1);
    $('#limitForm').submit();
  });

});
</script>
</body>
</html>
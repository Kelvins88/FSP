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
require_once "class/Dosen.php";
$dosen = new Dosen();

if (isset($_POST['simpan'])) {
    $npk = trim($_POST['npk']);
    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if ($npk === '' || $nama === '' || $username === '' || $password === '') {
        $error = "Semua field wajib diisi!";
    } else {
        try {
            if ($dosen->existsNpk($npk)) {
                throw new Exception("NPK '$npk' sudah terdaftar.");
            }
            if ($dosen->existsUsername($username)) {
                throw new Exception("Username '$username' sudah digunakan.");
            }
            $ext = strtolower(pathinfo($_FILES['foto']['name'] ?? '', PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];
            if (!in_array($ext, $allowed)) {
                throw new Exception("Format foto tidak valid. Hanya jpg, jpeg, png.");
            }

            if (!$dosen->insert($npk, $nama, $ext)) {
                throw new Exception("Gagal menyimpan data dosen. Silakan coba lagi.");
            }

            if (!empty($_FILES['foto']['tmp_name'])) {
                $upload_dir = "./upload/dosen";
                if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true)) {
                    throw new Exception("Tidak bisa membuat folder untuk menyimpan foto.");
                }
                if (!move_uploaded_file($_FILES['foto']['tmp_name'], "$upload_dir/{$npk}.{$ext}")) {
                    throw new Exception("Gagal mengunggah foto. Pastikan file valid dan tidak terlalu besar.");
                }
            }

            $hash = password_hash($password, PASSWORD_DEFAULT);
            $dosen->insertAccount($username, $hash, $npk);

            header("Location: dosen_data.php");
            exit();
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}

$limit = isset($_GET['limit']) && is_numeric($_GET['limit']) ? intval($_GET['limit']) : 5;
$page  = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
if ($page < 1) $page = 1;

$totalData = $dosen->getTotalDosen();

if ($limit === 0 || $limit >= $totalData) {
    $limit = $totalData > 0 ? $totalData : 1;
    $totalPages = 1;
    $page = 1;
} else {
    $totalPages = ceil($totalData / $limit);
}

if ($page > $totalPages) $page = 1;

$offset = ($page - 1) * $limit;
$result = $dosen->getAllWithAccount($limit, $offset);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<?php include "inc/head.php"; ?>
<title>Data Dosen</title>
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
input[type=text], input[type=password], input[type=file] { background: var(--bg); color: var(--text); border: 1px solid var(--chat-border); padding: 8px; border-radius: 4px; }
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

  <h2>Data Dosen</h2>
  <?php if (!empty($error)) echo "<p style='color:red;'>$error</p>"; ?>
    <form method="GET" action="dosen_data.php" id="limitForm">
  <label>Tampilkan: </label>
  <select name="limit" onchange="this.form.submit()">
    <option value="5">5</option>
    <option value="10">10</option>
    <option value="25">25</option>
    <option value="50">50</option>
    <option value="0">Semua</option>
  </select>
  <input type="hidden" name="page" value="1">
</form>



  <table>
    <tr>
      <th>NPK</th>
      <th>Nama</th>
      <th>Username</th>
      <th>Foto</th>
      <th>Aksi</th>
    </tr>
    <?php while ($row = $result->fetch_assoc()): ?>
      <?php $foto = "upload/dosen/{$row['npk']}.{$row['foto_extension']}"; ?>
      <tr>
        <td><?= htmlspecialchars($row['npk']) ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= htmlspecialchars($row['username'] ?? '-') ?></td>
        <td><img src="<?= $foto ?>?t=<?= time() ?>" width="80" alt="foto"></td>
        <td>
          <a href="edit_dosen.php?npk=<?= urlencode($row['npk']) ?>" class="btn edit">Edit</a>
          <a href="hapus_dosen.php?npk=<?= urlencode($row['npk']) ?>" class="btn hapus">Hapus</a>
          <a href="ganti_pwd_admin.php?npk=<?= urlencode($row['npk']) ?>" class="btn reset">Reset Password</a>
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
      <span>Menampilkan semua data (<?= $totalData ?> dosen)</span>
    <?php endif; ?>
  </div>

  <h2>Tambah Dosen</h2>
  <form method="POST" enctype="multipart/form-data" id="formTambah">
    <p>NPK: <input type="text" name="npk" required></p>
    <p>Nama: <input type="text" name="nama" required></p>
    <p>Username: <input type="text" name="username" required></p>
    <p>Password: <input type="password" name="password" required></p>
    <p>Foto: <input type="file" name="foto" accept="image/*" required></p>
    <p><input type="submit" name="simpan" value="Simpan"></p>
  </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function(){

  $('.btn.hapus').on('click', function(){
    return confirm('Yakin hapus data ini?');
  });


});
</script>
</body>
</html>
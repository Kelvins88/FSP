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

$nrp = $_GET['nrp'] ?? '';
$data = $mhs->getByNrp($nrp);

if (!$data) {
    die("Data mahasiswa tidak ditemukan");
}

$error = [];
$success = '';

if (isset($_POST['update'])) {
    $new_nrp = trim($_POST['nrp']);
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama']);
    $gender = $_POST['gender'] == '1' ? 'Pria' : 'Wanita';
    $tgl = !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : null;
    $angkatan = $_POST['angkatan'];
    $ext = $data['foto_extention'];

    if ($new_nrp !== $nrp && $mhs->existsNrp($new_nrp)) {
        $error[] = "NRP '$new_nrp' sudah digunakan mahasiswa lain.";
    }

    if ($username !== $data['username'] && $mhs->usernameExists($username)) {
        $error[] = "Username '$username' sudah digunakan.";
    }

    if (!empty($_FILES['foto']['name'])) {
        $filename = $_FILES['foto']['name'];
        $tmp      = $_FILES['foto']['tmp_name'];
        $ext      = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $allowed  = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $allowed)) {
            $error[] = "Format foto tidak valid! Hanya boleh JPG, JPEG atau PNG.";
        } else {
            if (!is_dir("upload/mahasiswa")) {
                mkdir("upload/mahasiswa", 0755, true);
            }

            $target = "upload/mahasiswa/$new_nrp.$ext";

            foreach (['jpg', 'jpeg', 'png'] as $oldExt) {
                $oldFile = "upload/mahasiswa/$nrp.$oldExt";
                if (file_exists($oldFile)) {
                    unlink($oldFile);
                }
            }

            if (!move_uploaded_file($tmp, $target)) {
                $error[] = "Gagal mengunggah foto.";
            }
        }
    }

    if (empty($error)) {
        $ok = $mhs->update($nrp, $nama, $gender, $tgl, $angkatan, $ext, $new_nrp, $username);
        if ($ok) {
            header("Location: mahasiswa_data.php");
            exit();
        } else {
            $error[] = "Gagal update data.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <?php include "inc/head.php"; ?>
    <title>Edit Mahasiswa</title>
    <style>
        body { padding: 20px; }
        form { max-width: 500px; margin: auto; background: var(--panel); color: var(--text); padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        input[type=text], input[type=date], input[type=file], input[type=number] {
            width: 100%; padding: 8px; margin-bottom: 10px;
            border-radius: 4px; border: 1px solid var(--chat-border);
            background: var(--bg); color: var(--text);
            box-sizing: border-box;
        }
        input[type=submit] {
            background: var(--accent); color: white;
            border: none; padding: 10px 20px;
            border-radius: 5px; cursor: pointer;
        }
        input[type=submit]:hover { opacity: 0.9; }
        .error { color: #dc3545; }
        .success { color: #28a745; }
    </style>
</head>
<body>

<h2>Edit Mahasiswa</h2>

<?php if (!empty($error)): ?>
    <ul class="error">
        <?php foreach ($error as $e): ?>
            <li><?= htmlspecialchars($e) ?></li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php if ($success): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>NRP:</label>
    <input type="text" name="nrp" value="<?= htmlspecialchars($data['nrp']) ?>" required>

    <label>Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>

    <label>Nama:</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>

    <label>Gender:</label><br>
    <label><input type="radio" name="gender" value="1" <?= $data['gender']=='Pria'?'checked':'' ?>> Pria</label>
    <label><input type="radio" name="gender" value="2" <?= $data['gender']=='Wanita'?'checked':'' ?>> Wanita</label><br>

    <label>Tanggal Lahir:</label>
    <input type="date" name="tanggal_lahir" value="<?= htmlspecialchars($data['tanggal_lahir']) ?>" required>

    <label>Angkatan:</label>
    <input  type="number" name="angkatan" min="1901" max="<?= date('Y') ?>" 
            title="Masukkan tahun antara 1901 sampai <?= date('Y') ?>" required>
    </p>

    <label>Foto baru:</label>
    <input type="file" name="foto" accept="image/*" required>

    <input type="submit" name="update" value="Update">
</form>


</body>
</html>


<script src="assets/js/jquery.min.js"></script>
<script>
$(document).ready(function(){

  $('input[name="angkatan"]').on('input', function(){
    if (this.value.length > 4) {
      this.value = this.value.slice(0,4);
    }
  });

</script>
</body>
</html>
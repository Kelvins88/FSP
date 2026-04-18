<?php
session_start();

if (!isset($_SESSION['isadmin'])) {
    if (isset($_SESSION['npk'])) {
        header("Location: dosen.php");
    } elseif (isset($_SESSION['nrp'])) {
        header("Location: mahasiswa.php");
    } else {
        header("Location: login.php");
    }
    exit();
}

require_once "class/Dosen.php";
$dosen = new Dosen();

$npk = $_GET['npk'] ?? '';
$data = $dosen->getByNpk($npk);

if (!$data) {
    die("Data dosen tidak ditemukan");
}

$error = '';
$success = '';

if (isset($_POST['update'])) {
    $new_npk = trim($_POST['new_npk']);
    $username = trim($_POST['username']);
    $nama = trim($_POST['nama']);
    $ext = $data['foto_extension'];

    if ($new_npk !== $npk && $dosen->existsNpk($new_npk)) {
        $error = "NPK sudah digunakan dosen lain.";
    } elseif ($username !== $data['username'] && $dosen->usernameExists($username)) {
        $error = "Username sudah digunakan.";
    } else {
        if (!empty($_FILES['foto']['name'])) {
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png'];

            if (!in_array($ext, $allowed)) {
                $error = "Format foto tidak valid! Hanya boleh JPG, JPEG atau PNG.";
            } else {
                if (!is_dir("upload/dosen")) {
                    mkdir("upload/dosen", 0755, true);
                }

                $target = "upload/dosen/$new_npk.$ext";

                if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
                    $error = "Gagal mengunggah foto.";
                } else {
                    if ($new_npk !== $npk && file_exists("upload/dosen/$npk." . $data['foto_extension'])) {
                        unlink("upload/dosen/$npk." . $data['foto_extension']);
                    }
                }
            }
        }

        if (empty($error)) {
            if ($dosen->update($npk, $nama, $ext, $new_npk, $username)) {
                header("Location: dosen_data.php");
                exit();
            } else {
                $error = "Gagal update data.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <?php include "inc/head.php"; ?>
    <title>Edit Dosen</title>
    <style>
        body { padding: 20px; }
        form {
            max-width: 500px;
            margin: auto;
            background: var(--panel);
            color: var(--text);
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        input[type=text], input[type=file] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            border-radius: 4px;
            border: 1px solid var(--chat-border);
            background: var(--bg);
            color: var(--text);
            box-sizing: border-box;
        }
        input[type=submit] {
            background: var(--accent);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }
        input[type=submit]:hover {
            opacity: 0.9;
        }
        .error { color: #dc3545; }
        .success { color: #28a745; }
    </style>
</head>
<body>

<h2>Edit Dosen</h2>

<?php if (!empty($error)): ?>
    <p class="error"><?= htmlspecialchars($error) ?></p>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <p class="success"><?= htmlspecialchars($success) ?></p>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data">
    <label>NPK:</label>
    <input type="text" name="new_npk" value="<?= htmlspecialchars($data['npk']) ?>" required>

    <label>Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($data['username']) ?>" required>

    <label>Nama:</label>
    <input type="text" name="nama" value="<?= htmlspecialchars($data['nama']) ?>" required>

    <label>Foto baru:</label>
    <input type="file" name="foto" accept="image/*"required>

    <input type="submit" name="update" value="Update">
</form>


</body>
</html>
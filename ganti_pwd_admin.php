<?php
session_start();
require_once "class/Dosen.php";
require_once "class/Mahasiswa.php";

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
$npk = isset($_GET['npk']) ? $_GET['npk'] : null;
$nrp = isset($_GET['nrp']) ? $_GET['nrp'] : null;


if (!$nrp && !$npk) {
    echo "<p style='color:red;'>Data tidak valid.</p>";
    echo "<p><a href='admin.php'>Kembali</a></p>";
    exit();
}

if ($nrp) {
    $type = 'mahasiswa';
    $class = new Mahasiswa();
    $data = $class->getByNrp($nrp);
    $redirect = 'mahasiswa_data.php';
} else {
    $type = 'dosen';
    $class = new Dosen();
    $data = $class->getByNpk($npk);
    $redirect = 'dosen_data.php';
}

if (!$data) {
    echo "<p style='color:red;'>Akun tidak ditemukan.</p>";
    echo "<p><a href='$redirect'>Kembali</a></p>";
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newpass = $_POST['password'] ?? '';
    if (empty($newpass)) {
        $error = "Password baru tidak boleh kosong.";
    } else {
        try {
            if ($type === 'mahasiswa') {
                $class->updatePassword($nrp, $newpass);
            } else {
                $class->updatePassword($npk, $newpass);
            }
            echo "<p>Password berhasil diubah.</p>";
            echo "<p><a href='$redirect'>Kembali</a></p>";
            exit();
        } catch (Exception $e) {
            $error = "Gagal mengubah password: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<?php include "inc/head.php"; ?>
<title>Ubah Password <?= ucfirst($type) ?></title>
<style>
    body { padding: 20px; }
    .container {
        max-width: 500px;
        margin: auto;
        background: var(--panel);
        color: var(--text);
        padding: 20px;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h2 { margin-bottom: 15px; }
    button {
        background: var(--accent);
        color: white;
        border: none;
        padding: 10px 20px;
        border-radius: 5px;
        cursor: pointer;
    }
    button:hover { opacity: 0.9; }
    a {
        text-decoration: none;
        color: var(--accent);
        margin-left: 10px;
    }
    input {
        width: 100%;
        padding: 8px;
        margin: 5px 0 15px 0;
        border-radius: 4px;
        border: 1px solid var(--chat-border);
        background: var(--bg);
        color: var(--text);
        box-sizing: border-box;
    }
    .error { color: #dc3545; }
</style>
</head>
<body>

<div class="container">
    <h2>Ubah Password <?= ucfirst($type) ?></h2>

    <p>Anda akan mengubah password <?= $type ?> berikut:</p>
    <ul>
        <?php if ($type === 'mahasiswa'): ?>
            <li><b>NRP:</b> <?= htmlspecialchars($data['nrp']) ?></li>
        <?php else: ?>
            <li><b>NPK:</b> <?= htmlspecialchars($data['npk']) ?></li>
        <?php endif; ?>
        <li><b>Nama:</b> <?= htmlspecialchars($data['nama']) ?></li>
        <li><b>Username:</b> <?= htmlspecialchars($data['username']) ?></li>
    </ul>

    <?php if (!empty($error)) echo "<p class='error'>$error</p>"; ?>

    <form method="POST">
        <label for="password">Password Baru:</label>
        <input type="password" name="password" id="password" required>
        <button type="submit" onclick="return confirm('Yakin ingin mengubah password?')">Ubah Password</button>
        <a href="<?= $redirect ?>">Batal</a>
    </form>
</div>


</body>
</html>
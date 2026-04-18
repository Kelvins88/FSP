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

$npk = $_GET['npk'];
if (isset($_GET['npk'])) {
    $npk = $_GET['npk'];
} else {
    $npk = '';
}
$data = $dosen->getByNpk($npk);

if ($data) {
    $foto = "upload/dosen/{$data['npk']}.{$data['foto_extention']}";
    if (file_exists($foto)) unlink($foto);

    if ($dosen->delete($npk)) {
        header("Location: dosen_data.php");
        exit;
    } else {
        echo "Gagal hapus data.";
    }
} else {
    echo "Data dosen tidak ditemukan.";
}
?>

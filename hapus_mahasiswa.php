<?php
session_start();
if (!isset($_SESSION['username']) || !isset($_SESSION['isadmin'])) {
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

$npk = $_GET['nrp'];
if (isset($_GET['nrp'])) {
    $nrp = $_GET['nrp'];
} else {
    $nrp = '';
}
if ($nrp !== '') {
    $data = $mhs->getByNrp($nrp);

    if ($data) {
        $foto = "upload/mahasiswa/{$data['nrp']}.{$data['foto_extention']}";
        if (file_exists($foto)) {
            unlink($foto);
        }

        $mhs->delete($nrp);
    }
}

header("Location: mahasiswa_data.php");
exit;
?>

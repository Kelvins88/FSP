<?php
session_start();
require_once "class/koneksi.php";
require_once "class/dosen.php";

if (!isset($_SESSION['npk'])) {
    header("Location: login.php");
    exit();
}

$dosen = new Dosen();
$idgrup = intval($_GET['id'] ?? 0);
$username = $_SESSION['username'];

$success = $dosen->deleteGroup($idgrup, $username);

if (!$success) {
    die("Anda tidak berhak menghapus grup ini.");
}

header("Location: dosen_group.php?hapus=success");
exit();
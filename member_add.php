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
$nrp = $_GET['nrp'] ?? "";

if ($nrp) {
    $dosen->addMemberByNrp($idgrup, $nrp);
}

header("Location: dosen_group_detail.php?id=" . $idgrup);
exit();

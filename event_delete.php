<?php
session_start();
require_once "class/koneksi.php";
require_once "class/dosen.php";

if (!isset($_SESSION['npk'])) {
    header("Location: login.php");
    exit();
}

$dosen = new Dosen();
$idevent = intval($_GET['id'] ?? 0);
$idgrup = intval($_GET['grup'] ?? 0);

$dosen->deleteEvent($idevent);

header("Location: dosen_group_detail.php?id=" . $idgrup);
exit();

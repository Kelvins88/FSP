<?php
session_start();
require_once "class/thread.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

if (!isset($_GET['id']) || !isset($_GET['idgrup'])) {
    die("Parameter tidak valid");
}

$idthread = (int) $_GET['id'];
$idgrup   = (int) $_GET['idgrup'];
$username = $_SESSION['username'];

$threadObj = new Thread();

if ($threadObj->closeThread($idthread, $username)) {
    header("Location: thread.php?idgrup=" . $idgrup);
    exit;
} else {
    die("Gagal menutup thread atau Anda tidak berhak.");
}

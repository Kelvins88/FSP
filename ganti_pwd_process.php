<?php
session_start();
require_once "class/Dosen.php";

require_once "class/Mahasiswa.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit;
}

$username = isset($_SESSION['username']) ? $_SESSION['username'] : null;
$old_password = isset($_POST['old_password']) ? $_POST['old_password'] : '';
$new_password = isset($_POST['new_password']) ? $_POST['new_password'] : '';
$confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';

if (!$username) {
    header("Location: login.php");
    exit;
}

$result = null;
$redirect = '';

if (isset($_SESSION['npk'])) {
    $obj = new Dosen();
    $redirect = 'dosen.php';
} 
elseif (isset($_SESSION['nrp'])) {
    $obj = new Mahasiswa();
    $redirect = 'mahasiswa.php';
} 
else {
    echo "<p style='color:red;'>Akun tidak valid!</p>";
    echo "<a href='login.php'>Kembali</a>";
    exit;
}

$result = $obj->gantiPassword($username, $old_password, $new_password, $confirm_password);

if ($result[0] === true) {   
    session_unset();
    session_destroy();
    echo "<p style='color:green;'>{$result[1]}</p>"; 
    echo "<a href='login.php'>Silakan login kembali</a>";
} else {
    echo "<p style='color:red;'>{$result[1]}</p>";
    echo "<a href='ganti_pwd.php'>Kembali</a>";
}

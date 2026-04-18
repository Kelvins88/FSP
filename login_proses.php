<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

require_once "class/Login.php";

$username = trim(isset($_POST['username']) ? $_POST['username'] : '');
$password = trim(isset($_POST['password']) ? $_POST['password'] : '');

$login = new Login();
$role = $login->autentikasi($username, $password);

if ($role === false) {
    echo "Username atau password salah!";
        echo "
        <p></p>
        <a href='login.php' >Kembali ke Login</a>
    ";
    exit();
}

$_SESSION['username'] = $username;

if ($role === 'mahasiswa') {
    $_SESSION['nrp'] = $username;
    header("Location: mahasiswa.php");
    exit();
} 
else if ($role === 'dosen') {
    $_SESSION['npk'] = $username;
    header("Location: dosen.php");
    exit();
} 
else if ($role === 'admin') {
    $_SESSION['isadmin'] = true;
    header("Location: admin.php");
    exit();
} 
else {
    echo "Tipe akun tidak dikenali!";
    exit();
}

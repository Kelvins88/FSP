<?php
require_once("koneksi.php");

class Login extends Database {

    public function __construct() {
        parent::__construct();
    }

    public function autentikasi($username, $password) {
        if (!$this->mysqli) {
            return false;
        }

        $stmt = $this->mysqli->prepare("SELECT * FROM akun WHERE username = ?");
        if (!$stmt) {
            return false;
        }

        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            return false;
        }

        $data = $result->fetch_assoc();
        $stmt->close();

        $stored = isset($data['password']) ? $data['password'] : '';
        if (is_string($stored)) {
            $stored = trim($stored);
            $stored = trim($stored, "'\"");
        }

        if (!password_verify($password, $stored)) {
            if ($stored === $password) {
                $newHash = password_hash($password, PASSWORD_DEFAULT);
                $upd = $this->mysqli->prepare("UPDATE akun SET password = ? WHERE username = ?");
                if ($upd) {
                    $upd->bind_param("ss", $newHash, $username);
                    $upd->execute();
                    $upd->close();
                }
            } else {
                $is_md5 = (is_string($stored) && strlen($stored) === 32 && ctype_xdigit($stored));
                if ($is_md5 && $stored === md5($password)) {
                    $newHash = password_hash($password, PASSWORD_DEFAULT);
                    $upd = $this->mysqli->prepare("UPDATE akun SET password = ? WHERE username = ?");
                    if ($upd) {
                        $upd->bind_param("ss", $newHash, $username);
                        $upd->execute();
                        $upd->close();
                    }
                } else {
                    return false;
                }
            }
        }

        if (!empty($data['nrp_mahasiswa'])) {
            return 'mahasiswa';
        } 
        else if (!empty($data['npk_dosen'])) {
            return 'dosen';
        } 
        else if (!empty($data['isadmin']) && $data['isadmin'] == 1) {
            return 'admin';
        } 
        else {
            return false;
        }
    }
}
?>

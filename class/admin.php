<?php

require_once("koneksi.php");

class Admin extends Database {
    public function getPassword($username) {
            $sql = "SELECT password FROM akun WHERE username = ?";
        $stmt = $this->mysqli->prepare($sql);

    if (!$stmt) {
        throw new Exception("Gagal menyiapkan query getPassword: " . $this->mysqli->error);
    }

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $row = $result ? $result->fetch_assoc() : null;

        $stmt->close();

    if ($row && isset($row['password'])) {
        return $row['password'];
    } else {
        return null;
        }
    }

    public function updatePassword($username, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->mysqli->prepare("UPDATE akun SET password = ? WHERE username = ?");
        if (!$stmt) throw new Exception("Gagal menyiapkan query update password: " . $this->mysqli->error);
        $stmt->bind_param("ss", $hash, $username);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }
}
?>
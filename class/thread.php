<?php
require_once "koneksi.php";

class Thread extends Database
{
    public function getThreadsByGroup($idgrup)
    {
        $sql = "
            SELECT
                t.idthread,
                t.idgrup,
                t.username_pembuat,
                t.status,
                t.tanggal_pembuatan,
                COALESCE(d.nama, m.nama) AS nama_pembuat
            FROM thread t
            JOIN akun a ON t.username_pembuat = a.username
            LEFT JOIN dosen d ON a.npk_dosen = d.npk
            LEFT JOIN mahasiswa m ON a.nrp_mahasiswa = m.nrp
            WHERE t.idgrup = ?
            ORDER BY t.tanggal_pembuatan DESC
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function canAccessThread($username, $idthread)
    {
        $sql = "
            SELECT 1
            FROM thread t
            JOIN member_grup mg ON t.idgrup = mg.idgrup
            WHERE t.idthread = ?
              AND t.status = 'OPEN'
              AND mg.username = ?
            LIMIT 1
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $idthread, $username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function isMemberGroup($idgrup, $username)
    {
        $sql = "
            SELECT 1 FROM member_grup
            WHERE idgrup = ? AND username = ?
            LIMIT 1
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $idgrup, $username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function createThread($idgrup, $username, $status = 'OPEN')
    {
        $sql = "
            INSERT INTO thread (idgrup, username_pembuat, tanggal_pembuatan, status)
            VALUES (?, ?, NOW(), ?)
        ";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("iss", $idgrup, $username, $status);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function closeThread($idthread, $username)
    {
        $sql = "UPDATE thread SET status = 'CLOSE' WHERE idthread = ? AND username_pembuat = ?";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("is", $idthread, $username);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function isThreadCreator($idthread, $username)
    {
        $sql = "SELECT 1 FROM thread WHERE idthread = ? AND username_pembuat = ? LIMIT 1";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("is", $idthread, $username);
        $stmt->execute();
        $stmt->store_result();
        $isCreator = $stmt->num_rows > 0;
        $stmt->close();
        return $isCreator;
    }

    public function getThreadById($idthread)
    {
        $sql = "SELECT * FROM thread WHERE idthread = ? LIMIT 1";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return null;
        }
        $stmt->bind_param("i", $idthread);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }
}

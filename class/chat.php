<?php
require_once "koneksi.php";

class Chat extends Database
{
    public function getChats($idthread, $lastId = 0)
    {
        $sql = "
            SELECT c.*,
                   COALESCE(m.nama, d.nama) AS nama,
                   c.username_pembuat
            FROM chat c
            JOIN akun a ON c.username_pembuat = a.username
            LEFT JOIN mahasiswa m ON a.nrp_mahasiswa = m.nrp
            LEFT JOIN dosen d ON a.npk_dosen = d.npk
            WHERE c.idthread = ?
              AND c.idchat > ?
            ORDER BY c.idchat ASC
        ";

        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return [];
        }
        $stmt->bind_param("ii", $idthread, $lastId);
        $stmt->execute();

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    public function insertChat($idthread, $username, $isi)
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
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("is", $idthread, $username);
        $stmt->execute();
        $stmt->store_result();
        $canAccess = $stmt->num_rows > 0;
        $stmt->close();

        if (!$canAccess) {
            return false;
        }

        $sql = "
            INSERT INTO chat
            (idthread, username_pembuat, isi, tanggal_pembuatan)
            VALUES (?, ?, ?, NOW())
        ";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("iss", $idthread, $username, $isi);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
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
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("is", $idthread, $username);
        $stmt->execute();
        $stmt->store_result();
        $allowed = $stmt->num_rows > 0;
        $stmt->close();
        return $allowed;
    }
}
?>

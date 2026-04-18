<?php

require_once("koneksi.php");
class Dosen extends Database {

    public function getAll() {
        $sql = "SELECT npk, nama, foto_extension FROM dosen";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        return $result;
    }

    public function gantiPassword($username, $old_password, $new_password, $confirm_password) {
        if ($new_password !== $confirm_password) {
            $status = false;
            $message = 'Konfirmasi password tidak cocok!';
            return array($status, $message);
        }

        $stmt = $this->mysqli->prepare("SELECT password FROM akun WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            $stmt->close();
            $status = false;
            $message = 'Akun tidak ditemukan!';
            return array($status, $message);
        }

        $data = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($old_password, $data['password'])) {
            $status = false;
            $message = 'Password lama salah!';
            return array($status, $message);
        }

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        $update = $this->mysqli->prepare("UPDATE akun SET password = ? WHERE username = ?");
        $update->bind_param("ss", $new_hash, $username);
        $update->execute();

        $berhasil = $update->affected_rows > 0;
        $update->close();

        if ($berhasil) {
            $status = true;
            $message = 'Password berhasil diganti!';
            return array($status, $message);
        }

        $status = false;
        $message = 'Gagal mengganti password!';
        return array($status, $message);
    }

    public function getByNpk($npk) {
        $sql = "
            SELECT d.*, a.username
            FROM dosen d
            LEFT JOIN akun a ON d.npk = a.npk_dosen
            WHERE d.npk = ?
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public function insert($npk, $nama, $ext) {
        $sql = "INSERT INTO dosen (npk, nama, foto_extension) VALUES (?, ?, ?)";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sss", $npk, $nama, $ext);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update($old_npk, $nama, $ext, $new_npk = null, $username = null) {
        $sql = "
            UPDATE dosen d
            JOIN akun a ON d.npk = a.npk_dosen
            SET d.npk = ?, d.nama = ?, d.foto_extension = ?, 
                a.npk_dosen = ?, a.username = ?
            WHERE d.npk = ?
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ssssss", $new_npk, $nama, $ext, $new_npk, $username, $old_npk);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function delete($npk) {
        $sql = "DELETE FROM dosen WHERE npk = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $npk);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function usernameExists($username) {
        $sql = "SELECT username FROM akun WHERE username = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result();
        $exists = $res->num_rows > 0;
        $stmt->close();
        return $exists;
    }

    public function insertAccount($username, $passwordHash, $npk) {
        $sql = "
            INSERT INTO akun (username, password, npk_dosen, nrp_mahasiswa, isadmin) 
            VALUES (?, ?, ?, NULL, 0)
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sss", $username, $passwordHash, $npk);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function existsNpk($npk) {
        $npk = trim($npk);
        $stmt = $this->mysqli->prepare("SELECT 1 FROM dosen WHERE npk = ?");
        $stmt->bind_param("s", $npk);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return !empty($res);
    }

    public function existsUsername($username) {
        $username = trim($username);
        $stmt = $this->mysqli->prepare("SELECT 1 FROM akun WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return !empty($res);
    }

    public function isDosen($username) {
        $sql = "SELECT npk_dosen FROM akun WHERE username = ? LIMIT 1";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();
        return !empty($user['npk_dosen']);
    }

    public function updatePassword($npk, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE akun SET password = ? WHERE npk_dosen = ?";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            throw new Exception("Gagal menyiapkan query update password: " . $this->mysqli->error);
        }
        $stmt->bind_param("ss", $hash, $npk);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getTotalDosen() {
        $sql = "SELECT COUNT(*) AS total FROM dosen";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $stmt->close();

        if ($row && isset($row['total'])) {
            return $row['total'];
        } else {
            return 0;
        }
    }

    public function getAllWithAccount($limit = null, $offset = 0) {
        $sql = "
            SELECT d.npk, d.nama, d.foto_extension, a.username
            FROM dosen d
            LEFT JOIN akun a ON d.npk = a.npk_dosen
            ORDER BY d.npk ASC
        ";

        if ($limit !== null && is_numeric($limit)) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
        } else {
            $stmt = $this->mysqli->prepare($sql);
        }

        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        return $res;
    }

    public function getGroupsCreatedBy($username)
    {
        $sql = "
            SELECT * 
            FROM grup
            WHERE username_pembuat = ?
            ORDER BY tanggal_pembentukan DESC
        ";

        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return [];

        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $rows = $result->fetch_all(MYSQLI_ASSOC);

        $stmt->close();
        return $rows;
    }

    public function generateKodeUnik() {
        do {
            $kode = strtoupper(substr(md5(time() . rand()), 0, 6));

            $cek = $this->mysqli->prepare("
                SELECT 1 FROM grup WHERE kode_pendaftaran = ? LIMIT 1
            ");
            $cek->bind_param("s", $kode);
            $cek->execute();
            $cek->store_result();

            $exists = $cek->num_rows > 0;
            $cek->close();

        } while ($exists);

        return $kode;
    }

    public function buatGrup($username_pembuat, $nama, $deskripsi, $jenis) {
        $jenis = trim($jenis);
        if ($jenis !== 'Publik' && $jenis !== 'Privat') {
            return [false, "Jenis grup harus 'Publik' or 'Privat'"];
        }

        $kode = $this->generateKodeUnik();

        $stmt = $this->mysqli->prepare("
            INSERT INTO grup (username_pembuat, nama, deskripsi, tanggal_pembentukan, jenis, kode_pendaftaran)
            VALUES (?, ?, ?, NOW(), ?, ?)
        ");

        if (!$stmt) {
            return [false, "Query gagal: ".$this->mysqli->error];
        }

        $stmt->bind_param("sssss", $username_pembuat, $nama, $deskripsi, $jenis, $kode);
        $ok = $stmt->execute();

        if (!$ok) {
            $stmt->close();
            return [false, "Gagal membuat grup"];
        }

        $insertId = $stmt->insert_id;
        $stmt->close();

        $stmt2 = $this->mysqli->prepare("
            INSERT INTO member_grup (idgrup, username) VALUES (?, ?)
        ");
        if ($stmt2) {
            $stmt2->bind_param("is", $insertId, $username_pembuat);
            $stmt2->execute();
            $stmt2->close();
        }

        return [true, $insertId];
    }

    public function getGroupById($idgrup, $username)
    {
        $sql = "SELECT * FROM grup WHERE idgrup = ? AND username_pembuat = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $idgrup, $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function updateGroup($idgrup, $nama, $jenis, $deskripsi, $username)
    {
        $sql = "UPDATE grup 
                SET nama = ?, jenis = ?, deskripsi = ?
                WHERE idgrup = ? AND username_pembuat = ?";
                
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) {
            return false;
        }
        $stmt->bind_param("sssis", $nama, $jenis, $deskripsi, $idgrup, $username);

        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getGroupEvents($idgrup, $limit = 5, $offset = 0) {
        $sql = "SELECT * FROM event WHERE idgrup = ? ORDER BY tanggal ASC LIMIT ? OFFSET ?";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("iii", $idgrup, $limit, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getGroupMembers($idgrup, $limit = 5, $offset = 0) {
        $sql = "SELECT akun.username,
                       COALESCE(mahasiswa.nama, dosen.nama) AS nama,
                       CASE 
                            WHEN mahasiswa.nrp IS NOT NULL THEN 'Mahasiswa'
                            WHEN dosen.npk IS NOT NULL THEN 'Dosen'
                       END AS jenis_user
                FROM member_grup mg
                JOIN akun ON akun.username = mg.username
                LEFT JOIN mahasiswa ON mahasiswa.nrp = akun.nrp_mahasiswa
                LEFT JOIN dosen ON dosen.npk = akun.npk_dosen
                WHERE mg.idgrup = ?
                ORDER BY nama ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("iii", $idgrup, $limit, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function getAvailableStudents($idgrup, $limit = 5, $offset = 0, $q = '') {
        $like = "%$q%";
        $sql = "SELECT m.nrp, m.nama
                FROM mahasiswa m
                LEFT JOIN akun a ON a.nrp_mahasiswa = m.nrp
                WHERE (m.nrp LIKE ? OR m.nama LIKE ?)
                AND a.username NOT IN (SELECT username FROM member_grup WHERE idgrup = ?)
                ORDER BY m.nama ASC
                LIMIT ? OFFSET ?";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return [];
        $stmt->bind_param("ssiii", $like, $like, $idgrup, $limit, $offset);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $rows;
    }

    public function searchStudents($idgrup, $query) {
        $sql = "SELECT m.nrp, m.nama, a.username
                FROM mahasiswa m
                LEFT JOIN akun a ON a.nrp_mahasiswa = m.nrp
                WHERE a.username IS NOT NULL
                AND (m.nrp LIKE ? OR m.nama LIKE ?)
                AND a.username NOT IN (
                    SELECT username FROM member_grup WHERE idgrup = ?
                )
                ORDER BY m.nama ASC";
        
        $search = "%$query%";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ssi", $search, $search, $idgrup);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function getGroupsCreatedByPaged($username, $limit = 10, $offset = 0) {
        $sql = "SELECT * 
                FROM grup
                WHERE username_pembuat = ?
                ORDER BY tanggal_pembentukan DESC
                LIMIT ? OFFSET ?";
        
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sii", $username, $limit, $offset);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function countGroupsCreatedBy($username) {
        $sql = "SELECT COUNT(*) AS total 
                FROM grup
                WHERE username_pembuat = ?";
        
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0;
    }

    public function getEventById($idevent) {
        $sql = "SELECT * FROM event WHERE idevent = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $idevent);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result;
    }

    public function slugify($text) {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) return 'event';
        return $text;
    }

    public function addEvent($idgrup, $judul, $tanggal, $keterangan, $jenis, $poster_ext) {
        $slug = $this->slugify($judul);
        $sql = "INSERT INTO event (idgrup, judul, `judul-slug`, tanggal, keterangan, jenis, poster_extension)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return false;

        $stmt->bind_param("issssss", $idgrup, $judul, $slug, $tanggal, $keterangan, $jenis, $poster_ext);
        $ok = $stmt->execute();
        $stmt->close();

        return $ok;
    }

    public function updateEvent($idevent, $judul, $tanggal, $keterangan, $jenis, $poster_ext) {
        $slug = $this->slugify($judul);
        $sql = "UPDATE event 
                SET judul = ?, `judul-slug` = ?, tanggal = ?, keterangan = ?, jenis = ?, poster_extension = ?
                WHERE idevent = ?";
        
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ssssssi", $judul, $slug, $tanggal, $keterangan, $jenis, $poster_ext, $idevent);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function deleteEvent($idevent) {
        $sql = "DELETE FROM event WHERE idevent = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $idevent);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function addMemberByNrp($idgrup, $nrp) {
        $sql = "SELECT username FROM akun WHERE nrp_mahasiswa = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $nrp);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($user) {
            $username = $user['username'];
            
            $cek = $this->mysqli->prepare("SELECT 1 FROM member_grup WHERE idgrup = ? AND username = ?");
            $cek->bind_param("is", $idgrup, $username);
            $cek->execute();
            $cek->store_result();
            
            if ($cek->num_rows == 0) {
                $sql = "INSERT INTO member_grup (idgrup, username) VALUES (?, ?)";
                $stmt = $this->mysqli->prepare($sql);
                $stmt->bind_param("is", $idgrup, $username);
                $stmt->execute();
                $stmt->close();
            }
            $cek->close();
        }
    }

    public function deleteMember($idgrup, $username) {
        $sql = "DELETE FROM member_grup WHERE idgrup = ? AND username = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $idgrup, $username);
        $stmt->execute();
        $stmt->close();
    }

    public function deleteGroup($idgrup, $username) {
        $stmt = $this->mysqli->prepare("SELECT idgrup FROM grup WHERE idgrup = ? AND username_pembuat = ?");
        $stmt->bind_param("is", $idgrup, $username);
        $stmt->execute();
        $res = $stmt->get_result();
        
        if ($res->num_rows == 0) {
            $stmt->close();
            return false;
        }
        $stmt->close();
        
        $stmt = $this->mysqli->prepare("DELETE FROM event WHERE idgrup = ?");
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->mysqli->prepare("DELETE FROM member_grup WHERE idgrup = ?");
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        $stmt->close();
        
        $stmt = $this->mysqli->prepare("DELETE FROM grup WHERE idgrup = ?");
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        $stmt->close();
        
        return true;
    }

    public function cekGrupMilikDosen($idgrup, $npk) {
        $sql = "SELECT COUNT(*) FROM grup WHERE idgrup = ? AND username_pembuat = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $idgrup, $npk);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function getGroupEventsPaging($id, $limit, $offset) {
        $stmt = $this->mysqli->prepare("SELECT * FROM event WHERE idgrup = ? ORDER BY tanggal DESC LIMIT ? OFFSET ?");
        $stmt->bind_param("iii", $id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countGroupEvents($idgrup) {
        $sql = "SELECT COUNT(*) AS total FROM event WHERE idgrup = ?";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return isset($row['total']) ? (int)$row['total'] : 0;
    }

    public function getGroupMembersPaging($id, $limit, $offset) {
        $stmt = $this->mysqli->prepare("
            SELECT gm.*, a.nama, a.username, a.jenis_user 
            FROM member_grup gm
            JOIN akun a ON gm.username = a.username
            WHERE gm.idgrup = ?
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countGroupMembers($id) {
        $stmt = $this->mysqli->prepare("SELECT COUNT(*) AS total FROM member_grup WHERE idgrup = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc()['total'];
    }

    public function getAvailableStudentsPaging($id, $limit, $offset) {
        $stmt = $this->mysqli->prepare("
            SELECT m.nrp, m.nama
            FROM mahasiswa m
            WHERE m.username NOT IN (SELECT username FROM member_grup WHERE idgrup = ?)
            LIMIT ? OFFSET ?
        ");
        $stmt->bind_param("iii", $id, $limit, $offset);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function countAvailableStudents($idgrup, $q = '') {
        $like = "%$q%";
        $sql = "SELECT COUNT(*) AS total
                FROM mahasiswa m
                LEFT JOIN akun a ON a.nrp_mahasiswa = m.nrp
                WHERE (m.nrp LIKE ? OR m.nama LIKE ?)
                AND a.username NOT IN (SELECT username FROM member_grup WHERE idgrup = ?)";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return 0;
        $stmt->bind_param("ssi", $like, $like, $idgrup);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return isset($row['total']) ? (int)$row['total'] : 0;
    }

    public function getGroupDetail($idgrup) {
        $sql = "SELECT * FROM grup WHERE idgrup = ? LIMIT 1";
        $stmt = $this->mysqli->prepare($sql);
        if (!$stmt) return null;
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public function createThread($idgrup, $username, $judul) {
        $sql = "
            INSERT INTO thread (idgrup, username_pembuat, judul, tanggal_pembuatan, status)
            VALUES (?, ?, ?, NOW(), 'OPEN')
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $idgrup, $username, $judul);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getThreadsByGroup($idgrup)
    {
        $sql = "
            SELECT
                t.idthread,
                t.username_pembuat,
                t.status,
                t.tanggal_pembuatan,
                COALESCE(d.nama, m.nama) AS nama_pembuat
            FROM thread t
            LEFT JOIN akun a ON t.username_pembuat = a.username
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
            SELECT t.idthread
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

        $allowed = $stmt->num_rows > 0;
        $stmt->close();

        return $allowed;
    }

    public function closeThread($idthread, $username) {
        $sql = "
            UPDATE thread
            SET status = 'CLOSE'
            WHERE idthread = ? AND username_pembuat = ?
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("is", $idthread, $username);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function getChats($idthread, $lastId)
    {
        $sql = "
            SELECT c.*,
                   COALESCE(m.nama, d.nama) AS nama
            FROM chat c
            JOIN akun a ON c.username_pembuat = a.username
            LEFT JOIN mahasiswa m ON a.nrp_mahasiswa = m.nrp
            LEFT JOIN dosen d ON a.npk_dosen = d.npk
            WHERE c.idthread = ?
              AND c.idchat > ?
            ORDER BY c.idchat ASC
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ii", $idthread, $lastId);
        $stmt->execute();

        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $rows;
    }

    public function insertChat($idthread, $username, $isi)
    {
        if (!$this->canAccessThread($username, $idthread)) {
            return false;
        }

        $sql = "
            INSERT INTO chat
            (idthread, username_pembuat, isi, tanggal_pembuatan)
            VALUES (?, ?, ?, NOW())
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("iss", $idthread, $username, $isi);
        $stmt->execute();
        $stmt->close();
    }
}
?>
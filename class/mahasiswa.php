<?php
require_once("koneksi.php");

class Mahasiswa extends Database {

    public function __construct() {
        parent::__construct();
    }

    public function getAll() {
        $sql = "SELECT * FROM mahasiswa";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->execute();
        $res = $stmt->get_result();
        $stmt->close();
        return $res;
    }

    public function getByNrp($nrp) {
        $sql = "
            SELECT m.*, a.username 
            FROM mahasiswa m
            LEFT JOIN akun a ON m.nrp = a.nrp_mahasiswa
            WHERE m.nrp = ?
        ";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $nrp);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $res;
    }

    public function getAllWithUsername($limit = null, $offset = null) {
        $sql = "
            SELECT m.*, a.username 
            FROM mahasiswa m 
            LEFT JOIN akun a ON m.nrp = a.nrp_mahasiswa
            ORDER BY m.nrp ASC
        ";

        if ($limit !== null && $limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $stmt = $this->mysqli->prepare($sql);
            $stmt->bind_param("ii", $limit, $offset);
        } else {
            $stmt = $this->mysqli->prepare($sql);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public function getTotalMahasiswa() {
        $sql = "SELECT COUNT(*) as total FROM mahasiswa";
        $result = $this->mysqli->query($sql);
        $row = $result->fetch_assoc();
        return $row['total'] ?? 0;
    }

    public function insert($nrp, $nama, $gender, $tgl, $angkatan, $ext) {
        $sql = "
            INSERT INTO mahasiswa (nrp, nama, gender, tanggal_lahir, angkatan, foto_extention)
            VALUES (?, ?, ?, ?, ?, ?)
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ssssis", $nrp, $nama, $gender, $tgl, $angkatan, $ext);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function update($old_nrp, $nama, $gender, $tgl, $angkatan, $ext, $new_nrp, $username) {
        $sql = "
            UPDATE mahasiswa m
            JOIN akun a ON m.nrp = a.nrp_mahasiswa
            SET m.nrp = ?, 
                m.nama = ?, 
                m.gender = ?, 
                m.tanggal_lahir = ?, 
                m.angkatan = ?, 
                m.foto_extention = ?,
                a.nrp_mahasiswa = ?, 
                a.username = ?
            WHERE m.nrp = ?
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ssssissss", $new_nrp, $nama, $gender, $tgl, $angkatan, $ext, $new_nrp, $username, $old_nrp);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function delete($nrp) {
        $sql = "DELETE FROM mahasiswa WHERE nrp = ?";
        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $nrp);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function insertAkunMahasiswa($username, $password, $nrp) {
        $username = trim($username);
        $nrp = trim($nrp);
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "
            INSERT INTO akun (username, password, nrp_mahasiswa, npk_dosen, isadmin)
            VALUES (?, ?, ?, NULL, 0)
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sss", $username, $hash, $nrp);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function gantiPassword($username, $old_password, $new_password, $confirm_password) {
        if ($new_password !== $confirm_password) {
            return [false, 'Konfirmasi password tidak cocok!'];
        }

        $stmt = $this->mysqli->prepare("SELECT password FROM akun WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return [false, 'Akun tidak ditemukan!'];
        }

        $data = $result->fetch_assoc();
        $stmt->close();

        if (!password_verify($old_password, $data['password'])) {
            return [false, 'Password lama salah!'];
        }

        $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
        
        $update = $this->mysqli->prepare("UPDATE akun SET password = ? WHERE username = ?");
        $update->bind_param("ss", $new_hash, $username);
        $update->execute();

        if ($update->affected_rows > 0) {
            return [true, 'Password berhasil diganti!'];
        }

        return [false, 'Gagal mengganti password!'];
    }

    public function updatePassword($nrp, $password) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $sql = "UPDATE akun SET password = ? WHERE nrp_mahasiswa = ?";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("ss", $hash, $nrp);
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

    public function existsNrp($nrp) {
        $stmt = $this->mysqli->prepare("SELECT 1 FROM mahasiswa WHERE nrp = ?");
        $stmt->bind_param("s", $nrp);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return !empty($res);
    }

    public function existsUsername($username) {
        $stmt = $this->mysqli->prepare("SELECT 1 FROM akun WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return !empty($res);
    }

    private function stmt_fetch_all($stmt) {
        if (method_exists($stmt, 'get_result')) {
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }

        $meta = $stmt->result_metadata();
        if (!$meta) return [];

        $row = [];
        $bind = [];
        while ($field = $meta->fetch_field()) {
            $row[$field->name] = null;
            $bind[] = &$row[$field->name];
        }

        call_user_func_array([$stmt, 'bind_result'], $bind);

        $results = [];
        while ($stmt->fetch()) {
            $results[] = array_map(fn($v) => $v, $row);
        }

        return $results;
    }

    public function getGroupsFollowed($username) {
        $sql = "
            SELECT g.* FROM grup g
            JOIN member_grup mg ON g.idgrup = mg.idgrup
            WHERE mg.username = ?
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $rows = $this->stmt_fetch_all($stmt);
        $stmt->close();
        return $rows;
    }

    public function getPublicGroupsNotJoined($username) {
        $sql = "
            SELECT * FROM grup
            WHERE jenis = 'Publik'
            AND idgrup NOT IN (
                SELECT idgrup FROM member_grup WHERE username = ?
            )
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $rows = $this->stmt_fetch_all($stmt);
        $stmt->close();
        return $rows;
    }

    public function joinPublicGroup($idgrup, $kode, $username) {
        $cek = $this->mysqli->prepare("SELECT idgrup, kode_pendaftaran FROM grup WHERE idgrup = ? AND jenis = 'Publik' LIMIT 1");
        $cek->bind_param("i", $idgrup);
        $cek->execute();
        $data = $this->stmt_fetch_all($cek);
        $cek->close();

        if (empty($data)) return [false, "Grup tidak ditemukan atau bukan grup publik."];
        if ($data[0]['kode_pendaftaran'] !== $kode) return [false, "Kode pendaftaran salah!"];

        $cek2 = $this->mysqli->prepare("SELECT 1    FROM member_grup WHERE idgrup = ? AND username = ? LIMIT 1");
        $cek2->bind_param("is", $idgrup, $username);
        $cek2->execute();
        $cek2->store_result();
        if ($cek2->num_rows > 0) return [false, "Anda sudah tergabung dalam grup ini."];
        $cek2->close();

        $ins = $this->mysqli->prepare("INSERT INTO member_grup (idgrup, username) VALUES (?, ?)");
        $ins->bind_param("is", $idgrup, $username);
        $ok = $ins->execute();
        $ins->close();

        return $ok ? [true, "Berhasil bergabung ke grup!"] : [false, "Gagal bergabung."];
    }

    public function leaveGroup($idgrup, $username) {
        $stmt = $this->mysqli->prepare("DELETE FROM member_grup WHERE idgrup = ? AND username = ?");
        $stmt->bind_param("is", $idgrup, $username);
        $ok = $stmt->execute();
        $stmt->close();
        return $ok;
    }

    public function ambilDetailGrup($idgrup) {
        $stmt = $this->mysqli->prepare("SELECT * FROM grup WHERE idgrup = ? LIMIT 1");
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function cekMemberGrup($idgrup, $username) {
        $stmt = $this->mysqli->prepare("SELECT 1 FROM member_grup WHERE idgrup = ? AND username = ?");
        $stmt->bind_param("is", $idgrup, $username);
        $stmt->execute();
        $stmt->store_result();
        return $stmt->num_rows > 0;
    }

    public function ambilPembuatGrup($usernamePembuat) {
        $sql = "
            SELECT akun.username,
                   COALESCE(mahasiswa.nama, dosen.nama) AS nama
            FROM akun
            LEFT JOIN mahasiswa ON mahasiswa.nrp = akun.nrp_mahasiswa
            LEFT JOIN dosen ON dosen.npk = akun.npk_dosen
            WHERE akun.username = ?
            LIMIT 1
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $usernamePembuat);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function ambilAnggotaGrup($idgrup) {
        $sql = "
            SELECT akun.username,
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
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getGroupEvents($idgrup) {
        $stmt = $this->mysqli->prepare("SELECT * FROM event WHERE idgrup = ? ORDER BY tanggal ASC");
        $stmt->bind_param("i", $idgrup);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $result;
    }

    public function getGroupsFollowedPaged($username, $limit = 10, $offset = 0) {
        $sql = "
            SELECT g.* FROM grup g
            JOIN member_grup mg ON g.idgrup = mg.idgrup
            WHERE mg.username = ?
            ORDER BY g.tanggal_pembentukan DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sii", $username, $limit, $offset);
        $stmt->execute();
        $rows = $this->stmt_fetch_all($stmt);
        $stmt->close();
        return $rows;
    }

    public function countGroupsFollowed($username) {
        $sql = "
            SELECT COUNT(*) AS total FROM grup g
            JOIN member_grup mg ON g.idgrup = mg.idgrup
            WHERE mg.username = ?
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0;
    }

    public function getPublicGroupsNotJoinedPaged($username, $limit = 10, $offset = 0) {
        $sql = "
            SELECT * FROM grup
            WHERE jenis = 'Publik'
            AND idgrup NOT IN (
                SELECT idgrup FROM member_grup WHERE username = ?
            )
            ORDER BY tanggal_pembentukan DESC
            LIMIT ? OFFSET ?
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("sii", $username, $limit, $offset);
        $stmt->execute();
        $rows = $this->stmt_fetch_all($stmt);
        $stmt->close();
        return $rows;
    }

    public function countPublicGroupsNotJoined($username) {
        $sql = "
            SELECT COUNT(*) AS total FROM grup
            WHERE jenis = 'Publik'
            AND idgrup NOT IN (
                SELECT idgrup FROM member_grup WHERE username = ?
            )
        ";

        $stmt = $this->mysqli->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        return $result['total'] ?? 0;
    }

   public function countGroupMembers($idgrup) {
    $stmt = $this->mysqli->prepare("
        SELECT COUNT(*) AS total 
        FROM member_grup 
        WHERE idgrup = ?
    ");
    $stmt->bind_param("i", $idgrup);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}
public function countGroupEvents($idgrup) {
    $stmt = $this->mysqli->prepare("
        SELECT COUNT(*) AS total 
        FROM event 
        WHERE idgrup = ?
    ");
    $stmt->bind_param("i", $idgrup);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['total'] ?? 0;
}

public function getGroupMembersPaging($idgrup, $limit = 5, $offset = 0) {
    $sql = "
        SELECT akun.username,
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
        LIMIT ? OFFSET ?
    ";

    $stmt = $this->mysqli->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param("iii", $idgrup, $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}
public function getGroupEventsPaging($idgrup, $limit = 5, $offset = 0) {
    $sql = "
        SELECT *
        FROM event
        WHERE idgrup = ?
        ORDER BY tanggal DESC
        LIMIT ? OFFSET ?
    ";

    $stmt = $this->mysqli->prepare($sql);
    if (!$stmt) return [];

    $stmt->bind_param("iii", $idgrup, $limit, $offset);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    return $rows;
}
}
?>
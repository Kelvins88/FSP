<?php

require_once("data.php");

class Database {
    protected $mysqli;

    public function __construct() {
           try {
            $this->mysqli = new mysqli(SERVER, USERID, PWD, DB);

            if ($this->mysqli->connect_errno) {
                throw new Exception("Koneksi gagal: " . $this->mysqli->connect_error);
            }
        } catch (mysqli_sql_exception $e) {
            die("Gagal konek ke database. Pastikan database '" . DB . " sudah di import");
        } catch (Exception $e) {
            die("Terjadi kesalahan: " . $e->getMessage());
        }
    }

    public function getConnection() {
        return $this->mysqli;
    }

    public function __destruct() {
        if ($this->mysqli) {
            $this->mysqli->close();
        }
    }
}
?>

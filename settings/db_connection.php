<?php
class DB_Connection {
    protected function connect() {
        try {
            $dsn = "mysql:host=localhost;dbname=shoppn";
            $username = "root";
            $password = "";

            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            return $pdo;
        } catch (PDOException $e) {
            die("DB Connection failed: " . $e->getMessage());
        }
    }
}
?>

<?php
// config/database.php - Connexion PDO

class Database {
    private static $instance = null;
    private $pdo;
    
    private $host = 'localhost';
    private $dbname = 'clinic_rdv';
    private $username = 'root';
    private $password = '';
    
    private function __construct() {
        try {
            $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ];
            $this->pdo = new PDO($dsn, $this->username, $this->password, $options);
        } catch(PDOException $e) {
            die("Erreur de connexion : " . $e->getMessage());
        }
    }
    
    public static function getInstance() {
        if(self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->pdo;
    }
}

function getPDO() {
    return Database::getInstance()->getConnection();
}
?>
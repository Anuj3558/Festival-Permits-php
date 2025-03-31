<?php
class Database {
    private $host = 'localhost';
    private $port = '3307';  // ✅ Updated MySQL port
    private $db_name = 'festival_permit_system';
    private $username = 'root';
    private $password = '';
    public $conn;  // ✅ Changed to public so other classes can access it
    
    public function connect() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host={$this->host};port={$this->port};dbname={$this->db_name}",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            error_log("❌ Database Connection Error: " . $e->getMessage()); // ✅ Logs error to PHP logs
            die("⚠️ Database connection failed. Please try again later."); // ✅ User-friendly error message
        }
        
        return $this->conn;
    }
}
?>

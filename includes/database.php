<?php
// includes/database.php

// Include configuration
require_once 'config.php';

/**
 * Database connection class
 */
class Database {
    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    public $conn;
    
    /**
     * Get database connection
     * @return PDO|null
     */
    public function getConnection() {
        $this->conn = null;
        
        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8", 
                $this->username, 
                $this->password
            );
            
            // Set error mode to exceptions
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Set default fetch mode to associative array
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            if (DEBUG_MODE) {
                die("Connection error: " . $exception->getMessage());
            } else {
                error_log("Database connection failed: " . $exception->getMessage());
                die("Database connection failed. Please try again later.");
            }
        }
        
        return $this->conn;
    }
    
    /**
     * Execute a query with parameters
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return PDOStatement|false
     */
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch(PDOException $e) {
            if (DEBUG_MODE) {
                die("Query failed: " . $e->getMessage());
            } else {
                error_log("Query error: " . $e->getMessage());
                return false;
            }
        }
    }
}

// Create database instance
$database = new Database();
$pdo = $database->getConnection();

// Check if admin user exists, if not create default admin
function createDefaultAdmin($pdo) {
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM admins WHERE username = 'admin'");
    $stmt->execute();
    $adminExists = $stmt->fetchColumn();
    
    if (!$adminExists) {
        // Create default admin user with the password hash from your database
        $hashedPassword = '$2y$10$r3B6W7X8Y9Z0A1B2C3D4Ee5F6G7H8I9J0K1L2M3N4O5P6Q7R8S9T0'; // Your existing hash
        $stmt = $pdo->prepare("
            INSERT INTO admins (username, email, password, created_at) 
            VALUES (?, ?, ?, NOW())
        ");
        
        $stmt->execute(['admin', 'admin@vrtour.com', $hashedPassword]);
        
        if (DEBUG_MODE) {
            error_log("Default admin user created: admin / admin123");
        }
    }
}

// Create default admin if needed
createDefaultAdmin($pdo);
?>
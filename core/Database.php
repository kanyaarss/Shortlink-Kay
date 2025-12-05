<?php
/**
 * Database Class - PDO Wrapper
 * 
 * Mengelola semua database operations dengan PDO.
 * Menggunakan prepared statements untuk security.
 * 
 * Usage:
 *   $db = new Database($config);
 *   $row = $db->fetch("SELECT * FROM links WHERE code = ?", ['abc123']);
 *   $rows = $db->fetchAll("SELECT * FROM links WHERE is_active = ?", [true]);
 *   $db->execute("INSERT INTO links (code, url) VALUES (?, ?)", ['code', 'url']);
 */

class Database
{
    private $pdo;
    private $config;
    private $error;
    private $last_query;
    
    /**
     * Constructor - Initialize database connection
     * 
     * @param array $config Database configuration
     * @throws Exception Jika koneksi gagal
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->connect();
    }
    
    /**
     * Connect to database
     * 
     * @return void
     * @throws Exception Jika koneksi gagal
     */
    private function connect()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%d;dbname=%s;charset=%s',
                $this->config['db_host'],
                $this->config['db_port'],
                $this->config['db_name'],
                $this->config['db_charset']
            );
            
            $this->pdo = new PDO(
                $dsn,
                $this->config['db_user'],
                $this->config['db_pass'],
                $this->config['db_options']
            );
            
            // Set timezone
            $this->pdo->exec("SET time_zone = '+00:00'");
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new Exception('Database connection failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Execute query dan return statement
     * 
     * @param string $sql SQL query dengan placeholder (?)
     * @param array $params Parameter values
     * @return PDOStatement
     * @throws Exception Jika query gagal
     */
    public function query($sql, $params = [])
    {
        try {
            $this->last_query = $sql;
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->error = $e->getMessage();
            throw new Exception('Query failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Fetch single row
     * 
     * @param string $sql SQL query
     * @param array $params Parameter values
     * @return array|null Single row atau null jika tidak ada
     */
    public function fetch($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetch();
    }
    
    /**
     * Fetch all rows
     * 
     * @param string $sql SQL query
     * @param array $params Parameter values
     * @return array Array of rows
     */
    public function fetchAll($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }
    
    /**
     * Fetch single column value
     * 
     * @param string $sql SQL query
     * @param array $params Parameter values
     * @return mixed Single column value atau null
     */
    public function fetchColumn($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchColumn();
    }
    
    /**
     * Execute insert/update/delete
     * 
     * @param string $sql SQL query
     * @param array $params Parameter values
     * @return int Jumlah rows affected
     */
    public function execute($sql, $params = [])
    {
        $stmt = $this->query($sql, $params);
        return $stmt->rowCount();
    }
    
    /**
     * Get last inserted ID
     * 
     * @return string Last insert ID
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin transaction
     * 
     * @return void
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     * 
     * @return void
     */
    public function commit()
    {
        $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     * 
     * @return void
     */
    public function rollback()
    {
        $this->pdo->rollBack();
    }
    
    /**
     * Check if in transaction
     * 
     * @return bool True jika dalam transaction
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Get error message
     * 
     * @return string Error message
     */
    public function getError()
    {
        return $this->error;
    }
    
    /**
     * Get last query
     * 
     * @return string Last query
     */
    public function getLastQuery()
    {
        return $this->last_query;
    }
    
    /**
     * Close connection
     * 
     * @return void
     */
    public function close()
    {
        $this->pdo = null;
    }
    
    /**
     * Get PDO instance (for advanced usage)
     * 
     * @return PDO PDO instance
     */
    public function getPDO()
    {
        return $this->pdo;
    }
}

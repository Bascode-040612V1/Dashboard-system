<?php
// performance_config.php - Performance optimization configuration

// Enable output compression
if (!ob_get_level()) {
    ob_start('ob_gzhandler');
}

// Set memory limit for better performance
ini_set('memory_limit', '128M');

// Database connection pooling and optimization
class DatabasePool {
    private static $instance = null;
    private $connections = [];
    private $maxConnections = 5;
    private $currentConnections = 0;
    
    private function __construct() {}
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        // Reuse existing connection if available
        if (!empty($this->connections)) {
            return array_pop($this->connections);
        }
        
        // Create new connection if under limit
        if ($this->currentConnections < $this->maxConnections) {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                throw new Exception("Database connection failed: " . $conn->connect_error);
            }
            
            // Optimize connection settings
            $conn->set_charset("utf8");
            $conn->query("SET SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            $conn->query("SET SESSION innodb_lock_wait_timeout = 5");
            
            $this->currentConnections++;
            return $conn;
        }
        
        // Wait and retry if connection limit reached
        usleep(100000); // 0.1 second
        return $this->getConnection();
    }
    
    public function releaseConnection($conn) {
        if ($conn && !$conn->connect_error) {
            $this->connections[] = $conn;
        }
    }
}

// Simple file-based caching system
class SimpleCache {
    private static $cacheDir = 'cache/';
    private static $defaultTTL = 300; // 5 minutes
    
    public static function init() {
        if (!is_dir(self::$cacheDir)) {
            mkdir(self::$cacheDir, 0755, true);
        }
    }
    
    public static function get($key) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        
        if (!file_exists($file)) {
            return false;
        }
        
        $data = unserialize(file_get_contents($file));
        
        if ($data['expires'] < time()) {
            unlink($file);
            return false;
        }
        
        return $data['value'];
    }
    
    public static function set($key, $value, $ttl = null) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        $ttl = $ttl ?: self::$defaultTTL;
        
        $data = [
            'value' => $value,
            'expires' => time() + $ttl
        ];
        
        file_put_contents($file, serialize($data));
    }
    
    public static function delete($key) {
        self::init();
        $file = self::$cacheDir . md5($key) . '.cache';
        if (file_exists($file)) {
            unlink($file);
        }
    }
    
    public static function clear() {
        self::init();
        $files = glob(self::$cacheDir . '*.cache');
        foreach ($files as $file) {
            unlink($file);
        }
    }
}

// Query optimization helper
class QueryOptimizer {
    private static $queryCache = [];
    
    public static function prepareAndCache($conn, $sql) {
        $hash = md5($sql);
        
        if (!isset(self::$queryCache[$hash])) {
            self::$queryCache[$hash] = $conn->prepare($sql);
        }
        
        return self::$queryCache[$hash];
    }
    
    public static function optimizeQuery($sql) {
        // Add optimization hints
        $sql = str_replace('SELECT ', 'SELECT SQL_CACHE ', $sql);
        return $sql;
    }
}

// Response optimization
class ResponseOptimizer {
    public static function compressJSON($data) {
        return gzcompress(json_encode($data), 9);
    }
    
    public static function sendCompressedJSON($data) {
        header('Content-Type: application/json');
        header('Content-Encoding: gzip');
        echo self::compressJSON($data);
    }
    
    public static function setHeaders() {
        // Set caching headers
        header('Cache-Control: public, max-age=300'); // 5 minutes
        header('ETag: ' . md5(serialize($_GET)));
        
        // Check if client has cached version
        $etag = $_SERVER['HTTP_IF_NONE_MATCH'] ?? '';
        if ($etag === md5(serialize($_GET))) {
            http_response_code(304);
            exit;
        }
    }
}

// Data pagination helper
class Paginator {
    public static function paginate($query, $page = 1, $perPage = 20) {
        $offset = ($page - 1) * $perPage;
        return $query . " LIMIT $perPage OFFSET $offset";
    }
    
    public static function getTotalPages($totalRecords, $perPage = 20) {
        return ceil($totalRecords / $perPage);
    }
}

// Initialize performance optimizations
SimpleCache::init();
?>
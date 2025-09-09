<?php
// config.php - Centralized database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'rfid_system');

// Create database connection using mysqli
function getDatabaseConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check connection
    if ($conn->connect_error) {
        error_log("Database connection failed: " . $conn->connect_error);
        die("Database connection failed. Please try again later.");
    }
    
    // Set charset to prevent character encoding issues
    $conn->set_charset("utf8");
    
    return $conn;
}

// Create global connection for backward compatibility
$conn = getDatabaseConnection();

// Security configurations
ini_set('display_errors', 0); // Don't display errors in production
ini_set('log_errors', 1); // Log errors for debugging
error_reporting(E_ALL); // Report all errors to log

// Session security settings
if (session_status() == PHP_SESSION_NONE) {
    // Prevent session fixation attacks
    ini_set('session.cookie_httponly', 1);
    ini_set('session.cookie_secure', 0); // Set to 1 if using HTTPS
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Strict');
    
    // Set session timeout (30 minutes)
    ini_set('session.gc_maxlifetime', 1800);
    ini_set('session.cookie_lifetime', 1800);
    
    session_start();
    
    // Regenerate session ID for security
    if (!isset($_SESSION['initiated'])) {
        session_regenerate_id(true);
        $_SESSION['initiated'] = true;
    }
    
    // Check for session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 1800)) {
        session_unset();
        session_destroy();
        session_start();
    }
    $_SESSION['last_activity'] = time();
}
?>
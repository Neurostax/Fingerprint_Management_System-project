<?php
// Session configuration
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', 1);

// Start session
session_start();

// Include database connection
require_once __DIR__ . '/../includes/db.php';

// Base URL configuration
if (!defined('BASE_URL')) define('BASE_URL', 'http://localhost/202PROJECT.1');

// Database configuration
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'attendance_system');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');

// Application paths
if (!defined('BASE_PATH')) define('BASE_PATH', dirname(__DIR__));
if (!defined('INCLUDES_PATH')) define('INCLUDES_PATH', BASE_PATH . '/includes');
if (!defined('MODULES_PATH')) define('MODULES_PATH', BASE_PATH . '/modules');
if (!defined('CONFIG_PATH')) define('CONFIG_PATH', BASE_PATH . '/config');
if (!defined('UPLOADS_PATH')) define('UPLOADS_PATH', BASE_PATH . '/uploads');

// System Configuration
if (!defined('SITE_NAME')) define('SITE_NAME', 'Smart Attendance System');
if (!defined('SITE_URL')) define('SITE_URL', 'http://localhost/202PROJECT.1');
if (!defined('DEFAULT_LANGUAGE')) define('DEFAULT_LANGUAGE', 'en');
if (!defined('DEFAULT_THEME')) define('DEFAULT_THEME', 'light');

// Session Configuration
if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600); // 1 hour in seconds
if (!defined('REMEMBER_ME_DURATION')) define('REMEMBER_ME_DURATION', 2592000); // 30 days in seconds

// File Upload Configuration
if (!defined('MAX_FILE_SIZE')) define('MAX_FILE_SIZE', 5242880); // 5MB
if (!defined('ALLOWED_IMAGE_TYPES')) define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif']);

// Security Configuration
if (!defined('PASSWORD_MIN_LENGTH')) define('PASSWORD_MIN_LENGTH', 8);
if (!defined('MAX_LOGIN_ATTEMPTS')) define('MAX_LOGIN_ATTEMPTS', 5);
if (!defined('LOGIN_TIMEOUT')) define('LOGIN_TIMEOUT', 900); // 15 minutes in seconds

// Load settings
require_once CONFIG_PATH . '/settings.php';

// Load language file
$lang = require_once BASE_PATH . '/languages/' . DEFAULT_LANGUAGE . '.php';

// Load theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : DEFAULT_THEME;

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    die("Database connection failed. Please try again later.");
}

// Load functions
require_once INCLUDES_PATH . '/functions.php';
require_once INCLUDES_PATH . '/auth.php';

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', BASE_PATH . '/logs/error.log');
?> 
<?php
// config.php - Core configuration file

// 1. Khởi động Session an toàn (Ngăn lỗi "session already active")
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Định nghĩa đường dẫn gốc tuyệt đối (Fix lỗi require từ file con)
// __DIR__ là thư mục vật lý chứa file config.php (tức là thư mục gốc dự án)
define('ROOT_PATH', __DIR__);


// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'nuky.vn');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Site configuration
define('SITE_URL', 'http://localhost:8000/');
define('UPLOAD_PATH', ROOT_PATH . '/uploads/'); // Sử dụng ROOT_PATH
define('UPLOAD_URL', SITE_URL . '/uploads/');

// Timezone
date_default_timezone_set('Asia/Ho_Chi_Minh');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Load functions
require_once ROOT_PATH . '/functions.php'; // Sử dụng ROOT_PATH

// Load settings from database
// CHÚ Ý: Hàm get_all_settings() phải tồn tại trong functions.php
$site_settings = get_all_settings();

// Define constants from settings
define('SITE_NAME', $site_settings['site_name'] ?? 'NUKY');
define('COLOR_PRIMARY', $site_settings['color_primary'] ?? '#2E7D32');
define('COLOR_SECONDARY', $site_settings['color_secondary'] ?? '#F9F7F2');
define('COLOR_ACCENT', $site_settings['color_accent'] ?? '#FFD54F');

// Email configuration
define('ADMIN_EMAIL', $site_settings['contact_email'] ?? 'orders@nuky.vn');
define('SMTP_HOST', 'mail.nuky.vn');
define('SMTP_PORT', 587);
define('SMTP_USER', 'noreply@nuky.vn');
define('SMTP_PASS', '');

// Cart session key
define('CART_SESSION_KEY', 'nuky_cart');

// Pagination
define('ITEMS_PER_PAGE', 12);

// Upload settings
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGES', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
define('ALLOWED_VIDEOS', ['mp4', 'webm', 'mov']);

// Cache settings
define('CACHE_ENABLED', true);
define('CACHE_TIME', 3600); // 1 hour
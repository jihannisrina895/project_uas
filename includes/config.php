<?php
// Konfigurasi Database (Google Sheets)
define('GOOGLE_SCRIPT_URL', 'https://script.google.com/macros/s/AKfycbyTd8C6GbU-OUxf773_W_zJEA0ccEMZayXcF_nEJjv9TpbDezPFuYR_cY9A-PPXzvxl/exec'); // Web App URL
define('SPREADSHEET_ID', 'YOUR_SPREADSHEET_ID'); // ID spreadsheet Google Sheets

// Konfigurasi Situs
define('SITE_NAME', 'Housora Living');
define('SITE_URL', 'http://localhost/housora-living/');
define('ADMIN_EMAIL', 'admin@housoraliving.com');

// Konfigurasi Session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Auto load classes jika diperlukan
spl_autoload_register(function($class_name) {
    if (file_exists('classes/' . $class_name . '.php')) {
        include_once 'classes/' . $class_name . '.php';
    }
});

// Set timezone
date_default_timezone_set('Asia/Jakarta');

// Error reporting (matikan di production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>
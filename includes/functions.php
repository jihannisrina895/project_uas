<?php
/**
 * Housora Living - Helper Functions
 * Tidak include fungsi yang sudah ada di spreadsheet.php
 */

/**
 * Format price from Google Sheets to display format
 */
function formatPrice($price) {
    if (empty($price)) {
        return 'Price upon request';
    }
    
    // Remove dollar sign, commas, and spaces
    $clean_price = str_replace(['$', ',', ' '], '', $price);
    
    // Convert to float
    $numeric_price = floatval($clean_price);
    
    // If conversion failed, return original
    if ($numeric_price == 0) {
        return $price;
    }
    
    // Format with thousand separators
    return '$' . number_format($numeric_price);
}

/**
 * Get numeric price for calculations
 */
function getNumericPrice($price) {
    if (empty($price)) {
        return 0;
    }
    
    $clean_price = str_replace(['$', ',', ' '], '', $price);
    return floatval($clean_price);
}

/**
 * Validate and clean price input
 */
function cleanPriceInput($price) {
    if (empty($price)) {
        return '';
    }
    
    // Remove dollar sign and spaces
    $clean_price = str_replace(['$', ' '], '', $price);
    return $clean_price;
}

/**
 * Sanitize input data
 */
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

/**
 * Redirect with message
 */
function redirect($url, $message = '', $type = 'success') {
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit();
}

/**
 * Get flash message
 */
function getFlashMessage() {
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

/**
 * Check if user is admin
 */
function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

/**
 * Require login
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

/**
 * Require admin
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

/**
 * Format date
 */
function formatDate($date, $format = 'M d, Y') {
    if (empty($date)) {
        return 'N/A';
    }
    return date($format, strtotime($date));
}

/**
 * Get status badge class
 */
function getStatusBadge($status) {
    $badges = [
        'available' => 'success',
        'pending' => 'warning',
        'sold' => 'danger',
        'featured' => 'info',
        'funding' => 'secondary',
        'completed' => 'success',
        'cancelled' => 'danger'
    ];
    
    return $badges[$status] ?? 'secondary';
}

/**
 * Format area (sqft)
 */
function formatArea($area) {
    if (empty($area)) {
        return 'N/A';
    }
    
    // Remove non-numeric characters except dots
    $clean_area = preg_replace('/[^0-9.]/', '', $area);
    $numeric_area = floatval($clean_area);
    
    if ($numeric_area == 0) {
        return $area;
    }
    
    // Format with thousand separators
    return number_format($numeric_area) . ' sqft';
}
?>
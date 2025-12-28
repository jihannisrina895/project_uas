<?php
// Authentication functions

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin';
}

function requireLogin() {
    if(!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    requireLogin();
    if(!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}

function sanitizeInput($input) {
    return htmlspecialchars(strip_tags(trim($input)));
}

function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function redirect($url, $message = '', $type = 'success') {
    if($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit();
}

function getFlashMessage() {
    if(isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'info';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}

function formatPrice($price) {
    if(is_numeric($price)) {
        return '$' . number_format($price, 0, '.', ',');
    }
    return $price;
}

function getPropertyStatusBadge($status) {
    $badges = [
        'available' => 'success',
        'pending' => 'warning',
        'sold' => 'secondary',
        'rented' => 'info'
    ];
    
    $color = $badges[$status] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . ucfirst($status) . '</span>';
}
?>
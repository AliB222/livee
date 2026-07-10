<?php
/**
 * بررسی احراز هویت
 * مسیر: /wp-content/plugins/livePoint/auth-check.php
 */
session_start();

$user_id = intval($_SESSION['user_id'] ?? 0);
if ($user_id === 0) {
    header('Location: login.php');
    exit;
}

// ===== دریافت اطلاعات کاربر =====
global $wpdb;
$table_name = $wpdb->prefix . 'lp_users';
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_name} WHERE id = %d",
    $user_id
));

if (!$user) {
    session_destroy();
    header('Location: login.php');
    exit;
}

define('CURRENT_USER_ID', $user_id);
define('CURRENT_USERNAME', $user->username);
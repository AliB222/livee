<?php
/**
 * ایجاد کاربر جدید
 * مسیر: /wp-content/plugins/livePoint/create-user.php
 * (بعد از استفاده حذف شود)
 */
require_once('../../../wp-load.php');
global $wpdb;

$table_name = $wpdb->prefix . 'lp_users';
$charset_collate = $wpdb->get_charset_collate();

$sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    display_name VARCHAR(100) DEFAULT '',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) {$charset_collate};";

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
dbDelta($sql);

// ===== ایجاد کاربر جدید =====
$username = 'admin';
$password = password_hash('123456', PASSWORD_DEFAULT);

$result = $wpdb->insert($table_name, [
    'username' => $username,
    'password' => $password,
    'display_name' => 'مدیر اصلی'
]);

if ($result) {
    echo "✅ کاربر با موفقیت ایجاد شد.<br>";
    echo "👤 نام کاربری: <strong>admin</strong><br>";
    echo "🔑 رمز عبور: <strong>123456</strong><br>";
    echo "<br><a href='login.php'>ورود به پنل</a>";
} else {
    echo "❌ خطا در ایجاد کاربر. احتمالاً قبلاً وجود دارد.<br>";
    echo "<a href='login.php'>ورود به پنل</a>";
}
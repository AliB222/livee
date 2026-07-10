<?php
/**
 * نصب و ایجاد جدول کاربران
 * (یک بار اجرا شود، سپس حذف شود)
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

echo "✅ جدول کاربران ایجاد شد.";
echo "<br><a href='create-user.php'>ایجاد کاربر</a>";
<?php
/**
 * صفحه اصلی اسکوربرد - انتخاب پوسته بر اساس پارامتر URL
 */
require_once( dirname(__FILE__) . '/../../../wp-load.php' );

// دریافت نام پوسته از پارامتر URL (مثلاً ?theme=obs)
$theme = isset($_GET['theme']) ? $_GET['theme'] : 'default';

// جلوگیری از دسترسی به فایل‌های خارج از پوشه themes
$theme = preg_replace('/[^a-zA-Z0-9_-]/', '', $theme);

// مسیر فایل پوسته
$theme_file = plugin_dir_path(__FILE__) . 'themes/' . $theme . '.php';

// اگر فایل پوسته وجود داشت، آن را لود کن، در غیر این صورت پوسته پیش‌فرض
if (file_exists($theme_file)) {
    include $theme_file;
} else {
    // اگر پوسته درخواستی وجود نداشت، پوسته پیش‌فرض را لود کن
    include plugin_dir_path(__FILE__) . 'themes/default.php';
}
?>
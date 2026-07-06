<?php
/**
 * API Endpoint برای دریافت داده‌های تیم‌ها از دیتابیس وردپرس
 * مسیر: /wp-content/plugins/livePoint/api.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// پیدا کردن مسیر wp-load.php
$wp_load_path = dirname(__DIR__, 3) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die('خطا: فایل wp-load.php پیدا نشد.');
}
require_once($wp_load_path);

if (!function_exists('get_option')) {
    die('خطا: وردپرس به درستی بارگذاری نشد.');
}

// دریافت داده‌ها از دیتابیس
$teams_data = get_option('lp_teams', []);
$general_data = get_option('lp_general', []);

if (empty($teams_data)) {
    header('Content-Type: application/json');
    echo json_encode(['teams' => [], 'general' => $general_data]);
    exit;
}

// تبدیل به فرمت خروجی
$output = [];
$pos = 0;
foreach ($teams_data as $t) {
    $pos++;
    $kills = intval($t['km1'] ?? 0) + intval($t['km2'] ?? 0) + 
             intval($t['km3'] ?? 0) + intval($t['km4'] ?? 0) + intval($t['km5'] ?? 0);
    $total = $kills + intval($t['plc'] ?? 0) + intval($t['bonus'] ?? 0);
    
    // دریافت آدرس لوگو
    $logo_url = '';
    if (!empty($t['logo_id'])) {
        $img = wp_get_attachment_image_src($t['logo_id'], 'thumbnail');
        if ($img) $logo_url = $img[0];
    }
    
    // ===== تعیین کلاس شرطی =====
    $class = '';
    if (intval($t['active'] ?? 0) == 0) {
        $class = 'notActive';
    } elseif (intval($t['alive'] ?? 0) < 1) {
        $class = 'dead';
    }
    
    $output[] = [
        'name'      => $t['name'] ?? 'بدون نام',
        'logo'      => $logo_url,
        'alive'     => intval($t['alive'] ?? 0),
        'kills'     => $kills,
        'plc'       => intval($t['plc'] ?? 0),
        'bonus'     => intval($t['bonus'] ?? 0),
        'total'     => $total,
        'win'       => intval($t['win'] ?? 0),
        'pos'       => $pos,
        'poscolor'  => $t['color'] ?? '#ffffff',
        'active'    => !empty($t['active']) ? 'true' : 'false',
        'class'     => $class,
        // ===== اضافه کردن KMها به صورت جداگانه =====
        'km1'       => intval($t['km1'] ?? 0),
        'km2'       => intval($t['km2'] ?? 0),
        'km3'       => intval($t['km3'] ?? 0),
        'km4'       => intval($t['km4'] ?? 0),
        'km5'       => intval($t['km5'] ?? 0),
    ];
}

// مرتب‌سازی
usort($output, function ($a, $b) {
    if ($a['total'] != $b['total']) return $b['total'] - $a['total'];
    if ($a['win'] != $b['win']) return $b['win'] - $a['win'];
    if ($a['plc'] != $b['plc']) return $b['plc'] - $a['plc'];
    return $b['kills'] - $a['kills'];
});

// خروجی JSON
header('Content-Type: application/json');
echo json_encode(['teams' => $output, 'general' => $general_data], JSON_UNESCAPED_UNICODE);
<?php
/**
 * API Endpoint برای دریافت داده‌های تیم‌ها از دیتابیس وردپرس
 * مسیر: /wp-content/plugins/livePoint/api.php
 * بر اساس شماره مچ ذخیره‌شده در دیتابیس، کشته‌های آن مچ را نمایش می‌دهد
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

// ============================================================
// ===== دریافت شماره مچ فعلی از دیتابیس =====
// ============================================================
$match = intval($general_data['current_match'] ?? 1);
if ($match < 1 || $match > 5) {
    $match = 1;
}
$km_key = 'km' . $match; // km1, km2, ...

// ============================================================
// ===== اضافه کردن آدرس لوگو به general =====
// ============================================================
$org_logo_id = $general_data['org_logo_id'] ?? 0;
$org_logo_url = '';
if ($org_logo_id) {
    $img = wp_get_attachment_image_src($org_logo_id, 'medium');
    if ($img) {
        $org_logo_url = $img[0];
    }
}
$general_data['org_logo_url'] = $org_logo_url;

// ============================================================
// ===== اطمینان از وجود promoted_teams در خروجی =====
// ============================================================
$general_data['promoted_teams'] = intval($general_data['promoted_teams'] ?? 0);

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
    
    // ===== کشته‌های مچ فعلی =====
    $kills = intval($t[$km_key] ?? 0);
    
    // ===== مجموع کل کشته‌ها (برای محاسبه Total) =====
    $total_kills = intval($t['km1'] ?? 0) + intval($t['km2'] ?? 0) + 
                   intval($t['km3'] ?? 0) + intval($t['km4'] ?? 0) + intval($t['km5'] ?? 0);
    $total = $total_kills + intval($t['plc'] ?? 0) + intval($t['bonus'] ?? 0);
    
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
        'name'       => $t['name'] ?? 'بدون نام',
        'logo'       => $logo_url,
        'alive'      => intval($t['alive'] ?? 0),
        'kills'      => $kills,      // ← کشته‌های مچ فعلی
        'total_kills'=> $total_kills, // ← جمع کل کشته‌ها (برای استفاده در صورت نیاز)
        'plc'        => intval($t['plc'] ?? 0),
        'bonus'      => intval($t['bonus'] ?? 0),
        'total'      => $total,       // ← امتیاز کل (PTS)
        'win'        => intval($t['win'] ?? 0),
        'pos'        => $pos,
        'poscolor'   => $t['color'] ?? '#ffffff',
        'active'     => !empty($t['active']) ? 'true' : 'false',
        'class'      => $class,
        'km1'        => intval($t['km1'] ?? 0),
        'km2'        => intval($t['km2'] ?? 0),
        'km3'        => intval($t['km3'] ?? 0),
        'km4'        => intval($t['km4'] ?? 0),
        'km5'        => intval($t['km5'] ?? 0),
    ];
}

// مرتب‌سازی بر اساس Total (امتیاز کل)
usort($output, function ($a, $b) {
    if ($a['total'] != $b['total']) return $b['total'] - $a['total'];
    if ($a['win'] != $b['win']) return $b['win'] - $a['win'];
    if ($a['plc'] != $b['plc']) return $b['plc'] - $a['plc'];
    return $b['kills'] - $a['kills'];
});

// خروجی JSON
header('Content-Type: application/json');
echo json_encode(['teams' => $output, 'general' => $general_data], JSON_UNESCAPED_UNICODE);
<?php
/**
 * API Endpoint با کش فایل JSON - بهینه‌شده برای سرعت بالا
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// ===== ۱. بررسی کش =====
// ============================================================
$cache_dir = __DIR__ . '/cache';
$cache_file = $cache_dir . '/api.json';
$cache_duration = 1.5; // ۱.۵ ثانیه

// اگر پوشه کش وجود ندارد، بساز
if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
    // فایل .htaccess برای محافظت از پوشه
    file_put_contents($cache_dir . '/.htaccess', 'Order Deny,Allow' . "\n" . 'Deny from all');
    file_put_contents($cache_dir . '/index.php', '<?php // Silence is golden');
}

// ===== بررسی کش =====
if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_duration)) {
    header('Content-Type: application/json; charset=utf-8');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    header('X-Cache-Hit: true');
    readfile($cache_file);
    exit;
}

// ============================================================
// ===== ۲. تولید داده جدید =====
// ============================================================
$wp_load_path = dirname(__DIR__, 3) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die('خطا: فایل wp-load.php پیدا نشد.');
}
require_once($wp_load_path);

if (!function_exists('get_option')) {
    die('خطا: وردپرس به درستی بارگذاری نشد.');
}

// دریافت داده‌ها
$teams_data = get_option('lp_teams', []);
$general_data = get_option('lp_general', []);

// اضافه کردن آدرس لوگو به general
$org_logo_id = $general_data['org_logo_id'] ?? 0;
$org_logo_url = '';
if ($org_logo_id) {
    $img = wp_get_attachment_image_src($org_logo_id, 'medium');
    if ($img) $org_logo_url = $img[0];
}
$general_data['org_logo_url'] = $org_logo_url;
$general_data['promoted_teams'] = intval($general_data['promoted_teams'] ?? 0);

// دریافت شماره مچ
$match = intval($general_data['current_match'] ?? 1);
if ($match < 1 || $match > 5) $match = 1;

if (empty($teams_data)) {
    $output = ['teams' => [], 'general' => $general_data];
    $json_output = json_encode($output, JSON_UNESCAPED_UNICODE);
    header('Content-Type: application/json; charset=utf-8');
    echo $json_output;
    exit;
}

// تبدیل به فرمت خروجی
$output = [];
$pos = 0;
foreach ($teams_data as $t) {
    $pos++;
    $matches = $t['matches'] ?? [];
    $km = intval($matches[$match]['km'] ?? 0);
    $plc = intval($matches[$match]['plc'] ?? 0);
    
    // محاسبه total
    $total_km = 0;
    $total_plc = 0;
    for ($i = 1; $i <= 5; $i++) {
        $total_km += intval($matches[$i]['km'] ?? 0);
        $total_plc += intval($matches[$i]['plc'] ?? 0);
    }
    $total = $total_km + $total_plc + intval($t['bonus'] ?? 0);
    
    $logo_url = '';
    if (!empty($t['logo_id'])) {
        $img = wp_get_attachment_image_src($t['logo_id'], 'thumbnail');
        if ($img) $logo_url = $img[0];
    }
    
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
        'kills'      => $km,
        'plc'        => $plc,
        'total'      => $total,
        'win'        => intval($t['win'] ?? 0),
        'pos'        => $pos,
        'poscolor'   => $t['color'] ?? '#ffffff',
        'active'     => !empty($t['active']) ? 'true' : 'false',
        'class'      => $class,
        'total_kills' => $total_km,
        'total_plc'   => $total_plc,
    ];
}

// مرتب‌سازی
usort($output, function ($a, $b) {
    if ($a['total'] != $b['total']) return $b['total'] - $a['total'];
    if ($a['win'] != $b['win']) return $b['win'] - $a['win'];
    if ($a['plc'] != $b['plc']) return $b['plc'] - $a['plc'];
    return $b['kills'] - $a['kills'];
});

// ============================================================
// ===== ۳. ذخیره در کش و خروجی =====
// ============================================================
$json_output = json_encode(['teams' => $output, 'general' => $general_data], JSON_UNESCAPED_UNICODE);
file_put_contents($cache_file, $json_output);

// هدرها
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Cache-Hit: false');
header('X-Timestamp: ' . time());
echo $json_output;
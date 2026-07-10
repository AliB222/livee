<?php
/**
 * API Endpoint با کش JSON و پشتیبانی از user_id
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// ============================================================
// ===== دریافت user_id =====
// ============================================================
$user_id = intval($_GET['user_id'] ?? 0);
if ($user_id === 0) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'user_id الزامی است']);
    exit;
}

// ============================================================
// ===== کش =====
// ============================================================
$cache_dir = __DIR__ . '/cache';
$cache_file = $cache_dir . '/api_' . $user_id . '.json';
$cache_duration = 1.5;

if (!is_dir($cache_dir)) {
    mkdir($cache_dir, 0755, true);
    file_put_contents($cache_dir . '/.htaccess', 'Order Deny,Allow' . "\n" . 'Deny from all');
    file_put_contents($cache_dir . '/index.php', '<?php // Silence is golden');
}

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
// ===== لود وردپرس =====
// ============================================================
$wp_load_path = dirname(__DIR__, 3) . '/wp-load.php';
if (!file_exists($wp_load_path)) {
    die('خطا: فایل wp-load.php پیدا نشد.');
}
require_once($wp_load_path);

if (!function_exists('get_option')) {
    die('خطا: وردپرس به درستی بارگذاری نشد.');
}

// ============================================================
// ===== دریافت داده‌ها =====
// ============================================================
$all_teams = get_option('lp_teams', []);
$teams_data = array_filter($all_teams, function($t) use ($user_id) {
    return intval($t['user_id'] ?? 0) === $user_id;
});
$teams_data = array_values($teams_data);

$all_general = get_option('lp_general', []);
$general_temp = array_filter($all_general, function($g) use ($user_id) {
    return intval($g['user_id'] ?? 0) === $user_id;
});
$general_data = reset($general_temp) ?: [];

// ============================================================
// ===== مهاجرت خودکار داده‌های قدیمی =====
// ============================================================
$converted = false;
foreach ($teams_data as $index => $t) {
    if (isset($t['matches']) && is_array($t['matches']) && !empty($t['matches'])) {
        continue;
    }
    $converted = true;
    $matches = [];
    if (isset($t['km1']) || isset($t['km2']) || isset($t['km3']) || isset($t['km4']) || isset($t['km5'])) {
        for ($i = 1; $i <= 5; $i++) {
            $matches[$i] = [
                'km' => intval($t['km' . $i] ?? 0),
                'plc' => intval($t['plc'] ?? 0)
            ];
        }
    } else {
        for ($i = 1; $i <= 5; $i++) {
            $matches[$i] = ['km' => 0, 'plc' => 0];
        }
    }
    $teams_data[$index]['matches'] = $matches;
    for ($i = 1; $i <= 5; $i++) {
        unset($teams_data[$index]['km' . $i]);
    }
}

if ($converted) {
    $all_teams = array_filter($all_teams, function($t) use ($user_id) {
        return intval($t['user_id'] ?? 0) !== $user_id;
    });
    $all_teams = array_merge($all_teams, $teams_data);
    update_option('lp_teams', $all_teams);
}

// ============================================================
// ===== اضافه کردن آدرس لوگو =====
// ============================================================
$org_logo_id = $general_data['org_logo_id'] ?? 0;
$org_logo_url = '';
if ($org_logo_id) {
    $img = wp_get_attachment_image_src($org_logo_id, 'medium');
    if ($img) $org_logo_url = $img[0];
}
$general_data['org_logo_url'] = $org_logo_url;
$general_data['promoted_teams'] = intval($general_data['promoted_teams'] ?? 0);
$general_data['timestamp'] = time();

// ============================================================
// ===== دریافت شماره مچ =====
// ============================================================
$match = intval($general_data['current_match'] ?? 1);
if ($match < 1 || $match > 5) $match = 1;

if (empty($teams_data)) {
    $output = ['teams' => [], 'general' => $general_data];
    $json_output = json_encode($output, JSON_UNESCAPED_UNICODE);
    file_put_contents($cache_file, $json_output, LOCK_EX);
    header('Content-Type: application/json; charset=utf-8');
    echo $json_output;
    exit;
}

// ============================================================
// ===== تبدیل به فرمت خروجی =====
// ============================================================
$output = [];
$pos = 0;
foreach ($teams_data as $t) {
    $pos++;
    $matches = $t['matches'] ?? [];
    $km = intval($matches[$match]['km'] ?? 0);
    $plc = intval($matches[$match]['plc'] ?? 0);
    
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
// ===== ذخیره در کش و خروجی =====
// ============================================================
$json_output = json_encode(['teams' => $output, 'general' => $general_data], JSON_UNESCAPED_UNICODE);
file_put_contents($cache_file, $json_output, LOCK_EX);

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('X-Cache-Hit: false');
header('X-Timestamp: ' . time());
echo $json_output;
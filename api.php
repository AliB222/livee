<?php
/**
 * api.php - فقط از پنل جدید (livepoint_teams) می‌خواند
 */

require_once("../../../wp-load.php");

header('Content-Type: application/json; charset=utf-8');

// ===== فقط از پنل جدید بخوان =====
$teams_data = get_option('livepoint_teams', array());

// ===== اگر هیچ داده‌ای نبود =====
if (empty($teams_data)) {
    echo json_encode(array('teams' => array()));
    exit;
}

// ===== تبدیل به فرمت خروجی =====
$output = array();
$pos = 0;
foreach ($teams_data as $t) {
    $pos++;
    $kills = intval($t['km1'] ?? 0) + intval($t['km2'] ?? 0) + 
             intval($t['km3'] ?? 0) + intval($t['km4'] ?? 0) + 
             intval($t['km5'] ?? 0);
    $total = $kills + intval($t['plc'] ?? 0) + intval($t['bonus'] ?? 0);

    $output[] = array(
        'name'      => $t['name'] ?? 'بدون نام',
        'logo'      => $t['logo'] ?? '',
        'alive'     => intval($t['alive'] ?? 0),
        'kills'     => $kills,
        'plc'       => intval($t['plc'] ?? 0),
        'total'     => $total,
        'win'       => intval($t['win'] ?? 0),
        'pos'       => $pos,
        'poscolor'  => $t['color'] ?? '#ffffff',
        'active'    => !empty($t['active']) ? 'true' : 'false',
    );
}

// ===== مرتب‌سازی =====
usort($output, function ($a, $b) {
    if ($a['total'] != $b['total']) return $b['total'] - $a['total'];
    if ($a['win'] != $b['win']) return $b['win'] - $a['win'];
    if ($a['plc'] != $b['plc']) return $b['plc'] - $a['plc'];
    return $b['kills'] - $a['kills'];
});

echo json_encode(array('teams' => $output), JSON_UNESCAPED_UNICODE);
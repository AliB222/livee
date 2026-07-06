<?php
require_once("../../../../wp-load.php");

// دریافت داده‌ها از دیتابیس جدید
$teams_data = get_option('lp_teams', []);
$alive_icon = 'http://localhost/livepoint/wp-content/uploads/2023/11/Asset-1.svg';

$teams_array = [];
$pos = 0;
foreach ($teams_data as $t) {
    $pos++;
    $kills = intval($t['km1'] ?? 0) + intval($t['km2'] ?? 0) + 
             intval($t['km3'] ?? 0) + intval($t['km4'] ?? 0) + intval($t['km5'] ?? 0);
    $total = $kills + intval($t['plc'] ?? 0) + intval($t['bonus'] ?? 0);
    
    if (intval($t['alive'] ?? 0) > 0) {
        $logo_url = '';
        if (!empty($t['logo_id'])) {
            $img = wp_get_attachment_image_src($t['logo_id'], 'medium');
            if ($img) $logo_url = $img[0];
        }
        
        $teams_array[] = [
            'name' => $t['name'] ?? 'بدون نام',
            'logo' => $logo_url,
            'alive' => intval($t['alive'] ?? 0),
            'kills' => $kills,
            'plc' => intval($t['plc'] ?? 0),
            'total' => $total,
            'win' => intval($t['win'] ?? 0),
            'pos' => $pos,
            'pos-color' => $t['color'] ?? '#ff9800'
        ];
    }
}

usort($teams_array, function ($a, $b) {
    if ($a['total'] != $b['total']) return $b['total'] - $a['total'];
    if ($a['win'] != $b['win']) return $b['win'] - $a['win'];
    if ($a['plc'] != $b['plc']) return $b['plc'] - $a['plc'];
    return $b['kills'] - $a['kills'];
});

$htmlOutput = '';
if (count($teams_array) <= 4) {
    $htmlOutput .= '<div id="liveBox" class="">';
    $htmlOutput .= '<h1>TOP 4</h1>';
    foreach ($teams_array as $num => $team_data) {
        $htmlOutput .= '<div class="logo">';
        $htmlOutput .= '<img width="150px" src="' . esc_url($team_data['logo']) . '" alt="">';
        $htmlOutput .= '</div>';
    }
    $htmlOutput .= '</div>';
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('html' => $htmlOutput, 'teams' => $teams_array));
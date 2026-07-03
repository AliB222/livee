<?php
require_once("../../../../wp-load.php");

$teams_array = array();
$pos = 0;
while (have_rows('teams', 'option')) : the_row();
    $team_name = get_sub_field('team_name');
    $team_logo = get_sub_field('team_logo');
    $alive = get_sub_field('alive');
    $KM1 = get_sub_field('KM1');
    $KM2 = get_sub_field('KM2');
    $KM3 = get_sub_field('KM3');
    $KM4 = get_sub_field('KM4');
    $PLC = get_sub_field('PLC');
    $win = get_sub_field('win');
    $kills = $KM1 + $KM2 + $KM3 + $KM4;
    $total = $kills + $PLC;
    $pos_color = get_sub_field('pos-color');
    $pos++;

    $team_data = array(
        'name' => $team_name,
        'logo' => $team_logo,
        'alive' => $alive,
        'kills' => $kills,
        'plc' => $PLC,
        'total' => $total,
        'win' => $win,
        'pos' => $pos,
        'pos-color' => $pos_color
    );
    if ($alive > 0){
        $teams_array[] = $team_data;
    }
endwhile;

usort($teams_array, function ($a, $b) {
    if ($a['total'] != $b['total']) {
        return $b['total'] - $a['total'];
    } elseif ($a['win'] != $b['win']) {
        return $b['win'] - $a['win'];
    } elseif ($a['plc'] != $b['plc']) {
        return $b['plc'] - $a['plc'];
    } else {
        return $b['kills'] - $a['kills'];
    }
});

$htmlOutput = '';

if (count($teams_array) == 1) {
    $winner = $teams_array[0];
    $htmlOutput = '
        <img src="' . esc_url($winner['logo']) . '" alt="Winner Logo">
        <p>' . esc_html($winner['name']) . '</p>
    ';
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
header('Access-Control-Allow-Origin: *');

echo json_encode(array('html' => $htmlOutput, 'teams' => $teams_array), JSON_UNESCAPED_UNICODE);
<?php
require_once("../../../wp-load.php");

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
        $KM5 = get_sub_field('KM5');
        $PLC = get_sub_field('PLC');
        $win = get_sub_field('win');
        $bonus = get_sub_field('bonus');
        $active = get_sub_field('active');
        $pos_color = get_sub_field('pos-color');
        $kills = $KM1 + $KM2 + $KM3 + $KM4 + $KM5;
        $total = $kills + $PLC + $bonus;
        $pos++;
        
        $team_data = array(
            'name' => $team_name,
            'logo' => $team_logo,
            'alive' => $alive,
            'KM1' => $KM1,
            'KM2' => $KM2,
            'KM3' => $KM3,
            'KM4' => $KM4,
            'KM5' => $KM5,
            'kills' => $kills,
            'plc' => $PLC,
            'total' => $total,
            'win' => $win,
            'bonus' => $bonus,
            'pos' => $pos,
            'poscolor' => $pos_color,
            'active' => $active
        );
        $teams_array[] = $team_data;
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
// اضافه کردن رنک به هر تیم
foreach ($teams_array as $index => &$team) {
  $team['rank'] = $index + 1; // اضافه کردن فیلد rank به هر تیم
}
    // اطلاعات را به صورت JSON ارسال کنید
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('teams' => $teams_array), JSON_UNESCAPED_UNICODE);
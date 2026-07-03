<?php
require_once("../../../../wp-load.php");
$alive_icon = get_field("alive-icon", 'options');

$teams_array = array();
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
    if (count($teams_array) <= 4) {
    foreach ($teams_array as $num => $team_data) :
        
        $htmlOutput .= '<div class="teams " style="background:' . $team_data['pos-color'] . ';">';
        $htmlOutput .= '<img width="150px" src="' . $team_data['logo'] . '" alt="">';
        $htmlOutput .= '<span>' . $team_data['name'] . '</span>';
        $htmlOutput .= '<div class="alive">';
         if ($team_data['alive'] == 0) {
        $htmlOutput .= '<img src="https://itsalib2.ir/wp-content/uploads/2023/11/white-helmet.svg" alt="">';
    } else {
        for ($i = 1; $i <= $team_data['alive']; $i++) {
            $htmlOutput .= '<img src="' . $alive_icon . '" alt="">';
        }
    }
        $htmlOutput .= '</div>';
        $htmlOutput .= '</div>';
    endforeach;
    }
// اطلاعات را به صورت JSON ارسال کنید
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('html' => $htmlOutput, 'teams' => $teams_array));
?>
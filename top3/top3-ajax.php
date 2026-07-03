<?php
require_once("../../../../wp-load.php");

$color = get_field("top3-color", 'options');
$teams_array = array();
$pos = 0;
$max_kills_team = null;
$max_kills = 0;
$max_plc_team = null;
$max_plc = 0;

$teams_array = array();
    while (have_rows('teams', 'option')) : the_row();
        $team_name = get_sub_field('team_name');
        $team_logo = get_sub_field('team_logo');
        $KM1 = get_sub_field('KM1');
        $KM2 = get_sub_field('KM2');
        $KM3 = get_sub_field('KM3');
        $KM4 = get_sub_field('KM4');
        $PLC = get_sub_field('PLC');
        $win = get_sub_field('win');
        $kills = $KM1 + $KM2 + $KM3 + $KM4;
        $total = $kills + $PLC;
        $pos++;

        $team_data = array(
            'name' => $team_name,
            'logo' => $team_logo,
            'kills' => $kills,
            'plc' => $PLC,
            'total' => $total,
            'win' => $win,
            'pos' => $pos,
        );
        $teams_array[] = $team_data;
            if ($kills > $max_kills) {
        $max_kills = $kills;
        $max_kills_team = $team_data;
    }
    if ($PLC > $max_plc) {
        $max_plc = $PLC;
        $max_plc_team = $team_data;
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

    foreach ($teams_array as $num => $team_data) :
        $htmlOutput .= '<div class="logo">';
        $htmlOutput .= '<img src="' . $team_data['logo'] . '" alt="">';
        $htmlOutput .= '<p style="color:' . $color . '" class="kills">' . $team_data['name'] . '</p>';
        $htmlOutput .= '<p style="color:' . $color . '" class="kills">' . $team_data['kills'] . '</p>';
        $htmlOutput .= '<p style="color:' . $color . '"<p class="plc">' . $team_data['plc'] . '</p>';
        $htmlOutput .= '<p style="color:' . $color . '"<p class="total">' . $team_data['total'] . '</p>';
        $htmlOutput .= '</div>';
        if ($num > 1) break;
    endforeach;
        foreach ($teams_array as $num => $team_data) :
         if ($num == 1) {
        $htmlOutput .= '<div class="logo">';
        $htmlOutput .= '<img src="' . $max_kills_team['logo'] . '" alt="not found">';
        $htmlOutput .= '<p style="color:' . $color . '" class="name">' . $max_kills_team['name'] . '</p>';
        $htmlOutput .= '<p style="color:' . $color . '" class="kills">' . $max_kills_team['kills'] . ' kills</p>';
        $htmlOutput .= '</div>';
         }
    endforeach;
            foreach ($teams_array as $num => $team_data) :
         if ($num == 1) {
        $htmlOutput .= '<div class="logo">';
        $htmlOutput .= '<img src="' . $max_plc_team['logo'] . '" alt="not found">';
        $htmlOutput .= '<p style="color:' . $color . '" class="name">' . $max_plc_team['name'] . '</p>';
        $htmlOutput .= '<p style="color:' . $color . '" class="plc">' . $max_plc_team['plc'] . ' plc</p>';
        $htmlOutput .= '</div>';
         }
    endforeach;

// اطلاعات را به صورت JSON ارسال کنید
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('html' => $htmlOutput, 'teams' => $teams_array));
?>
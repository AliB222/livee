<?php
require_once("../../../wp-load.php");

$org = get_field("org", 'option');
$match_info = get_field("match_info", 'option');
$org_logo = get_field("org_logo", 'option');
$color_set = get_field("color_set", 'options');
$team_num_color = get_field("team-number", 'options');
$team_color = get_field("team-color", 'options');
$alive_icon = get_field("alive-icon", 'options');

$pos = 0;
// ایجاد HTML برای liveBox
$htmlOutput = '';


// ایجاد آرایه برای اطلاعات تیم‌ها
$teams_array = array();

while (have_rows('teams', 'option')) : the_row();
    // اطلاعات تیم‌ها را از دیتابیس بخوانید
    $team_name = get_sub_field('team_name');
    $team_logo = get_sub_field('team_logo');
    $alive = get_sub_field('alive');
    $KM1 = get_sub_field('KM1');
    $KM2 = get_sub_field('KM2');
    $KM3 = get_sub_field('KM3');
    $KM4 = get_sub_field('KM4');
    $PLC = get_sub_field('PLC');
    $win = get_sub_field('win');
    $active = get_sub_field('active');
    $pos_color = get_sub_field('pos-color');
    $kills = $KM1 + $KM2 + $KM3 + $KM4;
    $total = $kills + $PLC;
    $pos++;
    // اضافه کردن اطلاعات به آرایه
    $team_data = array(
        'name' => $team_name,
        'logo' => $team_logo,
        'alive' => $alive,
        'kills' => $kills,
        'plc' => $PLC,
        'total' => $total,
        'win' => $win,
        'pos' => $pos,
        'pos-color' => $pos_color,
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

// ایجاد HTML برای هر تیم و افزودن به $htmlOutput
foreach ($teams_array as $num => $team_data) :
    if ($team_data['active'] == "false"){
                $active_status = "notActive";
            }else{
                $active_status = "Active";
            }       
    $dead = ($team_data['alive'] == 0) ? "dead" : "";
    $htmlOutput .= '<div id="team' . ($num + 1) . '" class="teams ' . $active_status . ' ' . $dead . ' pos' . $team_data['pos'] . '">';

    $htmlOutput .= '<div class="rank">' . ($num + 1) . '</div>';
    $htmlOutput .= '<div class="logo">';
    $htmlOutput .= '<img src="' . $team_data['logo'] . '" alt="">';
    $htmlOutput .= '<div class="pos-color" style="background:' . $team_data['pos-color'] . ' !important;  box-shadow: 0px 0px 14px 1px ' . $team_data['pos-color'] . ' !important"></div>';
    $htmlOutput .= '</div>';
    $htmlOutput .= '<div class="name animated">';
    $htmlOutput .= '<span>' . $team_data['name'] . '</span>';
    $htmlOutput .= '</div>';
    $htmlOutput .= '<div class="alive">';
    if ($team_data['alive'] == 0) {
        $htmlOutput .= '<img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="">';
    } else {
        for ($i = 1; $i <= $team_data['alive']; $i++) {
            $htmlOutput .= '<img src="' . $alive_icon . '" alt="">';
        }
    }
    $htmlOutput .= '</div>';
    $htmlOutput .= '<div class="kills">' . $team_data['kills'] . '</div>';
    $htmlOutput .= '<div class="plc">' . $team_data['plc'] . '</div>';
    $htmlOutput .= '<div class="total">' . $team_data['total'] . '</div>';
    $htmlOutput .= '</div>';
endforeach;

// اطلاعات را به صورت JSON ارسال کنید
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('html' => $htmlOutput, 'teams' => $teams_array));
?>

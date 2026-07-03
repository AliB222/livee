<?php

require_once("../../../../wp-load.php");

$match = $_GET['match'];
$last_pos = isset($_GET['last_pos']) ? intval($_GET['last_pos']) : 0; // موقعیت آخرین تیم نمایش داده شده
$teams_array = array();
$pos = 0;

while (have_rows('teams', 'option')):
    the_row();
    $team_name = get_sub_field('team_name');
    $team_logo = get_sub_field('team_logo');
    switch ($match)
    {
        case "1":
            $KM = get_sub_field('KM1');
        break;
        case "2":
            $KM = get_sub_field('KM2');
        break;
        case "3":
            $KM = get_sub_field('KM3');
        break;
        case "4":
            $KM = get_sub_field('KM4');
        break;
    }
    $pos_color = get_sub_field('pos-color');
    $pos++;

    if ($KM > "9" && $pos > $last_pos)
    {
        $team_data = array(
            'name' => $team_name,
            'logo' => $team_logo,
            'pos-color' => $pos_color,
            'pos' => $pos
        );
        $teams_array[] = $team_data;
    }

endwhile;
header('Content-Type: application/json');

echo json_encode($teams_array);
?>

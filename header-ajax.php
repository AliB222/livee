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
$htmlOutput .= '<div id="info">';
$htmlOutput .= '<div class="infoImg">';
$htmlOutput .= '<img src="' . $org_logo . '" alt="">';
$htmlOutput .= '</div>';
$htmlOutput .= '<div class="infoTxt">';
$htmlOutput .= '<p>' . $org . '</p>';
$htmlOutput .= '<p>' . $match_info . '</p>';
$htmlOutput .= '</div>';
$htmlOutput .= '</div>';
$htmlOutput .= '<div id="teamsHead">';
$htmlOutput .= '<div class="headText"><p>team</p></div>';
$htmlOutput .= '<div class="headText"><p>alive</p></div>';
$htmlOutput .= '<div class="headText"><p>kills</p></div>';
$htmlOutput .= '<div class="headText"><p>plc</p></div>';
$htmlOutput .= '<div class="headText"><p>total</p></div></div>';

// اطلاعات را به صورت JSON ارسال کنید
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('html' => $htmlOutput, 'teams' => $teams_array));
?>

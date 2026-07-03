<?php
require_once("../../../wp-load.php");

        $img1 = get_field("img1", 'option');
        $img2 = get_field("img2", 'option');
        $img3 = get_field("img3", 'option');
        $img4 = get_field("img4", 'option');
        $img5 = get_field("img5", 'option');

$htmlOutput = '';
$htmlOutput .= '<img src="' . $img1 . '">';
$htmlOutput .= '<img src="' . $img2 . '">';
$htmlOutput .= '<img src="' . $img3 . '">';
$htmlOutput .= '<img src="' . $img4 . '">';
$htmlOutput .= '<img src="' . $img5 . '">';
// اطلاعات را به صورت JSON ارسال کنید

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('html' => $htmlOutput, 'teams' => $teams_array));
?>


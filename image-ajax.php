<?php
require_once("../../../wp-load.php");

// دریافت تیم‌ها و شماره ردیف برندگان
$teams = get_option('lp_teams', []);
$match_winner_rows = get_option('lp_match_winner_rows', []);

// ساخت HTML فقط برای تصاویر (بدون هیچ المان اضافی)
$htmlOutput = '';
for ($i = 1; $i <= 5; $i++) {
    $row_number = $match_winner_rows[$i] ?? 0;
    $logo_url = '';

    if ($row_number > 0 && isset($teams[$row_number - 1])) {
        $team = $teams[$row_number - 1];
        $logo_id = $team['logo_id'] ?? 0;
        if ($logo_id) {
            $img = wp_get_attachment_image_src($logo_id, 'medium');
            if ($img) $logo_url = $img[0];
        }
    }

    if ($logo_url) {
        $htmlOutput .= '<img src="' . esc_url($logo_url) . '" style="display:inline-block; margin: 10px 20px; max-width:200px; height:auto;">';
    }
}

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
echo json_encode(array('html' => $htmlOutput));
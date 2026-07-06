<?php
// بارگذاری وردپرس
require_once("../../../wp-load.php");

// ===== هدرهای ضد کش =====
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Content-Type: application/json');

// ===== دریافت داده‌های عمومی از پنل جدید =====
$general = get_option('lp_general', []);
$org = $general['org'] ?? '';
$match_info = $general['match_info'] ?? '';
$logo_id = $general['org_logo_id'] ?? '';
$logo_url = '';

// دریافت آدرس لوگو
if ($logo_id) {
    $img = wp_get_attachment_image_src($logo_id, 'medium');
    if ($img) {
        $logo_url = $img[0];
    }
}

// ===== ساخت HTML برای هدر =====
$htmlOutput = '';
$htmlOutput .= '<div id="info">';
$htmlOutput .= '<div class="infoImg">';
$htmlOutput .= '<img src="' . esc_url($logo_url) . '" alt="">';
$htmlOutput .= '</div>';
$htmlOutput .= '<div class="infoTxt">';
$htmlOutput .= '<p>' . esc_html($org) . '</p>';
$htmlOutput .= '<p>' . esc_html($match_info) . '</p>';
$htmlOutput .= '</div>';
$htmlOutput .= '</div>';
$htmlOutput .= '<div id="teamsHead">';
$htmlOutput .= '<div class="headText"><p>team</p></div>';
$htmlOutput .= '<div class="headText"><p>alive</p></div>';
$htmlOutput .= '<div class="headText"><p>kills</p></div>';
$htmlOutput .= '<div class="headText"><p>plc</p></div>';
$htmlOutput .= '<div class="headText"><p>total</p></div></div>';

// خروجی JSON
echo json_encode([
    'html' => $htmlOutput,
    'general' => $general
], JSON_UNESCAPED_UNICODE);
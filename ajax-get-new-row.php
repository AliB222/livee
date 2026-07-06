<?php
/**
 * دریافت سطر جدید برای جدول تیم‌ها
 * این فایل توسط AJAX فراخوانی می‌شود
 */

// بارگذاری وردپرس
require_once( dirname(__FILE__) . '/../../../wp-load.php' );

// جلوگیری از دسترسی مستقیم
if (!defined('ABSPATH')) {
    exit;
}

// دریافت شماره ردیف از پارامتر
$row_index = isset($_GET['index']) ? intval($_GET['index']) : 0;
$row_number = $row_index + 1;

// ===== ساخت سطر جدید با همان ساختار PHP =====
$row_html = '<tr class="team-row" data-index="' . $row_index . '">';
$row_html .= '<td class="row-number" style="text-align:center; border:1px solid #ddd; padding:4px; font-weight:bold; color:#1d2327; width:40px;">' . $row_number . '</td>';
$row_html .= '<td style="text-align:center; border:1px solid #ddd; padding:4px; width:48px;"><input type="checkbox" class="team-active" checked></td>';
$row_html .= '<td style="text-align:center; border:1px solid #ddd; padding:4px; width:48px;"><input type="color" class="team-color" value="#ff9800" style="width:40px; padding:0; border:none;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:42px;"><input type="number" class="team-win num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:42px;"><input type="number" class="team-plc num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:48px;"><input type="number" class="team-bonus num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:44px;"><input type="number" class="team-km5 num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:44px;"><input type="number" class="team-km4 num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:44px;"><input type="number" class="team-km3 num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:44px;"><input type="number" class="team-km2 num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:44px;"><input type="number" class="team-km1 num-input" value="0" style="width:45px; padding:3px;"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:48px;"><input type="number" class="team-alive num-input" value="4" style="width:45px; padding:3px;" min="0" max="4" step="1"></td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; width:70px;">';
$row_html .= '<div class="team-image-container">';
$row_html .= '<input type="hidden" class="team-logo-id" value="">';
$row_html .= '<div class="team-image-wrap" style="display:none; position:relative; max-width:80px; max-height:35px;">';
$row_html .= '<div class="image-preview" style="cursor:pointer; position:relative; display:inline-block; max-height:35px; max-width:80px; border:1px solid #ddd; padding:3px; background:#fff;"></div>';
$row_html .= '<button type="button" class="button team-image-remove-btn" style="position:absolute; top:-8px; right:-8px; background:#dc3545; color:#fff; border:none; border-radius:50%; width:20px; height:20px; line-height:20px; font-size:14px; cursor:pointer; padding:0; text-align:center; opacity:0; transition:opacity 0.2s ease;">×</button>';
$row_html .= '</div>';
$row_html .= '<button type="button" class="button button-small team-image-add" style="padding:2px 8px; font-size:11px;">📁</button>';
$row_html .= '</div>';
$row_html .= '</td>';
$row_html .= '<td style="border:1px solid #ddd; padding:4px; min-width:110px;"><input type="text" class="team-name name-input" value="تیم جدید" style="width:100%; padding:3px; direction:rtl; text-align:right;"></td>';
$row_html .= '<td style="text-align:center; border:1px solid #ddd; padding:4px; width:38px;"><button type="button" class="button delete-team" style="background:#dc3545; color:#fff; border:none; padding:2px 10px; border-radius:4px; font-size:14px; cursor:pointer; line-height:24px;">✖</button></td>';
$row_html .= '</tr>';

// خروجی JSON
header('Content-Type: application/json');
echo json_encode(['html' => $row_html]);
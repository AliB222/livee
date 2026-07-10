<?php
/**
 * LivePoint Panel - نسخه چند کاربره با احراز هویت
 */
if (!defined('ABSPATH')) exit;

// ============================================================
// ===== بررسی احراز هویت =====
// ============================================================
session_start();
$user_id = intval($_SESSION['user_id'] ?? 0);
if ($user_id === 0) {
    wp_die('دسترسی غیرمجاز. لطفاً وارد شوید. <a href="' . plugin_dir_url(__FILE__) . 'login.php">ورود</a>');
}

global $wpdb;
$table_name = $wpdb->prefix . 'lp_users';
$user = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$table_name} WHERE id = %d",
    $user_id
));

if (!$user) {
    session_destroy();
    wp_die('کاربر یافت نشد. <a href="' . plugin_dir_url(__FILE__) . 'login.php">ورود مجدد</a>');
}

define('CURRENT_USER_ID', $user_id);
define('CURRENT_USERNAME', $user->username);

// ============================================================
// ===== بارگذاری اسکریپت‌ها =====
// ============================================================
add_action('admin_enqueue_scripts', 'lp_panel_enqueue_scripts');
function lp_panel_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_lp-panel') return;
    
    wp_enqueue_media();
    wp_enqueue_script('jquery');
    
    wp_enqueue_script(
        'lp-panel-script',
        plugin_dir_url(__FILE__) . 'lp-panel.js',
        array('jquery', 'media-upload'),
        '2.2',
        true
    );
    
    wp_localize_script('lp-panel-script', 'lp_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('lp_panel'),
        'user_id' => CURRENT_USER_ID
    ));
}

// ============================================================
// ===== منوی ادمین =====
// ============================================================
add_action('admin_menu', 'lp_panel_menu');
function lp_panel_menu() {
    add_menu_page(
        'LIVE POINT',
        'LIVE POINT',
        'manage_options',
        'lp-panel',
        'lp_panel_page',
        'dashicons-admin-generic',
        30
    );
}

// ============================================================
// ===== صفحه مدیریت (HTML) =====
// ============================================================
function lp_panel_page() {
    // ===== دریافت داده‌های این کاربر =====
    $all_teams = get_option('lp_teams', []);
    $teams = array_filter($all_teams, function($t) {
        return intval($t['user_id'] ?? 0) === CURRENT_USER_ID;
    });
    $teams = array_values($teams); // بازنویسی ایندکس‌ها
    
    $all_general = get_option('lp_general', []);
    $general_temp = array_filter($all_general, function($g) {
        return intval($g['user_id'] ?? 0) === CURRENT_USER_ID;
    });
    $general = reset($general_temp) ?: [];
    
    $all_match_winner_rows = get_option('lp_match_winner_rows', []);
    $match_winner_rows_temp = array_filter($all_match_winner_rows, function($m) {
        return intval($m['user_id'] ?? 0) === CURRENT_USER_ID;
    });
    $match_winner_rows = reset($match_winner_rows_temp) ?: [];
    
    $current_match = intval($general['current_match'] ?? 1);
    if ($current_match < 1 || $current_match > 5) $current_match = 1;
    
    $org_logo_url = '';
    if (!empty($general['org_logo_id'])) {
        $img = wp_get_attachment_image_src($general['org_logo_id'], 'medium');
        if ($img) $org_logo_url = $img[0];
    }
    ?>
    <div class="wrap acf-settings-wrap" style="max-width:1200px; padding:20px; background:#f5f7fa; direction:rtl; text-align:right;">

        <style>
            .lp-general-box { background:#ffffff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); padding:20px 25px; margin-bottom:25px; border:1px solid #e2e6ea; }
            .lp-general-box .lp-box-header { border-bottom:2px solid #f0f2f5; padding-bottom:12px; margin-bottom:18px; }
            .lp-general-box .lp-box-header h2 { margin:0; font-size:18px; color:#1d2327; font-weight:600; display:flex; align-items:center; gap:8px; }
            .lp-general-fields { display:flex; flex-wrap:wrap; gap:15px 20px; }
            .lp-general-fields .lp-field { flex:1 1 180px; min-width:150px; }
            .lp-general-fields .lp-field label { display:block; font-size:13px; font-weight:600; color:#2c3338; margin-bottom:4px; }
            .lp-general-fields .lp-field input[type="text"] { width:100%; padding:8px 12px; font-size:13px; border:1px solid #d0d5dd; border-radius:6px; background:#fafbfc; }
            .lp-table-wrapper { overflow-x:auto; background:#fff; border-radius:12px; padding:5px 10px 10px 10px; box-shadow:0 2px 8px rgba(0,0,0,0.06); }
            .lp-table { width:100%; border-collapse:collapse; font-size:13px; direction:rtl; }
            .lp-table th { padding:10px 6px; text-align:center; border-bottom:2px solid #e2e6ea; background:#f8f9fa; font-weight:600; color:#1d2327; }
            .lp-table td { padding:6px 4px; text-align:center; border-bottom:1px solid #eef1f3; vertical-align:middle; }
            .lp-table .num-input { width:44px; padding:4px; font-size:13px; border:1px solid #d0d5dd; border-radius:4px; text-align:center; }
            .lp-table .name-input { width:100%; padding:4px 6px; font-size:13px; border:1px solid #d0d5dd; border-radius:4px; }
            .lp-message { display:none; padding:12px 18px; border-radius:8px; margin-bottom:18px; font-weight:500; }
            .team-row.lp-row-hidden { display: none !important; }
            .lp-table .team-row.dead .num-input.dead { background-color:#ffcdd2 !important; }
            .lp-hidden-column { display:none !important; }
            .hide-col-btn { background:transparent; border:none; cursor:pointer; font-size:11px; margin-right:2px; color:#888; padding:0 2px; }
            .hide-col-btn:hover { color:#2271b1 !important; }
            #teams-table th { position:relative; }
            .row-number { font-weight:bold; color:#1d2327; }
            .match-logos-box { background:#ffffff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.08); padding:20px 25px; margin-top:25px; border:1px solid #e2e6ea; }
            .match-logos-box .lp-box-header { border-bottom:2px solid #f0f2f5; padding-bottom:12px; margin-bottom:18px; }
            .match-logos-box .lp-box-header h2 { margin:0; font-size:18px; color:#1d2327; font-weight:600; display:flex; align-items:center; gap:8px; }
            .match-logos-box .lp-box-header h2::before { content:"🏆"; font-size:20px; }
            .match-logos-fields { display:flex; flex-wrap:wrap; gap:15px 20px; }
            .match-logos-fields .lp-field { flex:1 1 120px; min-width:100px; }
            .match-logos-fields .lp-field label { display:block; font-size:13px; font-weight:600; color:#2c3338; margin-bottom:4px; }
            .match-logos-fields .lp-field input[type="number"] { width:100%; padding:8px 12px; font-size:13px; border:1px solid #d0d5dd; border-radius:6px; background:#fafbfc; }
            .match-logos-fields .lp-field input[type="number"]:focus { border-color:#2271b1; background:#fff; outline:none; box-shadow:0 0 0 2px rgba(34,113,177,0.15); }
            .lp-btn-save { padding:8px 36px; font-size:15px; border-radius:6px; background:#2271b1; border:none; color:#fff; cursor:pointer; font-weight:600; transition:background 0.2s; }
            .lp-btn-save:hover { background:#135e96; }
            .lp-btn-save:disabled { opacity:0.7; cursor:not-allowed; }
            
            .lp-image-remove-btn,
            .team-image-remove-btn {
                opacity: 0;
                transition: opacity 0.2s ease;
            }
            .lp-image-wrap:hover .lp-image-remove-btn,
            .team-image-wrap:hover .team-image-remove-btn {
                opacity: 1;
            }
            .inline-field {
                display: inline-flex;
                align-items: center;
                gap: 4px;
                margin-left: 8px;
            }
            .inline-field label {
                font-size: 12px;
                font-weight: 500;
                color: #2c3338;
                margin: 0;
                white-space: nowrap;
            }
            .inline-field input[type="number"] {
                width: 50px;
                padding: 4px 6px;
                font-size: 12px;
                border: 1px solid #d0d5dd;
                border-radius: 4px;
                background: #fafbfc;
                text-align: center;
            }
            .inline-field input[type="number"]:focus {
                border-color: #2271b1;
                background: #fff;
                outline: none;
                box-shadow: 0 0 0 2px rgba(34,113,177,0.15);
            }
            .inline-field .hint {
                font-size: 10px;
                color: #888;
                margin-right: 2px;
            }
        </style>

        <!-- ===== هدر ===== -->
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:20px;">
            <h1 style="font-size:24px; font-weight:700; color:#1d2327; margin:0;">🏆 LIVE POINT</h1>
            <div style="display:flex; align-items:center; gap:12px;">
                <span style="color:#666; font-size:13px;">👤 <?php echo esc_html(CURRENT_USERNAME); ?></span>
                <a href="<?php echo plugin_dir_url(__FILE__); ?>login.php?logout=1" style="color:#dc3545; text-decoration:none; font-size:13px;">خروج</a>
            </div>
        </div>

        <div id="lp-message" class="lp-message"></div>

        <form id="lp-panel-form" method="post">
            <!-- ===== تنظیمات مسابقه ===== -->
            <div class="lp-general-box">
                <div class="lp-box-header"><h2>تنظیمات مسابقه</h2></div>
                <div class="lp-general-fields">
                    <div class="lp-field">
                        <label>نام برگزار کننده</label>
                        <input type="text" id="lp-org" value="<?php echo esc_attr($general['org'] ?? ''); ?>">
                    </div>
                    <div class="lp-field">
                        <label>اطلاعات Match</label>
                        <input type="text" id="lp-match" value="<?php echo esc_attr($general['match_info'] ?? ''); ?>">
                    </div>
                    <div class="lp-field">
                        <label>لوگو</label>
                        <div class="lp-image-container">
                            <input type="hidden" id="lp-logo-id" class="lp-image-id" value="<?php echo esc_attr($general['org_logo_id'] ?? ''); ?>">
                            <div class="lp-image-wrap" style="<?php echo empty($org_logo_url) ? 'display:none;' : ''; ?>">
                                <div class="image-preview" style="cursor:pointer; position:relative; display:inline-block;">
                                    <?php if ($org_logo_url): ?>
                                        <img src="<?php echo esc_url($org_logo_url); ?>" style="max-height:50px;">
                                        <button type="button" class="button lp-image-remove-btn" style="position:absolute; top:-8px; right:-8px; background:#dc3545; color:#fff; border:none; border-radius:50%; width:20px; height:20px; line-height:20px; font-size:14px; cursor:pointer; padding:0; text-align:center;">×</button>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <button type="button" class="lp-image-add" style="<?php echo empty($org_logo_url) ? '' : 'display:none;'; ?>">📁 انتخاب تصویر</button>
                        </div>
                    </div>
                    <div class="lp-field" style="flex:0 1 100px;">
                        <label>شماره مچ فعلی</label>
                        <input type="number" id="lp-current-match" value="<?php echo esc_attr($current_match); ?>" min="1" max="5" step="1">
                        <div style="font-size:11px; color:#888; margin-top:4px;">مچ ۱ تا ۵</div>
                    </div>
                    <div class="lp-field" style="flex:0 1 100px;">
                        <label>تعداد تیم‌های صعود کننده</label>
                        <input type="number" id="lp-promoted-teams" value="<?php echo esc_attr($general['promoted_teams'] ?? 0); ?>" min="0" max="50" step="1">
                        <div style="font-size:11px; color:#888; margin-top:4px;">۰ = بدون خط</div>
                    </div>
                    <div class="lp-field" style="flex:0 1 120px;">
                        <label>تعداد تیم‌های زنده</label>
                        <div id="lp-total-team" style="font-size:28px; font-weight:700; color:#1d2327; padding:4px 0;"><?php echo esc_attr(count($teams)); ?></div>
                    </div>
                    <div class="lp-field" style="flex:0 1 120px;">
                        <label>مجموع Alive</label>
                        <div id="lp-total-alive" style="font-size:28px; font-weight:700; color:#1d2327; padding:4px 0;">
                            <?php 
                            $total_alive = 0;
                            foreach ($teams as $t) {
                                $total_alive += intval($t['alive'] ?? 0);
                            }
                            echo $total_alive;
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ===== جدول تیم‌ها با ستون ردیف ===== -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:20px 25px; border:1px solid #e2e6ea;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:15px;">
                    <h2 style="margin:0; font-size:18px; font-weight:600; color:#1d2327;">👥 لیست تیم‌ها</h2>
                    <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
                        <a href="#" id="reset-alive-btn" style="background:#e8f5e9; color:#2e7d32; border-color:#a5d6a7; padding:6px 16px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none; cursor:pointer; border:1px solid #ddd; display:inline-block;">🔄 Reset Alive (4)</a>
                        <a href="#" id="reset-all-btn" style="background:#ffebee; color:#c62828; border-color:#ef9a9a; padding:6px 16px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none; cursor:pointer; border:1px solid #ddd; display:inline-block;">🔄 Reset All</a>
                        <label style="display:inline-flex; align-items:center; gap:6px; cursor:pointer; background:#e3f2fd; padding:6px 16px; border-radius:6px; border:1px solid #90caf9; font-size:13px; font-weight:500; color:#0d47a1;">
                            <input type="checkbox" id="show-all-teams-checkbox"> نمایش همه تیم‌ها
                        </label>
                        <button type="button" id="add-team-btn" class="button button-primary">➕ سطر جدید</button>
                        <button type="button" id="reset-columns-btn" class="button" style="font-size:12px; padding:4px 12px;">👁️ نمایش همه ستون‌ها</button>
                    </div>
                </div>

                <div class="lp-table-wrapper">
                    <table id="teams-table" class="lp-table">
                        <thead>
                            <tr>
                                <th style="width:40px;">#</th>
                                <th style="width:48px;">فعال</th>
                                <th style="width:48px;">رنگ</th>
                                <th style="width:42px;">WIN</th>
                                <th style="width:42px;">KM</th>
                                <th style="width:42px;">PLC</th>
                                <th style="width:48px;">Bonus</th>
                                <th style="width:48px;">Alive</th>
                                <th style="width:70px;">لوگو</th>
                                <th style="min-width:110px;">نام تیم</th>
                                <th style="width:38px;">حذف</th>
                            </tr>
                        </thead>
                        <tbody id="teams-tbody">
                            <?php if (empty($teams)): ?>
                            <tr id="no-teams-row">
                                <td colspan="11" style="text-align:center; padding:30px; color:#888;">هیچ تیمی وجود ندارد.</td>
                            </tr>
                            <?php else: ?>
                            <?php $row_num = 1; foreach ($teams as $index => $t): 
                                $logo_url = '';
                                if (!empty($t['logo_id'])) {
                                    $img = wp_get_attachment_image_src($t['logo_id'], 'thumbnail');
                                    if ($img) $logo_url = $img[0];
                                }
                                $dead_class = (intval($t['alive'] ?? 0) < 1) ? 'dead' : '';
                                $hidden_class = (intval($t['alive'] ?? 0) < 1) ? 'lp-row-hidden' : '';
                                
                                $matches = $t['matches'] ?? [];
                                $km_value = intval($matches[$current_match]['km'] ?? 0);
                                $plc_value = intval($matches[$current_match]['plc'] ?? 0);
                                
                                $data_matches = [];
                                for ($i = 1; $i <= 5; $i++) {
                                    $data_matches[$i] = [
                                        'km' => intval($matches[$i]['km'] ?? 0),
                                        'plc' => intval($matches[$i]['plc'] ?? 0)
                                    ];
                                }
                                $data_matches_json = htmlspecialchars(json_encode($data_matches), ENT_QUOTES, 'UTF-8');
                            ?>
                            <tr class="team-row <?php echo $dead_class; ?> <?php echo $hidden_class; ?>" data-index="<?php echo $index; ?>" data-matches='<?php echo $data_matches_json; ?>'>
                                <td class="row-number"><?php echo $row_num++; ?></td>
                                <td><input type="checkbox" class="team-active" <?php echo !empty($t['active']) ? 'checked' : ''; ?>></td>
                                <td><input type="color" class="team-color" value="<?php echo esc_attr($t['color'] ?? '#ff9800'); ?>" style="width:32px; height:24px; padding:0; border:none; cursor:pointer;"></td>
                                <td><input type="number" class="team-win num-input" value="<?php echo esc_attr($t['win'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-km num-input" value="<?php echo esc_attr($km_value); ?>"></td>
                                <td><input type="number" class="team-plc num-input" value="<?php echo esc_attr($plc_value); ?>"></td>
                                <td><input type="number" class="team-bonus num-input" value="<?php echo esc_attr($t['bonus'] ?? 0); ?>"></td>
                                <td>
                                    <input type="number" class="team-alive num-input <?php echo $dead_class; ?>" value="<?php echo esc_attr($t['alive'] ?? 4); ?>" min="0" max="4" step="1">
                                </td>
                                <td>
                                    <div class="team-image-container">
                                        <input type="hidden" class="team-logo-id" value="<?php echo esc_attr($t['logo_id'] ?? ''); ?>">
                                        <div class="team-image-wrap" style="<?php echo empty($logo_url) ? 'display:none;' : ''; ?>">
                                            <div class="image-preview" style="cursor:pointer; position:relative; display:inline-block;">
                                                <?php if ($logo_url): ?>
                                                    <img src="<?php echo esc_url($logo_url); ?>" style="max-height:30px;">
                                                    <button type="button" class="button team-image-remove-btn" style="position:absolute; top:-8px; right:-8px; background:#dc3545; color:#fff; border:none; border-radius:50%; width:20px; height:20px; line-height:20px; font-size:14px; cursor:pointer; padding:0; text-align:center;">×</button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <button type="button" class="team-image-add" style="<?php echo empty($logo_url) ? '' : 'display:none;'; ?>">📁</button>
                                    </div>
                                </td>
                                <td><input type="text" class="team-name name-input" value="<?php echo esc_attr($t['name'] ?? ''); ?>" placeholder="نام تیم"></td>
                                <td><button type="button" class="button delete-team" style="background:#dc3545; color:#fff; border:none; padding:2px 10px; border-radius:4px; font-size:14px; cursor:pointer; line-height:24px;">✖</button></td>
                            </tr>
                            <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ===== بخش شماره ردیف برندگان مچ‌ها ===== -->
            <div class="match-logos-box">
                <div class="lp-box-header">
                    <h2>شماره ردیف برندگان هر مچ</h2>
                    <p style="margin:5px 0 0; color:#666; font-size:13px;">شماره ردیف تیم برنده را برای هر مچ (۱ تا ۵) وارد کنید.</p>
                </div>
                <div class="match-logos-fields">
                    <?php for ($i = 1; $i <= 5; $i++):
                        $row_number = $match_winner_rows[$i] ?? '';
                    ?>
                    <div class="lp-field">
                        <label>مچ <?php echo $i; ?></label>
                        <input type="number" class="lp-match-row" data-match="<?php echo $i; ?>" value="<?php echo esc_attr($row_number); ?>" min="1" max="50" placeholder="ردیف">
                        <div style="font-size:11px; color:#888; margin-top:4px;">شماره ردیف تیم برنده</div>
                    </div>
                    <?php endfor; ?>
                </div>
            </div>

            <!-- ===== دکمه ذخیره ===== -->
            <div style="margin-top:18px; display:flex; justify-content:flex-end;">
                <input type="submit" id="lp-save-btn" class="button button-primary lp-btn-save" value="💾 ذخیره همه تغییرات">
            </div>

        </form>
    </div>
    <?php
}

// ============================================================
// ===== AJAX Handler با user_id =====
// ============================================================
add_action('wp_ajax_lp_save_panel', 'lp_save_panel_ajax');
function lp_save_panel_ajax() {
    check_ajax_referer('lp_panel', 'nonce');

    session_start();
    $user_id = intval($_SESSION['user_id'] ?? 0);
    if ($user_id === 0) {
        wp_send_json_error('دسترسی غیرمجاز');
    }

    $general = $_POST['general'] ?? [];
    $current_match = intval($general['current_match'] ?? 1);
    if ($current_match < 1 || $current_match > 5) $current_match = 1;

    // ===== ۱. پردازش تیم‌ها =====
    if (isset($_POST['teams'])) {
        $teams = $_POST['teams'];
        $all_teams = get_option('lp_teams', []);
        $new_teams = [];

        // حذف تیم‌های قدیمی این کاربر
        $all_teams = array_filter($all_teams, function($t) use ($user_id) {
            return intval($t['user_id'] ?? 0) !== $user_id;
        });

        foreach ($teams as $t) {
            $matches = [];
            $km_value = intval($t['km'] ?? 0);
            $plc_value = intval($t['plc'] ?? 0);
            $matches[$current_match] = [
                'km' => $km_value,
                'plc' => $plc_value
            ];

            $new_teams[] = [
                'user_id' => $user_id,
                'active' => intval($t['active'] ?? 0),
                'color' => sanitize_hex_color($t['color'] ?? '#ff9800'),
                'win' => intval($t['win'] ?? 0),
                'bonus' => intval($t['bonus'] ?? 0),
                'alive' => intval($t['alive'] ?? 4),
                'logo_id' => intval($t['logo_id'] ?? 0),
                'name' => sanitize_text_field($t['name'] ?? ''),
                'matches' => $matches
            ];
        }

        $all_teams = array_merge($all_teams, $new_teams);
        update_option('lp_teams', $all_teams);
    }

    // ===== ۲. ذخیره general =====
    if (isset($_POST['general'])) {
        $all_general = get_option('lp_general', []);
        $all_general = array_filter($all_general, function($g) use ($user_id) {
            return intval($g['user_id'] ?? 0) !== $user_id;
        });
        $general = $_POST['general'];
        $general['user_id'] = $user_id;
        $all_general[] = $general;
        update_option('lp_general', $all_general);
    }

    // ===== ۳. ذخیره match_rows =====
    if (isset($_POST['match_rows'])) {
        $all_match_winner_rows = get_option('lp_match_winner_rows', []);
        $all_match_winner_rows = array_filter($all_match_winner_rows, function($m) use ($user_id) {
            return intval($m['user_id'] ?? 0) !== $user_id;
        });
        $rows = [];
        foreach ($_POST['match_rows'] as $key => $val) {
            $match_num = str_replace('match_row_', '', $key);
            $rows[intval($match_num)] = intval($val);
        }
        $rows['user_id'] = $user_id;
        $all_match_winner_rows[] = $rows;
        update_option('lp_match_winner_rows', $all_match_winner_rows);
    }

    // ============================================================
    // ===== Invalidate کش =====
    // ============================================================
    $cache_dir = plugin_dir_path(__FILE__) . 'cache';
    $cache_file = $cache_dir . '/api_' . $user_id . '.json';
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
    
    if (function_exists('wp_remote_get')) {
        wp_remote_get(plugin_dir_url(__FILE__) . 'api.php?user_id=' . $user_id, array(
            'timeout' => 1,
            'blocking' => false
        ));
    }

    wp_send_json_success();
}
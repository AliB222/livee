<?php
/**
 * LivePoint Panel - نسخه نهایی با اسکریپت جداگانه
 */
if (!defined('ABSPATH')) exit;

// ============================================================
// بارگذاری اسکریپت‌ها
// ============================================================
add_action('admin_enqueue_scripts', 'lp_panel_enqueue_scripts');
function lp_panel_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_lp-panel') return;
    
    wp_enqueue_media();
    wp_enqueue_script('jquery');
    
    // بارگذاری اسکریپت اصلی از فایل جداگانه
    wp_enqueue_script(
        'lp-panel-script',
        plugin_dir_url(__FILE__) . 'lp-panel.js',
        array('jquery', 'media-upload'),
        '1.1',
        true
    );
    
    // ارسال داده‌های AJAX به اسکریپت
    wp_localize_script('lp-panel-script', 'lp_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('lp_panel')
    ));
}

// ============================================================
// منوی ادمین
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
// صفحه مدیریت (HTML)
// ============================================================
function lp_panel_page() {
    $general = get_option('lp_general', []);
    $teams = get_option('lp_teams', []);
    
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
            .lp-general-box .lp-box-header h2::before { content:"⚙️"; font-size:20px; }
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
            .reset-buttons { display:flex; gap:12px; align-items:center; flex-wrap:wrap; }
            .reset-buttons a, .reset-buttons button { padding:6px 16px; border-radius:6px; font-size:13px; font-weight:500; text-decoration:none; cursor:pointer; border:1px solid #ddd; background:#fff; }
            .lp-hidden-column { display:none !important; }
            .hide-col-btn { background:transparent; border:none; cursor:pointer; font-size:11px; margin-right:2px; color:#888; padding:0 2px; }
            .hide-col-btn:hover { color:#2271b1 !important; }
            #teams-table th { position:relative; }
        </style>

        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:20px;">
            <h1 style="font-size:24px; font-weight:700; color:#1d2327; margin:0;">🏆 LIVE POINT</h1>
            <div class="reset-buttons">
                <a href="#" id="reset-alive-btn" style="background:#e8f5e9; color:#2e7d32; border-color:#a5d6a7;">🔄 Reset Alive (4)</a>
                <a href="#" id="reset-all-btn" style="background:#ffebee; color:#c62828; border-color:#ef9a9a;">🔄 Reset All</a>
                <button type="button" id="show-all-teams-btn" style="background:#e3f2fd; color:#0d47a1; border-color:#90caf9;">👁️ نمایش همه تیم‌ها</button>
            </div>
        </div>

        <div id="lp-message" class="lp-message"></div>

        <form id="lp-panel-form" method="post">
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
                                <div class="image-preview"><?php if ($org_logo_url): ?><img src="<?php echo esc_url($org_logo_url); ?>" style="max-height:50px;"><?php endif; ?></div>
                                <div class="lp-image-actions">
                                    <button type="button" class="button lp-image-edit">✏️</button>
                                    <button type="button" class="button lp-image-remove">✖</button>
                                </div>
                            </div>
                            <button type="button" class="lp-image-add" style="<?php echo empty($org_logo_url) ? '' : 'display:none;'; ?>">📁 انتخاب تصویر</button>
                        </div>
                    </div>
                    <div class="lp-field" style="flex:0 1 120px;">
                        <label>تعداد تیم‌های زنده</label>
                        <div id="lp-total-team" style="font-size:28px; font-weight:700; color:#1d2327; padding:4px 0;"><?php echo esc_attr($general['total_team'] ?? 0); ?></div>
                    </div>
                    <div class="lp-field" style="flex:0 1 120px;">
                        <label>مجموع Alive</label>
                        <div id="lp-total-alive" style="font-size:28px; font-weight:700; color:#1d2327; padding:4px 0;"><?php echo esc_attr($general['total_alive'] ?? 0); ?></div>
                    </div>
                </div>
            </div>

            <div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:20px 25px; border:1px solid #e2e6ea;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:15px;">
                    <h2 style="margin:0; font-size:18px; font-weight:600; color:#1d2327;">👥 لیست تیم‌ها</h2>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" id="add-team-btn" class="button button-primary">➕ سطر جدید</button>
                    </div>
                </div>

                <div class="lp-table-wrapper">
                    <table id="teams-table" class="lp-table">
                        <thead>
                            <tr>
                                <th style="width:48px;">فعال</th>
                                <th style="width:48px;">رنگ</th>
                                <th style="width:42px;">WIN</th>
                                <th style="width:42px;">PLC</th>
                                <th style="width:48px;">Bonus</th>
                                <th style="width:44px;">KM5</th>
                                <th style="width:44px;">KM4</th>
                                <th style="width:44px;">KM3</th>
                                <th style="width:44px;">KM2</th>
                                <th style="width:44px;">KM1</th>
                                <th style="width:48px;">Alive</th>
                                <th style="width:70px;">لوگو</th>
                                <th style="min-width:110px;">نام تیم</th>
                                <th style="width:38px;">حذف</th>
                            </tr>
                        </thead>
                        <tbody id="teams-tbody">
                            <?php if (empty($teams)): ?>
                            <tr id="no-teams-row">
                                <td colspan="14" style="text-align:center; padding:30px; color:#888;">هیچ تیمی وجود ندارد.</td>
                            </tr>
                            <?php else: ?>
                            <?php foreach ($teams as $index => $t): 
                                $logo_url = '';
                                if (!empty($t['logo_id'])) {
                                    $img = wp_get_attachment_image_src($t['logo_id'], 'thumbnail');
                                    if ($img) $logo_url = $img[0];
                                }
                                $dead_class = (intval($t['alive'] ?? 0) < 1) ? 'dead' : '';
                                $hidden_class = (intval($t['alive'] ?? 0) < 1) ? 'lp-row-hidden' : '';
                            ?>
                            <tr class="team-row <?php echo $dead_class; ?> <?php echo $hidden_class; ?>" data-index="<?php echo $index; ?>">
                                <td><input type="checkbox" class="team-active" <?php echo !empty($t['active']) ? 'checked' : ''; ?>></td>
                                <td><input type="color" class="team-color" value="<?php echo esc_attr($t['color'] ?? '#ff9800'); ?>" style="width:32px; height:24px; padding:0; border:none; cursor:pointer;"></td>
                                <td><input type="number" class="team-win num-input" value="<?php echo esc_attr($t['win'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-plc num-input" value="<?php echo esc_attr($t['plc'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-bonus num-input" value="<?php echo esc_attr($t['bonus'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-km5 num-input" value="<?php echo esc_attr($t['km5'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-km4 num-input" value="<?php echo esc_attr($t['km4'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-km3 num-input" value="<?php echo esc_attr($t['km3'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-km2 num-input" value="<?php echo esc_attr($t['km2'] ?? 0); ?>"></td>
                                <td><input type="number" class="team-km1 num-input" value="<?php echo esc_attr($t['km1'] ?? 0); ?>"></td>
                                <td>
                                    <input type="number" class="team-alive num-input <?php echo $dead_class; ?>" value="<?php echo esc_attr($t['alive'] ?? 4); ?>" min="0" max="4" step="1">
                                </td>
                                <td>
                                    <div class="team-image-container">
                                        <input type="hidden" class="team-logo-id" value="<?php echo esc_attr($t['logo_id'] ?? ''); ?>">
                                        <div class="team-image-wrap" style="<?php echo empty($logo_url) ? 'display:none;' : ''; ?>">
                                            <div class="image-preview"><?php if ($logo_url): ?><img src="<?php echo esc_url($logo_url); ?>" style="max-height:30px;"><?php endif; ?></div>
                                            <div class="lp-image-actions">
                                                <button type="button" class="button team-image-edit">✏️</button>
                                                <button type="button" class="button team-image-remove">✖</button>
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

                <div style="margin-top:18px; display:flex; justify-content:flex-end;">
                    <input type="submit" id="lp-save-btn" class="button button-primary" style="padding:8px 36px; font-size:15px;" value="💾 ذخیره همه تغییرات">
                </div>
            </div>
        </form>
    </div>
    <?php
}

// ============================================================
// ===== AJAX Handler =====
// ============================================================
add_action('wp_ajax_lp_save_panel', 'lp_save_panel_ajax');
function lp_save_panel_ajax() {
    check_ajax_referer('lp_panel', 'nonce');

    if (isset($_POST['teams'])) {
        update_option('lp_teams', $_POST['teams']);
    }
    if (isset($_POST['general'])) {
        update_option('lp_general', $_POST['general']);
    }

    wp_send_json_success();
}
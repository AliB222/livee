<?php
/**
 * پنل مدیریت مستقل LivePoint (بدون ACF)
 * این فایل فقط برای تست و توسعه است و به ACF دست نمی‌زند
 */

// ===== افزودن منوی ادمین =====
add_action('admin_menu', 'livepoint_test_admin_menu');

function livepoint_test_admin_menu() {
    add_menu_page(
        'تنظیمات LivePoint (تست)',  // عنوان صفحه
        'LivePoint (تست)',          // عنوان منو
        'manage_options',           // سطح دسترسی
        'livepoint-test-settings',  // slug
        'livepoint_test_settings_page', // تابع رندر
        'dashicons-groups',         // آیکون
        31                          // ترتیب (کمی پایین‌تر از منوی اصلی)
    );
}

// ===== تابع رندر صفحه =====
function livepoint_test_settings_page() {
    // ===== ذخیره‌سازی =====
    if (isset($_POST['save_livepoint_test']) && check_admin_referer('livepoint_test_nonce')) {
        // ذخیره تنظیمات عمومی
        $general = array(
            'org'         => sanitize_text_field($_POST['org']),
            'match_info'  => sanitize_text_field($_POST['match_info']),
            'org_logo'    => esc_url_raw($_POST['org_logo']),
            'color_set'   => sanitize_text_field($_POST['color_set']),
            'team_number' => intval($_POST['team_number']),
            'team_color'  => sanitize_text_field($_POST['team_color']),
            'alive_icon'  => esc_url_raw($_POST['alive_icon']),
            'top3_color'  => sanitize_text_field($_POST['top3_color']),
            'img1'        => esc_url_raw($_POST['img1']),
            'img2'        => esc_url_raw($_POST['img2']),
            'img3'        => esc_url_raw($_POST['img3']),
            'img4'        => esc_url_raw($_POST['img4']),
            'img5'        => esc_url_raw($_POST['img5']),
        );
        update_option('livepoint_test_general', $general);

        // ذخیره تیم‌ها
        $teams = array();
        if (isset($_POST['team_name']) && is_array($_POST['team_name'])) {
            for ($i = 0; $i < count($_POST['team_name']); $i++) {
                $teams[] = array(
                    'team_name'   => sanitize_text_field($_POST['team_name'][$i]),
                    'team_logo'   => esc_url_raw($_POST['team_logo'][$i]),
                    'alive'       => intval($_POST['alive'][$i]),
                    'KM1'         => intval($_POST['KM1'][$i]),
                    'KM2'         => intval($_POST['KM2'][$i]),
                    'KM3'         => intval($_POST['KM3'][$i]),
                    'KM4'         => intval($_POST['KM4'][$i]),
                    'KM5'         => intval($_POST['KM5'][$i]),
                    'bonus'       => intval($_POST['bonus'][$i]),
                    'PLC'         => intval($_POST['PLC'][$i]),
                    'win'         => intval($_POST['win'][$i]),
                    'pos-color'   => sanitize_text_field($_POST['pos-color'][$i]),
                    'active'      => isset($_POST['active'][$i]) ? 1 : 0,
                );
            }
        }
        update_option('livepoint_test_teams', $teams);

        echo '<div class="notice notice-success"><p>تنظیمات با موفقیت ذخیره شد (در دیتابیس جداگانه).</p></div>';
    }

    // ===== دریافت داده‌ها از آپشن‌های مخصوص تست =====
    $general = get_option('livepoint_test_general', array());
    $teams = get_option('livepoint_test_teams', array());
    ?>
    <div class="wrap">
        <h1>تنظیمات LivePoint (نسخه‌ی تست - مستقل از ACF)</h1>
        <p style="color: #888;">این پنل کاملاً جدا از تنظیمات ACF است و فقط برای تست و توسعه استفاده می‌شود.</p>
        <form method="post" action="">
            <?php wp_nonce_field('livepoint_test_nonce'); ?>
            <input type="hidden" name="save_livepoint_test" value="1">

            <h2>اطلاعات عمومی مسابقه</h2>
            <table class="form-table">
                <tr>
                    <th><label for="org">سازمان</label></th>
                    <td><input type="text" name="org" id="org" value="<?php echo esc_attr($general['org'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="match_info">اطلاعات مسابقه</label></th>
                    <td><input type="text" name="match_info" id="match_info" value="<?php echo esc_attr($general['match_info'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="org_logo">لوگوی سازمان</label></th>
                    <td><input type="url" name="org_logo" id="org_logo" value="<?php echo esc_url($general['org_logo'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="color_set">نوع رنگ‌بندی</label></th>
                    <td>
                        <select name="color_set" id="color_set">
                            <option value="top3" <?php selected($general['color_set'] ?? '', 'top3'); ?>>سه تیم اول</option>
                            <option value="all" <?php selected($general['color_set'] ?? '', 'all'); ?>>همه تیم‌ها</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <th><label for="team_number">تعداد تیم‌های دارای رنگ</label></th>
                    <td><input type="number" name="team_number" id="team_number" value="<?php echo esc_attr($general['team_number'] ?? 0); ?>"></td>
                </tr>
                <tr>
                    <th><label for="team_color">رنگ تیم‌ها (گرادیانت)</label></th>
                    <td><input type="text" name="team_color" id="team_color" value="<?php echo esc_attr($general['team_color'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="alive_icon">آیکون زنده</label></th>
                    <td><input type="url" name="alive_icon" id="alive_icon" value="<?php echo esc_url($general['alive_icon'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="top3_color">رنگ سه تیم اول</label></th>
                    <td><input type="text" name="top3_color" id="top3_color" value="<?php echo esc_attr($general['top3_color'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="img1">تصویر ۱</label></th>
                    <td><input type="url" name="img1" id="img1" value="<?php echo esc_url($general['img1'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="img2">تصویر ۲</label></th>
                    <td><input type="url" name="img2" id="img2" value="<?php echo esc_url($general['img2'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="img3">تصویر ۳</label></th>
                    <td><input type="url" name="img3" id="img3" value="<?php echo esc_url($general['img3'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="img4">تصویر ۴</label></th>
                    <td><input type="url" name="img4" id="img4" value="<?php echo esc_url($general['img4'] ?? ''); ?>" class="regular-text"></td>
                </tr>
                <tr>
                    <th><label for="img5">تصویر ۵</label></th>
                    <td><input type="url" name="img5" id="img5" value="<?php echo esc_url($general['img5'] ?? ''); ?>" class="regular-text"></td>
                </tr>
            </table>

            <h2>لیست تیم‌ها</h2>
            <div id="teams-container">
                <?php if (!empty($teams)) : ?>
                    <?php foreach ($teams as $index => $team) : ?>
                        <div class="team-row">
                            <h3>تیم <?php echo $index + 1; ?></h3>
                            <table class="form-table">
                                <tr>
                                    <th><label>نام تیم</label></th>
                                    <td><input type="text" name="team_name[]" value="<?php echo esc_attr($team['team_name'] ?? ''); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label>لوگو</label></th>
                                    <td><input type="url" name="team_logo[]" value="<?php echo esc_url($team['team_logo'] ?? ''); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label>تعداد زنده</label></th>
                                    <td><input type="number" name="alive[]" value="<?php echo esc_attr($team['alive'] ?? 0); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label>KM1 تا KM5</label></th>
                                    <td>
                                        <input type="number" name="KM1[]" value="<?php echo esc_attr($team['KM1'] ?? 0); ?>" placeholder="KM1">
                                        <input type="number" name="KM2[]" value="<?php echo esc_attr($team['KM2'] ?? 0); ?>" placeholder="KM2">
                                        <input type="number" name="KM3[]" value="<?php echo esc_attr($team['KM3'] ?? 0); ?>" placeholder="KM3">
                                        <input type="number" name="KM4[]" value="<?php echo esc_attr($team['KM4'] ?? 0); ?>" placeholder="KM4">
                                        <input type="number" name="KM5[]" value="<?php echo esc_attr($team['KM5'] ?? 0); ?>" placeholder="KM5">
                                    </td>
                                </tr>
                                <tr>
                                    <th><label>بونوس</label></th>
                                    <td><input type="number" name="bonus[]" value="<?php echo esc_attr($team['bonus'] ?? 0); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label>جایگاه (PLC)</label></th>
                                    <td><input type="number" name="PLC[]" value="<?php echo esc_attr($team['PLC'] ?? 0); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label>تعداد برد (win)</label></th>
                                    <td><input type="number" name="win[]" value="<?php echo esc_attr($team['win'] ?? 0); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label>رنگ</label></th>
                                    <td><input type="text" name="pos-color[]" value="<?php echo esc_attr($team['pos-color'] ?? ''); ?>"></td>
                                </tr>
                                <tr>
                                    <th><label>فعال</label></th>
                                    <td><input type="checkbox" name="active[]" value="1" <?php checked($team['active'] ?? 0, 1); ?>></td>
                                </tr>
                            </table>
                            <hr>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            <p>
                <button type="button" id="add-team" class="button">➕ افزودن تیم جدید</button>
            </p>

            <?php submit_button('ذخیره تنظیمات (تست)'); ?>
        </form>
    </div>

    <script>
        document.getElementById('add-team').addEventListener('click', function() {
            const container = document.getElementById('teams-container');
            const index = container.querySelectorAll('.team-row').length;
            const row = document.createElement('div');
            row.className = 'team-row';
            row.innerHTML = `
                <h3>تیم ${index + 1}</h3>
                <table class="form-table">
                    <tr><th><label>نام تیم</label></th><td><input type="text" name="team_name[]"></td></tr>
                    <tr><th><label>لوگو</label></th><td><input type="url" name="team_logo[]"></td></tr>
                    <tr><th><label>تعداد زنده</label></th><td><input type="number" name="alive[]"></td></tr>
                    <tr><th><label>KM1 تا KM5</label></th><td>
                        <input type="number" name="KM1[]" placeholder="KM1">
                        <input type="number" name="KM2[]" placeholder="KM2">
                        <input type="number" name="KM3[]" placeholder="KM3">
                        <input type="number" name="KM4[]" placeholder="KM4">
                        <input type="number" name="KM5[]" placeholder="KM5">
                    </td></tr>
                    <tr><th><label>بونوس</label></th><td><input type="number" name="bonus[]"></td></tr>
                    <tr><th><label>جایگاه (PLC)</label></th><td><input type="number" name="PLC[]"></td></tr>
                    <tr><th><label>تعداد برد (win)</label></th><td><input type="number" name="win[]"></td></tr>
                    <tr><th><label>رنگ</label></th><td><input type="text" name="pos-color[]"></td></tr>
                    <tr><th><label>فعال</label></th><td><input type="checkbox" name="active[]" value="1"></td></tr>
                </table>
                <hr>
            `;
            container.appendChild(row);
        });
    </script>
    <?php
}
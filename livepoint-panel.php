<?php
/**
 * LivePoint Panel - نسخه نهایی با دکمه حذف اصلاح‌شده
 */

if (!defined('ABSPATH')) exit;

add_action('admin_enqueue_scripts', 'lp_panel_enqueue_scripts');
function lp_panel_enqueue_scripts($hook) {
    if ($hook !== 'toplevel_page_lp-panel') return;
    wp_enqueue_media();
    wp_enqueue_script('jquery');
    
    wp_add_inline_script('jquery', '
        jQuery(document).ready(function($) {
            
            function showMessage(msg, type) {
                var el = $("#lp-message");
                var bg = type === "success" ? "#d4edda" : "#f8d7da";
                var color = type === "success" ? "#155724" : "#721c24";
                el.css({ display: "block", background: bg, color: color, border: "1px solid " + (type === "success" ? "#c3e6cb" : "#f5c6cb") });
                el.html(msg);
                setTimeout(function(){ el.fadeOut(); }, 4000);
            }

            // ===== کلید Enter فقط روی دکمه‌ها =====
            $(document).on("keydown", function(e) {
                if (e.key === "Enter" && !$(e.target).is("input, textarea, select")) {
                    e.preventDefault();
                    $("#lp-save-btn").click();
                }
            });

            // ===== دکمه‌های Reset =====
            $(document).on("click", "#reset-alive-btn", function(e) {
                e.preventDefault();
                if (confirm("✅ همه مقادیر Alive به 4 تنظیم شوند؟")) {
                    $(".team-alive").val(4).removeClass("dead");
                    $(".team-row").removeClass("dead").removeClass("lp-row-hidden");
                    showMessage("✅ همه Alive به ۴ تنظیم شدند.", "success");
                }
                $(this).blur();
            });

            $(document).on("click", "#reset-all-btn", function(e) {
                e.preventDefault();
                if (confirm("⚠️ همه مقادیر (به جز نام و لوگو) ریست شوند؟")) {
                    $(".team-win, .team-plc, .team-bonus, .team-km5, .team-km4, .team-km3, .team-km2, .team-km1").val(0);
                    $(".team-alive").val(4).removeClass("dead");
                    $(".team-name").val("");
                    $(".team-image-container").each(function() {
                        $(this).find(".team-logo-id").val("");
                        $(this).find(".team-image-preview").html("");
                        $(this).find(".team-image-wrap").hide();
                        $(this).find(".team-image-add").show();
                    });
                    $(".team-row").removeClass("dead").removeClass("lp-row-hidden");
                    showMessage("✅ همه مقادیر ریست شدند.", "success");
                }
                $(this).blur();
            });

            // ===== دکمه نمایش همه تیم‌ها =====
            $(document).on("click", "#show-all-teams-btn", function() {
                $(".team-row").removeClass("lp-row-hidden");
                showMessage("✅ همه تیم‌ها نمایش داده شدند.", "success");
                $(this).blur();
            });

            // ============================================================
            // ===== مخفی‌سازی ستون‌ها =====
            // ============================================================
            
            function saveColumnState(columnIndex, hidden) {
                var states = JSON.parse(localStorage.getItem("lp_hidden_columns") || "{}");
                states[columnIndex] = hidden;
                localStorage.setItem("lp_hidden_columns", JSON.stringify(states));
            }

            function getColumnStates() {
                return JSON.parse(localStorage.getItem("lp_hidden_columns") || "{}");
            }

            function applyColumnStates() {
                var states = getColumnStates();
                $("#teams-table thead th").each(function(index) {
                    if (states[index]) {
                        $(this).addClass("lp-hidden-column");
                        $("#teams-tbody tr").each(function() {
                            $(this).find("td").eq(index).addClass("lp-hidden-column");
                        });
                    }
                });
            }

            function addHideButtons() {
                $("#teams-table thead th").each(function(index) {
                    var $th = $(this);
                    var text = $th.text().trim();
                    if (["فعال", "رنگ", "نام تیم", "حذف", ""].includes(text)) return;
                    
                    var $btn = $(\'<button type="button" class="hide-col-btn" data-col="\' + index + \'" style="background:transparent; border:none; cursor:pointer; font-size:11px; margin-right:2px; color:#888;" title="مخفی کردن ستون">✕</button>\');
                    $th.prepend($btn);
                });
            }

            $(document).on("click", ".hide-col-btn", function(e) {
                e.preventDefault();
                e.stopPropagation();
                var colIndex = $(this).data("col");
                var $th = $("#teams-table thead th").eq(colIndex);
                var hidden = !$th.hasClass("lp-hidden-column");
                $th.toggleClass("lp-hidden-column", hidden);
                $("#teams-tbody tr").each(function() {
                    $(this).find("td").eq(colIndex).toggleClass("lp-hidden-column", hidden);
                });
                saveColumnState(colIndex, hidden);
                $(this).text(hidden ? "👁️" : "✕");
                $(this).css("color", hidden ? "#2271b1" : "#888");
            });

            function addResetColumnsButton() {
                var $container = $("#add-team-btn").parent();
                $container.append(\' <button type="button" id="reset-columns-btn" class="button" style="font-size:12px; padding:4px 12px;">👁️ نمایش همه ستون‌ها</button>\');
                $("#reset-columns-btn").on("click", function(e) {
                    e.preventDefault();
                    localStorage.removeItem("lp_hidden_columns");
                    $(".lp-hidden-column").removeClass("lp-hidden-column");
                    $(".hide-col-btn").text("✕").css("color", "#888");
                    showMessage("✅ همه ستون‌ها نمایش داده شدند.", "success");
                });
            }

            $(\'<style>.lp-hidden-column { display:none !important; } .hide-col-btn:hover { color:#2271b1 !important; }</style>\').appendTo("head");

            // ============================================================
            // ===== Media Uploader =====
            // ============================================================

            $(document).on("click", ".lp-image-add", function(e) {
                e.preventDefault();
                var container = $(this).closest(".lp-image-container");
                var targetId = container.find(".lp-image-id");
                var preview = container.find(".lp-image-preview");
                var wrap = container.find(".lp-image-wrap");
                var addBtn = container.find(".lp-image-add");
                var frame = wp.media({
                    title: "انتخاب تصویر",
                    multiple: false,
                    library: { type: "image" },
                    button: { text: "انتخاب" }
                });
                frame.on("select", function() {
                    var attachment = frame.state().get("selection").first().toJSON();
                    targetId.val(attachment.id);
                    preview.html(\'<img src="\' + attachment.url + \'" style="max-height:60px; max-width:150px;">\');
                    wrap.show();
                    addBtn.hide();
                    showMessage("✅ تصویر انتخاب شد.", "success");
                });
                frame.open();
            });

            $(document).on("click", ".lp-image-remove", function(e) {
                e.preventDefault();
                var container = $(this).closest(".lp-image-container");
                container.find(".lp-image-id").val("");
                container.find(".lp-image-preview").html("");
                container.find(".lp-image-wrap").hide();
                container.find(".lp-image-add").show();
                showMessage("✅ تصویر حذف شد.", "success");
            });

            $(document).on("click", ".lp-image-edit", function(e) {
                e.preventDefault();
                $(this).closest(".lp-image-container").find(".lp-image-add").click();
            });

            $(document).on("click", ".team-image-add", function(e) {
                e.preventDefault();
                var container = $(this).closest(".team-image-container");
                var targetId = container.find(".team-logo-id");
                var preview = container.find(".team-image-preview");
                var wrap = container.find(".team-image-wrap");
                var addBtn = container.find(".team-image-add");
                var frame = wp.media({
                    title: "انتخاب لوگو",
                    multiple: false,
                    library: { type: "image" },
                    button: { text: "انتخاب" }
                });
                frame.on("select", function() {
                    var attachment = frame.state().get("selection").first().toJSON();
                    targetId.val(attachment.id);
                    preview.html(\'<img src="\' + attachment.url + \'" style="max-height:35px; max-width:80px;">\');
                    wrap.show();
                    addBtn.hide();
                    showMessage("✅ لوگو انتخاب شد.", "success");
                });
                frame.open();
            });

            $(document).on("click", ".team-image-remove", function(e) {
                e.preventDefault();
                var container = $(this).closest(".team-image-container");
                container.find(".team-logo-id").val("");
                container.find(".team-image-preview").html("");
                container.find(".team-image-wrap").hide();
                container.find(".team-image-add").show();
                showMessage("✅ لوگو حذف شد.", "success");
            });

            $(document).on("click", ".team-image-edit", function(e) {
                e.preventDefault();
                $(this).closest(".team-image-container").find(".team-image-add").click();
            });

            // ============================================================
            // ===== مدیریت تیم‌ها =====
            // ============================================================

            $("#add-team-btn").on("click", function() {
                $("#no-teams-row").remove();
                var index = $(".team-row").length;
                var row = \'<tr class="team-row" data-index="\' + index + \'">\' +
                    \'<td style="text-align:center; border:1px solid #ddd; padding:4px;"><input type="checkbox" class="team-active" checked></td>\' +
                    \'<td style="text-align:center; border:1px solid #ddd; padding:4px;"><input type="color" class="team-color" value="#ff9800" style="width:40px; padding:0; border:none;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-win" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-plc" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-bonus" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km5" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km4" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km3" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km2" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km1" value="0" style="width:45px; padding:3px;"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-alive num-input" value="4" style="width:45px; padding:3px;" min="0" max="4" step="1"></td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;">\' +
                        \'<div class="team-image-container">\' +
                            \'<input type="hidden" class="team-logo-id" value="">\' +
                            \'<div class="team-image-wrap" style="display:none; position:relative; max-width:80px; max-height:35px;">\' +
                                \'<div class="team-image-preview" style="max-height:35px; max-width:80px; border:1px solid #ddd; padding:3px; background:#fff;"></div>\' +
                                \'<div class="team-image-actions" style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center; gap:10px;">\' +
                                    \'<button type="button" class="button button-small team-image-edit" style="padding:2px 8px; font-size:11px; background:transparent; color:#fff; border:1px solid #fff;">✏️</button>\' +
                                    \'<button type="button" class="button button-small team-image-remove" style="padding:2px 8px; font-size:11px; background:transparent; color:#fff; border:1px solid #fff;">✖</button>\' +
                                \'</div>\' +
                            \'</div>\' +
                            \'<button type="button" class="button button-small team-image-add" style="padding:2px 8px; font-size:11px;">افزودن تصویر</button>\' +
                        \'</div>\' +
                    \'</td>\' +
                    \'<td style="border:1px solid #ddd; padding:4px;"><input type="text" class="team-name" value="تیم جدید" style="width:100%; padding:3px; direction:rtl; text-align:right;"></td>\' +
                    \'<td style="text-align:center; border:1px solid #ddd; padding:4px;"><button type="button" class="button delete-team" style="background:#dc3545; color:#fff; border:none; padding:2px 10px; border-radius:4px; font-size:14px; cursor:pointer; line-height:24px;">✖</button></td>\' +
                \'</tr>\';
                $("#teams-tbody").append(row);
                showMessage("✅ تیم جدید اضافه شد.", "success");
                $(this).blur();
            });

            // ===== حذف تیم =====
            $(document).on("click", ".delete-team", function() {
                if (confirm("حذف شود؟")) {
                    $(this).closest("tr").remove();
                    showMessage("✅ تیم حذف شد.", "success");
                }
            });

            // ============================================================
            // ===== ذخیره =====
            // ============================================================

            $("#lp-save-btn").on("click", function(e) {
                e.preventDefault();
                var btn = $(this);
                btn.val("⏳ در حال ذخیره...").prop("disabled", true);

                var teams = [];
                var totalAlive = 0;
                var totalTeam = 0;

                $(".team-row").each(function() {
                    var alive = parseInt($(this).find(".team-alive").val()) || 0;
                    if (alive > 4) alive = 4;
                    if (alive < 0) alive = 0;
                    $(this).find(".team-alive").val(alive);
                    var active = $(this).find(".team-active").prop("checked") ? 1 : 0;
                    if (alive > 0) {
                        totalAlive += alive;
                        totalTeam++;
                    }
                    teams.push({
                        active: active,
                        color: $(this).find(".team-color").val(),
                        win: parseInt($(this).find(".team-win").val()) || 0,
                        plc: parseInt($(this).find(".team-plc").val()) || 0,
                        bonus: parseInt($(this).find(".team-bonus").val()) || 0,
                        km5: parseInt($(this).find(".team-km5").val()) || 0,
                        km4: parseInt($(this).find(".team-km4").val()) || 0,
                        km3: parseInt($(this).find(".team-km3").val()) || 0,
                        km2: parseInt($(this).find(".team-km2").val()) || 0,
                        km1: parseInt($(this).find(".team-km1").val()) || 0,
                        alive: alive,
                        logo_id: $(this).find(".team-logo-id").val(),
                        name: $(this).find(".team-name").val()
                    });
                });

                var general = {
                    org: $("#lp-org").val(),
                    match_info: $("#lp-match").val(),
                    org_logo_id: $("#lp-logo-id").val(),
                    total_team: totalTeam,
                    total_alive: totalAlive
                };

                $.post(ajaxurl, {
                    action: "lp_save_panel",
                    teams: teams,
                    general: general,
                    nonce: "' . wp_create_nonce('lp_panel') . '"
                }, function(res) {
                    btn.val("بروزرسانی").prop("disabled", false);
                    if (res.success) {
                        showMessage("✅ ذخیره شد!", "success");
                        $("#lp-total-team").text(totalTeam);
                        $("#lp-total-alive").text(totalAlive);
                    } else {
                        showMessage("❌ خطا: " + (res.data || "نامشخص"), "error");
                    }
                }).fail(function() {
                    btn.val("بروزرسانی").prop("disabled", false);
                    showMessage("❌ خطا در ارتباط با سرور", "error");
                });
            });

            // ============================================================
            // ===== هایلایت و مخفی‌سازی ردیف‌های با Alive=0 =====
            // ============================================================

            $(document).on("change", ".team-alive", function() {
                var val = parseInt($(this).val()) || 0;
                var row = $(this).closest("tr");
                if (val > 4) {
                    val = 4;
                    $(this).val(4);
                    showMessage("⚠️ مقدار Alive نمی‌تواند بیشتر از 4 باشد.", "error");
                } else if (val < 0) {
                    val = 0;
                    $(this).val(0);
                    showMessage("⚠️ مقدار Alive نمی‌تواند منفی باشد.", "error");
                }
                if (val < 1) {
                    $(this).addClass("dead");
                    row.addClass("lp-row-hidden");
                } else {
                    $(this).removeClass("dead");
                    row.removeClass("lp-row-hidden");
                }
            });

            // ============================================================
            // ===== هاور روی تصویر =====
            // ============================================================

            $(document).on("mouseenter", ".lp-image-wrap, .team-image-wrap", function() {
                $(this).find(".lp-image-actions, .team-image-actions").css("display", "flex");
            });
            $(document).on("mouseleave", ".lp-image-wrap, .team-image-wrap", function() {
                $(this).find(".lp-image-actions, .team-image-actions").css("display", "none");
            });

            // ============================================================
            // ===== اجرا =====
            // ============================================================
            addHideButtons();
            applyColumnStates();
            addResetColumnsButton();

        });
    ');
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
// ===== صفحه مدیریت =====
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
            /* ===== استایل‌های بخش تنظیمات عمومی ===== */
            .lp-general-box {
                background:#ffffff;
                border-radius:12px;
                box-shadow:0 2px 8px rgba(0,0,0,0.08);
                padding:20px 25px;
                margin-bottom:25px;
                border:1px solid #e2e6ea;
            }
            .lp-general-box .lp-box-header {
                border-bottom:2px solid #f0f2f5;
                padding-bottom:12px;
                margin-bottom:18px;
            }
            .lp-general-box .lp-box-header h2 {
                margin:0;
                font-size:18px;
                color:#1d2327;
                font-weight:600;
                display:flex;
                align-items:center;
                gap:8px;
            }
            .lp-general-box .lp-box-header h2::before {
                content:"⚙️";
                font-size:20px;
            }
            .lp-general-fields {
                display:flex;
                flex-wrap:wrap;
                gap:15px 20px;
            }
            .lp-general-fields .lp-field {
                flex:1 1 180px;
                min-width:150px;
            }
            .lp-general-fields .lp-field label {
                display:block;
                font-size:13px;
                font-weight:600;
                color:#2c3338;
                margin-bottom:4px;
            }
            .lp-general-fields .lp-field input[type="text"],
            .lp-general-fields .lp-field input[type="url"] {
                width:100%;
                padding:8px 12px;
                font-size:13px;
                border:1px solid #d0d5dd;
                border-radius:6px;
                background:#fafbfc;
                transition:border-color 0.2s;
            }
            .lp-general-fields .lp-field input:focus {
                border-color:#2271b1;
                background:#fff;
                outline:none;
                box-shadow:0 0 0 2px rgba(34,113,177,0.15);
            }
            .lp-general-fields .lp-field input[readonly] {
                background:#f0f2f5;
                color:#555;
                cursor:default;
            }
            .lp-general-fields .lp-field .lp-image-container {
                display:flex;
                align-items:center;
                gap:8px;
                flex-wrap:wrap;
            }
            .lp-general-fields .lp-field .lp-image-wrap {
                border:1px solid #d0d5dd;
                border-radius:6px;
                padding:4px;
                background:#fff;
                display:inline-block;
                position:relative;
            }
            .lp-general-fields .lp-field .lp-image-wrap .image-preview img {
                max-height:50px;
                max-width:120px;
                border-radius:4px;
            }
            .lp-general-fields .lp-field .lp-image-actions {
                position:absolute;
                top:0;
                left:0;
                right:0;
                bottom:0;
                background:rgba(0,0,0,0.5);
                display:none;
                justify-content:center;
                align-items:center;
                gap:8px;
                border-radius:4px;
            }
            .lp-general-fields .lp-field .lp-image-actions .button {
                background:transparent;
                color:#fff;
                border:1px solid #fff;
                padding:2px 10px;
                font-size:12px;
                border-radius:4px;
                cursor:pointer;
            }
            .lp-general-fields .lp-field .lp-image-actions .button:hover {
                background:#fff;
                color:#000;
            }
            .lp-general-fields .lp-field .lp-image-add {
                padding:4px 14px;
                font-size:12px;
                border-radius:6px;
                background:#f0f0f1;
                border:1px solid #d0d5dd;
                cursor:pointer;
                color:#2c3338;
            }
            .lp-general-fields .lp-field .lp-image-add:hover {
                background:#e2e4e7;
            }
            .lp-general-fields .lp-field .lp-image-add:active {
                background:#d0d2d5;
            }

            /* ===== استایل‌های دکمه‌های Reset و نمایش همه ===== */
            .reset-buttons {
                display:flex;
                gap:12px;
                align-items:center;
                flex-wrap:wrap;
            }
            .reset-buttons a {
                padding:6px 16px;
                border-radius:6px;
                font-size:13px;
                font-weight:500;
                text-decoration:none;
                transition:all 0.2s;
                cursor:pointer;
            }
            .reset-buttons a#reset-alive-btn {
                background:#e8f5e9;
                color:#2e7d32;
                border:1px solid #a5d6a7;
            }
            .reset-buttons a#reset-alive-btn:hover {
                background:#c8e6c9;
                box-shadow:0 2px 6px rgba(46,125,50,0.2);
            }
            .reset-buttons a#reset-all-btn {
                background:#ffebee;
                color:#c62828;
                border:1px solid #ef9a9a;
            }
            .reset-buttons a#reset-all-btn:hover {
                background:#ffcdd2;
                box-shadow:0 2px 6px rgba(198,40,40,0.2);
            }
            .reset-buttons #show-all-teams-btn {
                background:#e3f2fd;
                color:#0d47a1;
                border:1px solid #90caf9;
                padding:6px 16px;
                border-radius:6px;
                font-size:13px;
                font-weight:500;
                text-decoration:none;
                transition:all 0.2s;
                cursor:pointer;
            }
            .reset-buttons #show-all-teams-btn:hover {
                background:#bbdefb;
                box-shadow:0 2px 6px rgba(13,71,161,0.2);
            }

            /* ===== استایل‌های جدول ===== */
            .lp-table-wrapper {
                overflow-x:auto;
                background:#fff;
                border-radius:12px;
                padding:5px 10px 10px 10px;
                box-shadow:0 2px 8px rgba(0,0,0,0.06);
            }
            .lp-table {
                width:100%;
                border-collapse:collapse;
                font-size:13px;
                direction:rtl;
            }
            .lp-table th {
                padding:10px 6px;
                text-align:center;
                border-bottom:2px solid #e2e6ea;
                background:#f8f9fa;
                font-weight:600;
                color:#1d2327;
                white-space:nowrap;
            }
            .lp-table td {
                padding:6px 4px;
                text-align:center;
                border-bottom:1px solid #eef1f3;
                vertical-align:middle;
            }

            .lp-table .num-input::-webkit-inner-spin-button,
            .lp-table .num-input::-webkit-outer-spin-button {
                -webkit-appearance: none;
                margin: 0;
            }
            .lp-table .num-input {
                -moz-appearance: textfield;
            }
            .lp-table .num-input {
                width:44px;
                padding:4px;
                font-size:13px;
                border:1px solid #d0d5dd;
                border-radius:4px;
                text-align:center;
                background:#fff;
                transition:border-color 0.2s;
            }
            .lp-table .num-input:focus {
                border-color:#2271b1;
                outline:none;
                box-shadow:0 0 0 2px rgba(34,113,177,0.12);
            }
            .lp-table .color-input {
                width:36px;
                height:28px;
                padding:0;
                border:none;
                cursor:pointer;
                background:transparent;
            }
            .lp-table .name-input {
                width:100%;
                padding:4px 6px;
                font-size:13px;
                border:1px solid #d0d5dd;
                border-radius:4px;
                direction:rtl;
                text-align:right;
                background:#fff;
            }
            .lp-table .name-input:focus {
                border-color:#2271b1;
                outline:none;
                box-shadow:0 0 0 2px rgba(34,113,177,0.12);
            }
            .lp-table .btn-delete {
                background:#dc3545;
                color:#fff;
                border:none;
                padding:2px 10px;
                border-radius:4px;
                font-size:14px;
                cursor:pointer;
                transition:background 0.2s;
                line-height:24px;
            }
            .lp-table .btn-delete:hover {
                background:#c82333;
            }
            
            /* ===== هایلایت سلول Alive و مخفی‌سازی ردیف ===== */
            .lp-table .team-row.dead .num-input.dead {
                background-color:#ffcdd2 !important;
            }
            .team-row.lp-row-hidden {
                display: none !important;
            }
            
            .lp-table .team-image-wrap {
                position:relative;
                display:inline-block;
                border:1px solid #d0d5dd;
                border-radius:4px;
                padding:2px;
                background:#fff;
            }
            .lp-table .team-image-wrap .image-preview img {
                max-height:30px;
                max-width:60px;
                border-radius:3px;
            }
            .lp-table .team-image-wrap .lp-image-actions {
                position:absolute;
                top:0;
                left:0;
                right:0;
                bottom:0;
                background:rgba(0,0,0,0.5);
                display:none;
                justify-content:center;
                align-items:center;
                gap:4px;
                border-radius:3px;
            }
            .lp-table .team-image-wrap .lp-image-actions .button {
                background:transparent;
                color:#fff;
                border:1px solid #fff;
                padding:0 6px;
                font-size:10px;
                line-height:18px;
                min-height:18px;
                cursor:pointer;
            }
            .lp-table .team-image-wrap .lp-image-actions .button:hover {
                background:#fff;
                color:#000;
            }
            .lp-table .team-image-add {
                padding:0 8px;
                font-size:10px;
                line-height:18px;
                min-height:18px;
                background:#f0f0f1;
                border:1px solid #d0d5dd;
                border-radius:4px;
                cursor:pointer;
            }
            .lp-table .team-image-add:hover {
                background:#e2e4e7;
            }

            /* ===== دکمه‌های اصلی ===== */
            .lp-btn-add {
                padding:6px 18px;
                font-size:13px;
                border-radius:6px;
                background:#2271b1;
                border:none;
                color:#fff;
                cursor:pointer;
                font-weight:500;
                transition:background 0.2s;
            }
            .lp-btn-add:hover {
                background:#135e96;
            }
            .lp-btn-save {
                padding:8px 36px;
                font-size:15px;
                border-radius:6px;
                background:#2271b1;
                border:none;
                color:#fff;
                cursor:pointer;
                font-weight:600;
                transition:background 0.2s;
            }
            .lp-btn-save:hover {
                background:#135e96;
            }
            .lp-btn-save:disabled {
                opacity:0.7;
                cursor:not-allowed;
            }
            .lp-btn-save:disabled:hover {
                background:#2271b1;
            }

            /* ===== پیام ===== */
            .lp-message {
                display:none;
                padding:12px 18px;
                border-radius:8px;
                margin-bottom:18px;
                font-weight:500;
            }
            .lp-message.success {
                background:#e8f5e9;
                color:#2e7d32;
                border:1px solid #a5d6a7;
            }
            .lp-message.error {
                background:#ffebee;
                color:#c62828;
                border:1px solid #ef9a9a;
            }

            /* ===== مخفی‌سازی ستون‌ها ===== */
            .lp-hidden-column { display:none !important; }
            .hide-col-btn {
                background:transparent;
                border:none;
                cursor:pointer;
                font-size:11px;
                margin-right:2px;
                color:#888;
                padding:0 2px;
            }
            .hide-col-btn:hover { color:#2271b1 !important; }
            #teams-table th { position:relative; }
        </style>

        <!-- ===== هدر با دکمه‌ها ===== -->
        <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:20px;">
            <h1 style="font-size:24px; font-weight:700; color:#1d2327; margin:0; display:flex; align-items:center; gap:10px;">
                <span>🏆</span> LIVE POINT
            </h1>
            <div class="reset-buttons">
                <a href="#" id="reset-alive-btn">🔄 Reset Alive (4)</a>
                <a href="#" id="reset-all-btn">🔄 Reset All</a>
                <button type="button" id="show-all-teams-btn">👁️ نمایش همه تیم‌ها</button>
            </div>
        </div>

        <div id="lp-message" class="lp-message"></div>

        <form id="lp-panel-form" method="post">

            <!-- ===== تنظیمات عمومی ===== -->
            <div class="lp-general-box">
                <div class="lp-box-header">
                    <h2>تنظیمات مسابقه</h2>
                </div>
                <div class="lp-general-fields">
                    <div class="lp-field">
                        <label>نام برگزار کننده</label>
                        <input type="text" id="lp-org" value="<?php echo esc_attr($general['org'] ?? ''); ?>" placeholder="مثلاً TIAM PRO SERIES">
                    </div>
                    <div class="lp-field">
                        <label>اطلاعات Match</label>
                        <input type="text" id="lp-match" value="<?php echo esc_attr($general['match_info'] ?? ''); ?>" placeholder="مثلاً MATCH 4/4 - SANHOK">
                    </div>
                    <div class="lp-field" style="flex:1 1 200px;">
                        <label>لوگو</label>
                        <div class="lp-image-container">
                            <input type="hidden" id="lp-logo-id" class="lp-image-id" value="<?php echo esc_attr($general['org_logo_id'] ?? ''); ?>">
                            <div class="lp-image-wrap" style="<?php echo empty($org_logo_url) ? 'display:none;' : ''; ?>">
                                <div class="image-preview">
                                    <?php if ($org_logo_url): ?>
                                    <img src="<?php echo esc_url($org_logo_url); ?>">
                                    <?php endif; ?>
                                </div>
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

            <!-- ===== بخش تیم‌ها ===== -->
            <div style="background:#fff; border-radius:12px; box-shadow:0 2px 8px rgba(0,0,0,0.06); padding:20px 25px; border:1px solid #e2e6ea;">
                <div style="display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; margin-bottom:15px;">
                    <h2 style="margin:0; font-size:18px; font-weight:600; color:#1d2327; display:flex; align-items:center; gap:8px;">
                        <span>👥</span> لیست تیم‌ها
                    </h2>
                    <div style="display:flex; gap:10px; flex-wrap:wrap;">
                        <button type="button" id="add-team-btn" class="button button-primary lp-btn-add">➕ سطر جدید</button>
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
                                    <input type="number" class="team-alive num-input <?php echo (intval($t['alive'] ?? 0) < 1) ? 'dead' : ''; ?>" value="<?php echo esc_attr($t['alive'] ?? 4); ?>" min="0" max="4" step="1">
                                </td>
                                <td>
                                    <div class="team-image-container">
                                        <input type="hidden" class="team-logo-id" value="<?php echo esc_attr($t['logo_id'] ?? ''); ?>">
                                        <div class="team-image-wrap" style="<?php echo empty($logo_url) ? 'display:none;' : ''; ?>">
                                            <div class="image-preview">
                                                <?php if ($logo_url): ?>
                                                <img src="<?php echo esc_url($logo_url); ?>">
                                                <?php endif; ?>
                                            </div>
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
                    <input type="submit" id="lp-save-btn" class="button button-primary lp-btn-save" value="💾 ذخیره همه تغییرات">
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
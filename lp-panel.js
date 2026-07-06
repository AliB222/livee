jQuery(document).ready(function($) {
    console.log('✅ LivePoint Panel JS loaded successfully!');

    // ===== تابع نمایش پیام =====
    function showMessage(msg, type) {
        var el = $("#lp-message");
        if (!el.length) {
            console.error('❌ عنصر #lp-message پیدا نشد!');
            return;
        }
        var bg = type === "success" ? "#d4edda" : "#f8d7da";
        var color = type === "success" ? "#155724" : "#721c24";
        var borderColor = type === "success" ? "#c3e6cb" : "#f5c6cb";
        
        el.css({
            display: "block",
            background: bg,
            color: color,
            border: "1px solid " + borderColor
        });
        el.html(msg);

        if (window.lpMessageTimer) {
            clearTimeout(window.lpMessageTimer);
        }
        window.lpMessageTimer = setTimeout(function() {
            el.fadeOut();
        }, 4000);
    }

    // ===== کلید Enter (دکمه‌ها) =====
    $(document).on("keydown", function(e) {
        if (e.key === "Enter" && !$(e.target).is("input, textarea, select")) {
            e.preventDefault();
            $("#lp-save-btn").click();
        }
    });

    // ===== اینتر در فیلدها = فوکوس برداری + کلیک دکمه ذخیره =====
    $(document).on("keydown", "input[type='number'], input[type='text']", function(e) {
        if (e.key === "Enter") {
            e.preventDefault();
            $(this).blur();
            $("#lp-save-btn").click();
        }
    });

    // ===== حذف فلش‌های اسپین باکس =====
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            input[type="number"]::-webkit-outer-spin-button,
            input[type="number"]::-webkit-inner-spin-button {
                -webkit-appearance: none !important;
                margin: 0 !important;
            }
            input[type="number"] {
                -moz-appearance: textfield !important;
            }
            input[type="number"] {
                appearance: textfield !important;
            }
        `)
        .appendTo('head');

    // ===== رفع پس‌زمینه روشن در دارک مود =====
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .lp-match-row,
            .lp-match-row:focus,
            .lp-match-row:hover,
            .lp-match-row:active {
                background: #fafbfc !important;
                border-color: #d0d5dd !important;
                box-shadow: none !important;
                outline: none !important;
            }
            .lp-match-row:focus-visible {
                outline: none !important;
                box-shadow: none !important;
            }
        `)
        .appendTo('head');

    // ============================================================
    // ===== چک‌باکس نمایش همه تیم‌ها =====
    // ============================================================
    function applyShowAllTeamsState() {
        var showAll = localStorage.getItem('lp_show_all_teams') === 'true';
        $('#show-all-teams-checkbox').prop('checked', showAll);
        if (showAll) {
            $('.team-row').removeClass('lp-row-hidden');
        } else {
            $('.team-row').each(function() {
                var alive = parseInt($(this).find('.team-alive').val()) || 0;
                if (alive < 1) {
                    $(this).addClass('lp-row-hidden');
                } else {
                    $(this).removeClass('lp-row-hidden');
                }
            });
        }
    }

    $(document).on('change', '#show-all-teams-checkbox', function() {
        var isChecked = $(this).is(':checked');
        localStorage.setItem('lp_show_all_teams', isChecked ? 'true' : 'false');
        applyShowAllTeamsState();
    });

    // ===== دکمه‌های Reset =====
    $(document).on("click", "#reset-alive-btn", function(e) {
        e.preventDefault();
        if (confirm("✅ همه مقادیر Alive به 4 تنظیم شوند؟")) {
            $(".team-alive").val(4).removeClass("dead").css("background-color", "");
            $(".team-row").removeClass("dead");
            applyShowAllTeamsState();
            showMessage("✅ همه Alive به ۴ تنظیم شدند.", "success");
        }
        $(this).blur();
    });

    $(document).on("click", "#reset-all-btn", function(e) {
        e.preventDefault();
        if (confirm("⚠️ همه مقادیر (به جز نام و لوگو) ریست شوند؟")) {
            $(".team-win, .team-plc, .team-bonus, .team-km5, .team-km4, .team-km3, .team-km2, .team-km1").val(0);
            $(".team-alive").val(4).removeClass("dead").css("background-color", "");
            $(".team-name").val("");
            $(".team-image-container").each(function() {
                $(this).find(".team-logo-id").val("");
                $(this).find(".image-preview").html("");
                $(this).find(".team-image-wrap").hide();
                $(this).find(".team-image-add").show();
            });
            $(".team-row").removeClass("dead");
            applyShowAllTeamsState();
            showMessage("✅ همه مقادیر ریست شدند.", "success");
        }
        $(this).blur();
    });

    // ===== مدیریت ستون‌ها =====
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
            var $btn = $('<button type="button" class="hide-col-btn" data-col="' + index + '" style="background:transparent; border:none; cursor:pointer; font-size:11px; margin-right:2px; color:#888;" title="مخفی کردن ستون">✕</button>');
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
        if ($("#reset-columns-btn").length === 0) {
            $container.append(' <button type="button" id="reset-columns-btn" class="button" style="font-size:12px; padding:4px 12px;">👁️ نمایش همه ستون‌ها</button>');
        }
    }

    $(document).on("click", "#reset-columns-btn", function(e) {
        e.preventDefault();
        localStorage.removeItem("lp_hidden_columns");
        $(".lp-hidden-column").removeClass("lp-hidden-column");
        $(".hide-col-btn").text("✕").css("color", "#888");
        showMessage("✅ همه ستون‌ها نمایش داده شدند.", "success");
    });

    $('<style>.lp-hidden-column { display:none !important; } .hide-col-btn:hover { color:#2271b1 !important; }</style>').appendTo("head");

    // ============================================================
    // ===== لوگوی عمومی (کلیک روی تصویر = ویرایش) =====
    // ============================================================
    $(document).on("click", ".lp-image-wrap .image-preview img", function(e) {
        e.preventDefault();
        var container = $(this).closest(".lp-image-container");
        container.find(".lp-image-add").click();
    });

    // ============================================================
    // ===== لوگوی تیم‌ها (کلیک روی تصویر = ویرایش) =====
    // ============================================================
    $(document).on("click", ".team-image-wrap .image-preview img", function(e) {
        e.preventDefault();
        var container = $(this).closest(".team-image-container");
        container.find(".team-image-add").click();
    });

    // ============================================================
    // ===== دکمه حذف لوگوی عمومی =====
    // ============================================================
    $(document).on("click", ".lp-image-remove-btn", function(e) {
        e.preventDefault();
        e.stopPropagation();
        var container = $(this).closest(".lp-image-container");
        container.find(".lp-image-id").val("");
        container.find(".image-preview").html("");
        container.find(".lp-image-wrap").hide();
        container.find(".lp-image-add").show();
        showMessage("✅ تصویر حذف شد.", "success");
    });

    // ============================================================
    // ===== دکمه حذف لوگوی تیم‌ها =====
    // ============================================================
    $(document).on("click", ".team-image-remove-btn", function(e) {
        e.preventDefault();
        e.stopPropagation();
        var container = $(this).closest(".team-image-container");
        container.find(".team-logo-id").val("");
        container.find(".image-preview").html("");
        container.find(".team-image-wrap").hide();
        container.find(".team-image-add").show();
        showMessage("✅ لوگو حذف شد.", "success");
    });

    // ===== Media Uploader عمومی =====
    $(document).on("click", ".lp-image-add", function(e) {
        e.preventDefault();
        var container = $(this).closest(".lp-image-container");
        var targetId = container.find(".lp-image-id");
        var preview = container.find(".image-preview");
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
            preview.html('<img src="' + attachment.url + '" style="max-height:60px; max-width:150px;">');
            wrap.show();
            addBtn.hide();
            showMessage("✅ تصویر انتخاب شد.", "success");
        });
        frame.open();
    });

    // ===== Media Uploader لوگوی تیم‌ها =====
    $(document).on("click", ".team-image-add", function(e) {
        e.preventDefault();
        var container = $(this).closest(".team-image-container");
        var targetId = container.find(".team-logo-id");
        var preview = container.find(".image-preview");
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
            preview.html('<img src="' + attachment.url + '" style="max-height:35px; max-width:80px;">');
            wrap.show();
            addBtn.hide();
            showMessage("✅ لوگو انتخاب شد.", "success");
        });
        frame.open();
    });

    // ===== اضافه و حذف تیم =====
    $("#add-team-btn").on("click", function() {
        $("#no-teams-row").remove();
        var index = $(".team-row").length;
        var row = '<tr class="team-row" data-index="' + index + '">' +
            '<td style="text-align:center; border:1px solid #ddd; padding:4px;"><input type="checkbox" class="team-active" checked></td>' +
            '<td style="text-align:center; border:1px solid #ddd; padding:4px;"><input type="color" class="team-color" value="#ff9800" style="width:40px; padding:0; border:none;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-win" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-plc" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-bonus" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km5" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km4" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km3" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km2" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-km1" value="0" style="width:45px; padding:3px;"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="number" class="team-alive num-input" value="4" style="width:45px; padding:3px;" min="0" max="4" step="1"></td>' +
            '<td style="border:1px solid #ddd; padding:4px;">' +
                '<div class="team-image-container">' +
                    '<input type="hidden" class="team-logo-id" value="">' +
                    '<div class="team-image-wrap" style="display:none; position:relative; max-width:80px; max-height:35px;">' +
                        '<div class="image-preview" style="cursor:pointer; position:relative; display:inline-block; max-height:35px; max-width:80px; border:1px solid #ddd; padding:3px; background:#fff;"></div>' +
                        '<button type="button" class="button button-small team-image-add" style="padding:2px 8px; font-size:11px;">افزودن تصویر</button>' +
                    '</div>' +
                '</div>' +
            '</td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="text" class="team-name" value="تیم جدید" style="width:100%; padding:3px; direction:rtl; text-align:right;"></td>' +
            '<td style="text-align:center; border:1px solid #ddd; padding:4px;"><button type="button" class="button delete-team" style="background:#dc3545; color:#fff; border:none; padding:2px 10px; border-radius:4px; font-size:14px; cursor:pointer; line-height:24px;">✖</button></td>' +
        '</tr>';
        $("#teams-tbody").append(row);
        applyShowAllTeamsState();
        showMessage("✅ تیم جدید اضافه شد.", "success");
        $(this).blur();
    });

    $(document).on("click", ".delete-team", function() {
        if (confirm("حذف شود؟")) {
            $(this).closest("tr").remove();
            var totalTeam = $(".team-row").length;
            var totalAlive = 0;
            $(".team-row").each(function() {
                var alive = parseInt($(this).find(".team-alive").val()) || 0;
                if (alive > 0) totalAlive += alive;
            });
            $("#lp-total-team").text(totalTeam);
            $("#lp-total-alive").text(totalAlive);
            applyShowAllTeamsState();
            showMessage("✅ تیم حذف شد.", "success");
        }
    });

    // ============================================================
    // ===== ذخیره تغییرات =====
    // ============================================================
    $(document).on("click", "#lp-save-btn", function(e) {
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

        var matchRows = {};
        $(".lp-match-row").each(function() {
            var match = $(this).data("match");
            var val = $(this).val();
            if (val && parseInt(val) > 0) {
                matchRows['match_row_' + match] = parseInt(val);
            }
        });

        $.post(lp_ajax.ajaxurl, {
            action: "lp_save_panel",
            teams: teams,
            general: general,
            match_rows: matchRows,
            nonce: lp_ajax.nonce
        }, function(res) {
            btn.val("💾 ذخیره همه تغییرات").prop("disabled", false);
            if (res.success) {
                showMessage("✅ ذخیره شد!", "success");
                $("#lp-total-team").text(totalTeam);
                $("#lp-total-alive").text(totalAlive);
                localStorage.setItem('lp_header_update', Date.now().toString());
                localStorage.setItem('lp_match_logos_update', Date.now().toString());
                console.log('📢 تغییرات ذخیره شد و به صفحات دیگر اطلاع داده شد.');
                applyShowAllTeamsState();
            } else {
                showMessage("❌ خطا: " + (res.data || "نامشخص"), "error");
            }
        }).fail(function() {
            btn.val("💾 ذخیره همه تغییرات").prop("disabled", false);
            showMessage("❌ خطا در ارتباط با سرور", "error");
        });
    });

    // ============================================================
    // ===== زنده/مرده بودن تیم‌ها (با تغییر رنگ لحظه‌ای) =====
    // ============================================================
    $(document).on("change", ".team-alive", function() {
        var val = parseInt($(this).val()) || 0;
        var row = $(this).closest("tr");
        if (val > 4) { val = 4; $(this).val(4); }
        else if (val < 0) { val = 0; $(this).val(0); }
        if (val < 1) {
            $(this).addClass("dead").css("background-color", "#ffcdd2");
            row.addClass("dead");
        } else {
            $(this).removeClass("dead").css("background-color", "");
            row.removeClass("dead");
        }
        applyShowAllTeamsState();
    });

    $(document).on("mouseenter", ".lp-image-wrap, .team-image-wrap", function() {
        $(this).find(".lp-image-actions, .team-image-actions").css("display", "flex");
    });
    $(document).on("mouseleave", ".lp-image-wrap, .team-image-wrap", function() {
        $(this).find(".lp-image-actions, .team-image-actions").css("display", "none");
    });

    // ===== اجرای توابع اولیه =====
    addHideButtons();
    applyColumnStates();
    addResetColumnsButton();
    applyShowAllTeamsState();
});
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

    // ===== کلید Enter =====
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

    $(document).on("click", "#show-all-teams-btn", function() {
        $(".team-row").removeClass("lp-row-hidden");
        showMessage("✅ همه تیم‌ها نمایش داده شدند.", "success");
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

    // ===== Media Uploader =====
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
            preview.html('<img src="' + attachment.url + '" style="max-height:60px; max-width:150px;">');
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
            preview.html('<img src="' + attachment.url + '" style="max-height:35px; max-width:80px;">');
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
                        '<div class="team-image-preview" style="max-height:35px; max-width:80px; border:1px solid #ddd; padding:3px; background:#fff;"></div>' +
                        '<div class="team-image-actions" style="position:absolute; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); display:none; justify-content:center; align-items:center; gap:10px;">' +
                            '<button type="button" class="button button-small team-image-edit" style="padding:2px 8px; font-size:11px; background:transparent; color:#fff; border:1px solid #fff;">✏️</button>' +
                            '<button type="button" class="button button-small team-image-remove" style="padding:2px 8px; font-size:11px; background:transparent; color:#fff; border:1px solid #fff;">✖</button>' +
                        '</div>' +
                    '</div>' +
                    '<button type="button" class="button button-small team-image-add" style="padding:2px 8px; font-size:11px;">افزودن تصویر</button>' +
                '</div>' +
            '</td>' +
            '<td style="border:1px solid #ddd; padding:4px;"><input type="text" class="team-name" value="تیم جدید" style="width:100%; padding:3px; direction:rtl; text-align:right;"></td>' +
            '<td style="text-align:center; border:1px solid #ddd; padding:4px;"><button type="button" class="button delete-team" style="background:#dc3545; color:#fff; border:none; padding:2px 10px; border-radius:4px; font-size:14px; cursor:pointer; line-height:24px;">✖</button></td>' +
        '</tr>';
        $("#teams-tbody").append(row);
        showMessage("✅ تیم جدید اضافه شد.", "success");
        $(this).blur();
    });

    $(document).on("click", ".delete-team", function() {
        if (confirm("حذف شود؟")) {
            $(this).closest("tr").remove();
            showMessage("✅ تیم حذف شد.", "success");
        }
    });

    // ============================================================
    // ===== ذخیره تغییرات (با استفاده از داده‌های ارسالی از PHP) =====
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

        // استفاده از داده‌های ارسالی از PHP (lp_ajax)
        $.post(lp_ajax.ajaxurl, {
            action: "lp_save_panel",
            teams: teams,
            general: general,
            nonce: lp_ajax.nonce
        }, function(res) {
            btn.val("💾 ذخیره همه تغییرات").prop("disabled", false);
            if (res.success) {
                showMessage("✅ ذخیره شد!", "success");
                $("#lp-total-team").text(totalTeam);
                $("#lp-total-alive").text(totalAlive);
            } else {
                showMessage("❌ خطا: " + (res.data || "نامشخص"), "error");
            }
        }).fail(function() {
            btn.val("💾 ذخیره همه تغییرات").prop("disabled", false);
            showMessage("❌ خطا در ارتباط با سرور", "error");
        });
    });

    // ===== زنده/مرده بودن تیم‌ها =====
    $(document).on("change", ".team-alive", function() {
        var val = parseInt($(this).val()) || 0;
        var row = $(this).closest("tr");
        if (val > 4) { val = 4; $(this).val(4); }
        else if (val < 0) { val = 0; $(this).val(0); }
        
        if (val < 1) {
            $(this).addClass("dead");
            row.addClass("lp-row-hidden");
        } else {
            $(this).removeClass("dead");
            row.removeClass("lp-row-hidden");
        }
    });

    $(document).on("mouseenter", ".lp-image-wrap, .team-image-wrap", function() {
        $(this).find(".lp-image-actions, .team-image-actions").css("display", "flex");
    });
    $(document).on("mouseleave", ".lp-image-wrap, .team-image-wrap", function() {
        $(this).find(".lp-image-actions, .team-image-actions").css("display", "none");
    });

    // اجرای توابع اولیه
    addHideButtons();
    applyColumnStates();
    addResetColumnsButton();
});
jQuery(document).ready(function ($) {
    let lastTimestamp = 0;
    let isFirstLoad = true;

    function updateTeamsInfo() {
        $.ajax({
            url: '/livepoint/wp-content/plugins/livePoint/api.php?_=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                // ===== چک هوشمند: فقط در صورت تغییر داده‌ها آپدیت کن =====
                const newTimestamp = data.timestamp || Date.now();
                if (newTimestamp > lastTimestamp || isFirstLoad) {
                    lastTimestamp = newTimestamp;
                    isFirstLoad = false;

                    if (data.teams && data.teams.length > 0) {
                        var teams = data.teams;
                        var html = '';

                        $.each(teams, function (index, team) {
                            var teamClass = team.class || '';

                            html += '<div class="name animated ' + teamClass + '">';
                            html += '  <div class="alive">';
                            if (team.alive == 0) {
                                html += '<img src="http://localhost/livepoint/wp-content/plugins/livePoint/img/helmet.svg" style="width:20px;">';
                            } else {
                                for (var i = 1; i <= team.alive; i++) {
                                    html += '<img src="http://localhost/livepoint/wp-content/plugins/livePoint/img/helmet.svg" style="width:20px;">';
                                }
                            }
                            html += '  </div>';
                            html += '  <div class="kills">' + team.kills + '</div>';
                            html += '  <div class="plc">' + team.plc + '</div>';
                            html += '  <div class="total">' + team.total + '</div>';
                            html += '</div>';
                        });

                        $('#teams-container').html(html);
                    }
                }
            },
            error: function (xhr, status, error) {
                console.error('❌ خطا در دریافت داده‌ها:', error);
            }
        });
    }

    // ===== اجرای اولیه =====
    updateTeamsInfo();

    // ===== به‌روزرسانی هر ۱۵۰۰ میلی‌ثانیه =====
    setInterval(updateTeamsInfo, 1500);
});
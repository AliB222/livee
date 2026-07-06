console.log('✅ main.js اجرا شد!');
jQuery(document).ready(function ($) {
    function updateTeamsInfo() {
        $.ajax({
            url: '/livepoint/wp-content/plugins/livePoint/api.php?_=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                if (data.teams && data.teams.length > 0) {
                    var teams = data.teams;
                    var html = '';

                    $.each(teams, function (index, team) {
                        // ===== دریافت کلاس از داده =====
                        var teamClass = team.class || '';

                        // ===== ساخت کارت تیم با کلاس =====
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
            },
            error: function (xhr, status, error) {
                console.error('❌ خطا در دریافت داده‌ها:', error);
            }
        });
    }

    updateTeamsInfo();
    setInterval(updateTeamsInfo, 1000);
});
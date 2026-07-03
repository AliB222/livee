$(document).ready(function () {
    function updateTeamsInfo() {
        $.ajax({
            url: 'http://localhost/livepoint/wp-content/plugins/livePoint/winner/winner-ajax.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#team').html(data.html);
                var teams = data.teams;
            },
            error: function (xhr, status, error) {
                console.error('خطا در درخواست Ajax: ' + status);
            }
        });
    }

    // اجرای تابع updateTeamsInfo هر ۳ ثانیه
    setInterval(updateTeamsInfo, 100);
});

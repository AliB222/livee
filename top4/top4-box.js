$(document).ready(function () {
    function updateTeamsInfo() {
        $.ajax({
            url: '../wp-content/plugins/livePoint/top4/top4box-ajax.php?_=' + new Date().getTime(),
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#live').html(data.html);
                var teams = data.teams;
            },
            error: function (xhr, status, error) {
                console.error('خطا در درخواست Ajax: ' + status);
            }
        });
    }

    // اجرای تابع هر ۵۰۰ میلی‌ثانیه
    setInterval(updateTeamsInfo, 500);
});
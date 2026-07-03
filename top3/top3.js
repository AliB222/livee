$(document).ready(function () {
    function updateTeamsInfo() {
        $.ajax({
            url: 'https://itsalib2.ir/wp-content/plugins/livePoint/top3/top3-ajax.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#liveBox').html(data.html);
                var teams = data.teams;
            },
            error: function (xhr, status, error) {
                console.error('خطا در درخواست Ajax: ' + status);
            }
        });
    }

    // اجرای تابع updateTeamsInfo هر ۳ ثانیه
    setInterval(updateTeamsInfo, 1000);
});
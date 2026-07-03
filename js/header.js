jQuery(document).ready(function () {
    function updateTeamsInfo() {
        jQuery.ajax({
            url: 'https://itsalib2.ir/wp-content/plugins/livePoint/header-ajax.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                jQuery('#liveBox').html(data.html);
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


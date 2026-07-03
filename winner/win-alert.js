$(document).ready(function () {
    function updateTeamsInfo2() {
        $.ajax({
            url: 'https://itsalib2.ir/wp-content/plugins/livePoint/winner/win-alert-ajax.php',
            type: 'GET',
            dataType: 'json',
            success: function (data) {
                $('#win').html(data.html);
                var teams = data.teams;
            },
            error: function (xhr, status, error) {
                console.error('خطا در درخواست Ajax: ' + status);
            }
        });
    }

    // اجرای تابع updateTeamsInfo هر ۳ ثانیه
    setInterval(updateTeamsInfo2, 100);
});

setInterval(function () {
    let parent2 = document.getElementById("win");
    if (parent2.children.length > 0) {
        parent2.classList.add("animate__animated", "animate__backInUp");
    }
    else {
        parent2.classList.remove("animate__animated", "animate__backInUp");
    }
}, 1);

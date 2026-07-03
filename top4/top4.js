$(document).ready(function () {
    function updateTeamsInfo() {
        $.ajax({
            url: 'https://itsalib2.ir/wp-content/plugins/livePoint/top4/top4-ajax.php',
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
    setInterval(updateTeamsInfo, 1000);
});

setInterval(function () {
    let parent1 = document.getElementById("team");
    let parent2 = document.getElementById("live");
    if (parent1.children.length > 0) {
        parent2.classList.add("animate__fadeOut", "animate__delay-3s");
        parent1.classList.add("animate__animated");
        parent1.classList.add("animate__fadeInDown");
        parent1.classList.add("animate__delay-5s");
    }
    else {
        parent2.classList.remove("animate__fadeOut", "animate__delay-3s");
        parent1.classList.remove("animate__animated");
        parent1.classList.remove("animate__fadeInDown");
        parent1.classList.remove("animate__delay-5s");
    }
}, 1);
setInterval(function () {
    let parent2 = document.getElementById("live");
    if (parent2.children.length > 0) {
        parent2.classList.add("animate__animated");
        parent2.classList.add("animate__fadeInDown");

    }
    else {
        parent2.classList.remove("animate__animated");
        parent2.classList.remove("animate__fadeInDown");
       
    }
}, 2);
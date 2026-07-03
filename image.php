<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="../wp-content/plugins/livePoint/css/main.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas%20Neue"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <title>LivePoint</title>
    <style>
.images img {
    width: 200px;
    padding: 40px;
    margin: 20px 40px;
}
.images {
    display: flex;
    flex-direction: row;
}
    </style>

    <body>
    <?php
        $img1 = get_field("img1", 'option');
        $img2 = get_field("img2", 'option');
        $img3 = get_field("img3", 'option');
        $img4 = get_field("img4", 'option');
        $img5 = get_field("img5", 'option');
    ?>
    <div class="images">
        <img src="<?php echo $img1; ?>">
        <img src="<?php echo $img2; ?>">
        <img src="<?php echo $img3; ?>">
        <img src="<?php echo $img4; ?>">
        <img src="<?php echo $img5; ?>">
    </div>
    <script>
    $(document).ready(function () {
    function updateTeamsInfo() {
        $.ajax({
            url: 'https://itsalib2.ir/wp-content/plugins/livePoint/image-ajax.php',
            type: 'GET',
            dataType: 'json', 
            success: function (data) {
                $('.images').html(data.html);
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


    </script>
    </body>
</html>
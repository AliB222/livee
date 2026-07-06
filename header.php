<?php
// دریافت داده‌های عمومی از پنل جدید
$general = get_option('lp_general', []);
$org = $general['org'] ?? '';
$match_info = $general['match_info'] ?? '';
$logo_id = $general['org_logo_id'] ?? '';
$logo_url = '';

if ($logo_id) {
    $img = wp_get_attachment_image_src($logo_id, 'medium');
    if ($img) {
        $logo_url = $img[0];
    }
}

// (اختیاری) تنظیمات رنگ
$color_set = get_option('lp_color_set', 'top3');
$team_num_color = get_option('lp_team_num_color', 5);
$team_color = get_option('lp_team_color', '#ff9800');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <link rel="stylesheet" href="../wp-content/plugins/livePoint/css/main.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas%20Neue"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <title>LivePoint</title>
    <style>
        #displayBox {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .team-info-box {
            border: 1px solid #ccc;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .team-info-box img {
            max-width: 100px;
            max-height: 100px;
            margin-bottom: 5px;
        }
        <?php
        if ($color_set == "top3") {
            echo '
            #team1 .rank {background: linear-gradient(0deg, rgba(255,215,0,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}
            #team2 .rank {background: linear-gradient(0deg, rgba(192,192,192,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}
            #team3 .rank {background: linear-gradient(0deg, rgba(205,127,50,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}';
        } else {
            for ($i = 1; $i <= intval($team_num_color); $i++) {
                printf("#team%u .rank {background:%s} \n", $i, $team_color);
            }
        }
        ?>
    </style>
</head>
<body>
    <div id="displayBox"></div>
    <div id="liveBox">
        <div id="info">
            <div class="infoImg">
                <img src="<?php echo esc_url($logo_url); ?>" alt="">
            </div>
            <div class="infoTxt">
                <p><?php echo esc_html($org); ?></p>
                <p><?php echo esc_html($match_info); ?></p>
            </div>
        </div>
        <div id="teamsHead">
            <div class="headText"><p>team</p></div>
            <div class="headText"><p>alive</p></div>
            <div class="headText"><p>kills</p></div>
            <div class="headText"><p>plc</p></div>
            <div class="headText"><p>total</p></div>
        </div>
    </div>

    <!-- ===== اسکریپت به‌روزرسانی خودکار با تایمر (بدون localStorage) ===== -->
    <script>
    jQuery(document).ready(function($) {
        // ===== تابع دریافت داده‌های هدر =====
        function fetchHeaderData() {
            $.ajax({
                url: '../wp-content/plugins/livePoint/header-ajax.php?_=' + new Date().getTime(),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.html) {
                        // فقط بخش info و teamsHead را به‌روز کن (نه کل liveBox)
                        // برای جلوگیری از خطا، کل liveBox رو عوض می‌کنیم
                        $('#liveBox').html(data.html);
                        console.log('✅ هدر به‌روز شد');
                    }
                },
                error: function(xhr, status, error) {
                    console.error('❌ خطا در دریافت داده‌های هدر:', error);
                }
            });
        }

        // ===== اجرای اولیه =====
        fetchHeaderData();

        // ===== به‌روزرسانی هر ۲ ثانیه =====
        setInterval(fetchHeaderData, 2000);
    });
    </script>
</body>
</html>
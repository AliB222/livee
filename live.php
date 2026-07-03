<?php
/**
 * صفحه نمایش اسکوربرد زنده
 * این فایل بعد از بارگذاری کامل وردپرس و ACF اجرا می‌شود
 */

// بارگذاری محیط وردپرس - مسیر اصلاح شد (3 سطح بالا)
require_once( dirname(__FILE__) . '/../../../wp-load.php' );

// اگر ACF فعال نیست، خطای دوستانه نشان بده
if ( ! function_exists('get_field') ) {
    wp_die('خطا: پلاگین Advanced Custom Fields (ACF) فعال نیست یا نصب نشده است. لطفاً آن را از پنل ادمین فعال کنید.');
}

// ---------- کدهای اصلی خودت (همان کدهای قبلی) ----------
$org = get_field("org", 'option');
$match_info = get_field("match_info", 'option');
$org_logo = get_field("org_logo", 'option');
$color_set = get_field("color_set", 'options');
$team_num_color = get_field("team-number", 'options');
$team_color = get_field("team-color", 'options');
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
    <link rel="stylesheet" href="http://localhost/livepoint/wp-content/plugins/livePoint/css/main.css" />
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
.teams {
    transition: transform 0.5s ease, opacity 0.5s ease;
}
.moving {
    transform: translateY(0);
}
.hidden {
    opacity: 0;
    transform: translateY(-100%);
}
    <?php
        if ($color_set == "top3"){
            echo '
            #team1 .rank {background: linear-gradient(0deg, rgba(255,215,0,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}
            #team2 .rank {background: linear-gradient(0deg, rgba(192,192,192,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}
            #team3 .rank {background: linear-gradient(0deg, rgba(205,127,50,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}';
        }else{
            for ($i = 1; $i <= $team_num_color; $i++) {
                printf("#team%u .rank {background:%s} \n",$i,$team_color);
            }
        }
    ?>
.killed-notification {
    position: absolute;
    top: 0;
    right: 0;
    height: 100%;
    width: 80%;
    opacity: 0;
    background-color:#ab1818;
    color: white;
    text-align: center;
    padding: 5px;
    font-weight: bold;
    overflow: hidden;
    white-space: nowrap;
    font-size: 25px;
    letter-spacing: 4px;
}
.killed-notification.show {
    opacity:1;
}

    </style>
  </head>
<body>
    <div id="displayBox"></div>
    <div id="liveBox">
        <div id="info"></div>
    </div>

<script>
    const apiUrl = 'http://localhost/livepoint/wp-content/plugins/livePoint/api.php';
    const aliveIcon = 'http://localhost/livepoint/wp-content/uploads/2023/11/Asset-1.svg';

    function updateTeams() {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                let teams = data.teams || [];
                
                // مرتب‌سازی تیم‌ها بر اساس امتیاز
                teams = teams.map(team => ({
                    ...team,
                    kills: parseInt(team.kills) || 0,
                    total: parseInt(team.total) || 0
                }));

                teams.sort((a, b) => {
                    if (b.total !== a.total) return b.total - a.total;
                    if (b.win !== a.win) return b.win - a.win;
                    if (b.plc !== a.plc) return b.plc - a.plc;
                    return b.kills - a.kills;
                });

                const infoDiv = document.getElementById('info');
                if (!infoDiv) return;

                // ===== بازسازی کامل جدول بدون انیمیشن =====
                infoDiv.innerHTML = '';

                teams.forEach((team, index) => {
                    const teamDiv = createTeamElement(team, index);
                    infoDiv.appendChild(teamDiv);
                });

            })
            .catch(error => console.error('Error fetching data:', error));

        setTimeout(updateTeams, 1500);
    }

    // ===== ساخت المان تیم =====
    function createTeamElement(team, index) {
        const teamDiv = document.createElement('div');
        teamDiv.className = 'teams Active';
        teamDiv.dataset.teamName = team.name;

        let aliveIconsHTML = '';
        if (team.alive == 0) {
            aliveIconsHTML = '<img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="">';
        } else {
            for (let i = 0; i < team.alive; i++) {
                aliveIconsHTML += `<img src="${aliveIcon}" alt="">`;
            }
        }

        teamDiv.innerHTML = `
            <div class="rank">${index + 1}</div>
            <div class="logo">
                <img src="${team.logo ? team.logo : 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='}" alt="">
                <div class="pos-color" style="background:${team.poscolor}!important; box-shadow: 0px 0px 14px 1px ${team.poscolor}!important;"></div>
            </div>
            <div class="name animated">
                <span>${team.name}</span>
            </div>
            <div class="alive">${aliveIconsHTML}</div>
            <div class="kills">${team.kills}</div>
            <div class="plc">${team.plc}</div>
            <div class="total">${team.total}</div>
        `;

        return teamDiv;
    }

    // شروع
    updateTeams();
</script>
</body>
</html>
<?php
// ---------- پایان کدهای اصلی ----------
?>
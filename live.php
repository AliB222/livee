<?php
/**
 * صفحه نمایش اسکوربرد زنده
 */
require_once( dirname(__FILE__) . '/../../../wp-load.php' );
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
        #displayBox { display: flex; flex-direction: column; gap: 10px; }
        .team-info-box { border: 1px solid #ccc; padding: 10px; border-radius: 5px; background-color: #f9f9f9; }
        .team-info-box img { max-width: 100px; max-height: 100px; margin-bottom: 5px; }
        .teams { transition: transform 0.5s ease, opacity 0.5s ease; }
        .moving { transform: translateY(0); }
        .hidden { opacity: 0; transform: translateY(-100%); }
        .killed-notification {
            position: absolute; top: 0; right: 0; height: 100%; width: 80%;
            opacity: 0; background-color:#ab1818; color: white; text-align: center;
            padding: 5px; font-weight: bold; overflow: hidden; white-space: nowrap;
            font-size: 25px; letter-spacing: 4px;
        }
        .killed-notification.show { opacity:1; }
    </style>
</head>
<body>
    <div id="liveBox">
        <div id="info"></div>
    </div>

    <script>
        const apiUrl = 'http://localhost/livepoint/wp-content/plugins/livePoint/api.php';
        const aliveIcon = 'http://localhost/livepoint/wp-content/uploads/2023/11/Asset-1.svg';

        function updateTeams() {
            fetch(apiUrl + '?_=' + Date.now())
                .then(response => response.json())
                .then(data => {
                    let teams = data.teams || [];
                    const container = document.getElementById('info');
                    if (!container) return;
                    container.innerHTML = '';

                    teams.forEach((team, index) => {
                        const teamDiv = document.createElement('div');
                        
                        // ===== دریافت کلاس شرطی =====
                        const teamClass = team.class || '';
                        
                        // ===== اضافه کردن کلاس به کارت =====
                        teamDiv.className = 'teams Active ' + teamClass;

                        // ===== ساخت آیکون‌های Alive =====
                        let aliveIconsHTML = '';
                        if (team.alive == 0) {
                            aliveIconsHTML = '<img src="data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=" alt="">';
                        } else {
                            for (let i = 0; i < team.alive; i++) {
                                aliveIconsHTML += `<img src="${aliveIcon}" alt="">`;
                            }
                        }

                        // ===== ساخت کارت تیم =====
                        teamDiv.innerHTML = `
                            <div class="rank">${index + 1}</div>
                            <div class="logo">
                                <img src="${team.logo ? team.logo : 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs='}" alt="">
                                <div class="pos-color" style="background:${team.poscolor}!important; box-shadow: 0px 0px 14px 1px ${team.poscolor}!important;"></div>
                            </div>
                            <div class="name animated"><span>${team.name}</span></div>
                            <div class="alive">${aliveIconsHTML}</div>
                            <div class="kills">${team.kills}</div>
                            <div class="plc">${team.plc}</div>
                            <div class="total">${team.total}</div>
                        `;

                        container.appendChild(teamDiv);
                    });
                })
                .catch(error => console.error('❌ خطا در دریافت داده:', error));

            setTimeout(updateTeams, 1500);
        }

        // شروع
        updateTeams();
    </script>
</body>
</html>
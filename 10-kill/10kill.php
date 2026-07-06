<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas%20Neue"/>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>10 Kills</title>
    <style>
        div#box {
            display: none;
            width: 300px;
            height: 100px;
            margin: 150px auto;
            position: relative;
            border-radius: 5px;
        }
        .teams {
            background: #ebebeb;
            border-radius: 5px;
            height: 110px;
        }
        div#box .bg {
            width: 300px;
            position: absolute;
            top: -135px;
            left: 0px;
        }
        div#box p {
            font-family: Bebas Neue;
            text-align: center;
            font-size: 40px;
            margin: 5px;
            height: 35px;
        }
        img.team-logo {
            position: absolute;
            left: -25px;
            top: 25px;
            padding: 5px;
            border-radius: 0px 20px;
            height: 50px;
        }
        .kills {
            font-size: 50px !important;
        }
    </style>
</head>
<body>
    <div id="box"></div>

    <script>
        // ===== تنظیمات =====
        const apiUrl = '/livepoint/wp-content/plugins/livePoint/api.php';
        const targetElement = document.getElementById("box");
        const displayDuration = 3000; // ۳ ثانیه نمایش
        const checkInterval = 1000; // هر ۱ ثانیه چک کن
        const STORAGE_KEY = '10kill_teams';

        // ===== دریافت شماره مچ از URL =====
        const urlParams = new URLSearchParams(window.location.search);
        const match = urlParams.get('match') || '1';
        const kmKey = `km${match}`; // مثلاً km1, km2, ...

        let teamQueue = [];
        let isDisplaying = false;

        // ===== بازیابی تیم‌های نمایش داده شده از localStorage =====
        let displayedTeamIds = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

        // ===== تابع نمایش تیم بعدی =====
        function displayNextTeam() {
            if (teamQueue.length > 0 && !isDisplaying) {
                isDisplaying = true;
                const team = teamQueue.shift();

                const teamHTML = `
                    <div class="teams">
                        <img class="bg" src="bg.png" class="animate__animated animate__fadeIn">
                        <img style="background:${team.poscolor}" class="team-logo animate__animated animate__fadeIn" width="50px" src="${team.logo}" alt="${team.name}">
                        <p class="team-name animate__animated animate__fadeIn">${team.name}</p>
                        <p class="kills animate__animated animate__fadeIn">${team[kmKey]} kills</p>
                    </div>
                `;

                targetElement.innerHTML = teamHTML;
                targetElement.style.display = "block";

                setTimeout(() => {
                    targetElement.style.display = "none";
                    isDisplaying = false;
                    displayNextTeam();
                }, displayDuration);
            }
        }

        // ===== تابع اصلی چک کردن تیم‌های جدید =====
        function checkForNewTeams() {
            fetch(apiUrl + '?_=' + Date.now())
                .then(response => response.json())
                .then(data => {
                    const teams = data.teams || [];

                    // ===== فیلتر تیم‌هایی که در این مچ به ۱۰ کشته رسیده‌اند =====
                    const killTeams = teams.filter(team => {
                        const kmValue = parseInt(team[kmKey]) || 0;
                        return kmValue > 9;
                    });

                    // ===== کلیدهای ترکیبی جدید =====
                    const currentKeys = killTeams.map(t => `${t.name}_${parseInt(t[kmKey])}`);

                    // ===== حذف کلیدهایی که دیگر ۱۰ کشته ندارند =====
                    const newDisplayed = displayedTeamIds.filter(key => currentKeys.includes(key));

                    let changed = false;
                    if (newDisplayed.length !== displayedTeamIds.length) {
                        displayedTeamIds = newDisplayed;
                        changed = true;
                    }

                    // ===== اضافه کردن تیم‌های جدید =====
                    killTeams.forEach(team => {
                        const key = `${team.name}_${parseInt(team[kmKey])}`;
                        if (!displayedTeamIds.includes(key)) {
                            displayedTeamIds.push(key);
                            teamQueue.push(team);
                            changed = true;
                        }
                    });

                    if (changed) {
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(displayedTeamIds));
                    }

                    displayNextTeam();
                })
                .catch(error => {
                    console.error('❌ خطا در دریافت داده:', error);
                });
        }

        // ===== شروع =====
        setInterval(checkForNewTeams, checkInterval);
        checkForNewTeams();
    </script>
</body>
</html>
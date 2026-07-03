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
    <title>Eliminate</title>
    <style>
        div#teams {
            width: 300px;
            margin: 50px auto;
            position: relative;
        }
        .teams {
            display: flex;
            flex-wrap: nowrap;
            justify-content: center;
            background: #ebebeb;
            flex-direction: column;
            padding: 0px;
            height: 90px;
            align-items: center;
        }
        img.team-logo.animate__animated.animate__fadeIn {
            position: absolute;
            left: -35px;
            top: 5px;
            width: 70px;
        }
        p.team-name {
            font-family: Bebas Neue;
            font-size: 25px;
            margin: 0px 30px;
        }
        .rank {
            position: absolute;
            right: 0px;
            top: -1px;
            padding: 5px;
            border-radius: 0px 5px 0px 20px;
            color: white;
            font-family: Bebas Neue;
            font-size: 25px;
        }
    </style>
</head>
<body>

<div id="teams"></div>

<script>
    const apiUrl = 'http://localhost/livepoint/wp-content/plugins/livePoint/api.php';
    const targetElement = document.getElementById("teams");
    const displayDuration = 3000;
    const checkInterval = 1000;
    const STORAGE_KEY = 'eliminated_teams';

    let teamQueue = [];
    let isDisplaying = false;
    let displayedTeamIds = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

    function displayNextTeam() {
        if (teamQueue.length > 0 && !isDisplaying) {
            isDisplaying = true;
            const team = teamQueue.shift();

            targetElement.innerHTML = `
                <div class="teams">
                    <div style="background:${team.poscolor};" class="rank">#${team.rank}</div>
                    <img class="team-logo animate__animated animate__fadeIn" width="50px" src="${team.logo}" alt="${team.name}">
                    <p class="team-name animate__animated animate__fadeIn">${team.name}'s eliminated</p>
                    <p class="team-name animate__animated animate__fadeIn">${team.kills} Kills</p>
                </div>
            `;
            targetElement.style.display = "block";

            setTimeout(() => {
                targetElement.innerHTML = '';
                isDisplaying = false;
                displayNextTeam();
            }, displayDuration);
        }
    }

    function checkForNewTeams() {
        fetch(apiUrl)
            .then(response => response.json())
            .then(data => {
                const teams = data.teams || [];

                // ===== ۱. تیم‌های زنده را چک کن: اگر قبلاً در لیست بودند، حذفشان کن =====
                const aliveTeams = teams.filter(team => team.alive > 0);
                let changed = false;

                aliveTeams.forEach(team => {
                    const index = displayedTeamIds.indexOf(team.name);
                    if (index !== -1) {
                        displayedTeamIds.splice(index, 1);
                        changed = true;
                    }
                });

                if (changed) {
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(displayedTeamIds));
                }

                // ===== ۲. تیم‌های مرده جدید را اضافه کن =====
                const deadTeams = teams.filter(team => team.alive < 1);

                deadTeams.forEach(team => {
                    if (!displayedTeamIds.includes(team.name)) {
                        displayedTeamIds.push(team.name);
                        teamQueue.push(team);
                        localStorage.setItem(STORAGE_KEY, JSON.stringify(displayedTeamIds));
                    }
                });

                displayNextTeam();
            })
            .catch(error => {
                targetElement.style.display = "none";
                console.error('خطا در دریافت داده:', error);
            });
    }

    setInterval(checkForNewTeams, checkInterval);
    checkForNewTeams();
</script>

</body>
</html>
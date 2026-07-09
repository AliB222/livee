<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Eliminate Alert - Apex Broadcast</title>
   
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Mono:wght@400;700&family=Archivo+Narrow:wght@400;700&display=swap" rel="stylesheet"/>
   
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: transparent;
            font-family: 'Archivo Narrow', sans-serif;
            overflow: hidden;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding-top: 80px;
        }

        .eliminate-broadcast {
            width: 390px;
            background: rgba(18, 20, 20, 0.88);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(225, 236, 0, 0.2);
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.7);
        }

        /* Top Glow Bar */
        .eliminate-broadcast::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #ff3b30, #e1ec00, #ff3b30);
            z-index: 2;
            box-shadow: 0 0 15px #ff3b30;
        }

        /* Shimmer */
        .eliminate-broadcast::after {
            content: "";
            position: absolute;
            top: 0;
            left: -150%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.15), transparent);
            animation: shimmer 5s infinite linear;
            z-index: 1;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(400%); }
        }

        /* Header */
        .alert-header {
            background: rgba(30, 32, 32, 0.9);
            padding: 8px 16px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid rgba(225, 236, 0, 0.25);
        }

        .live-badge {
            background: #e1ec00;
            color: #111;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 15px;
            padding: 2px 14px;
            letter-spacing: 2px;
            font-weight: 700;
        }

        .eliminated-title {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 28px;
            color: #ff3b30;
            letter-spacing: 3px;
            text-shadow: 
                0 0 10px #ff3b30,
                0 0 25px #ff3b30;
            animation: title-glow 1.5s ease-in-out infinite alternate;
        }

        @keyframes title-glow {
            from { text-shadow: 0 0 10px #ff3b30; }
            to { text-shadow: 0 0 30px #ff3b30, 0 0 50px rgba(255, 59, 48, 0.6); }
        }

        /* Main Card */
        .eliminate-main {
            padding: 18px 20px 16px;
            display: grid;
            grid-template-columns: 48px 1fr 72px;
            gap: 14px;
            align-items: center;
            animation: card-enter 0.7s cubic-bezier(0.23, 1, 0.32, 1) forwards;
        }

        @keyframes card-enter {
            from {
                opacity: 0;
                transform: translateY(-30px) scale(0.85);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .rank-big {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 42px;
            line-height: 1;
            color: #c8c8ab;
            text-align: center;
            opacity: 0.9;
        }

        .team-info {
            min-width: 0;
        }

        .team-name-elim {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 26px;
            color: #fff;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .team-logo-elim {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .team-logo-elim img {
            max-height: 46px;
            max-width: 46px;
            object-fit: contain;
            filter: drop-shadow(0 0 12px rgba(255, 59, 48, 0.5));
        }

        .kills-box {
            text-align: center;
            background: rgba(30, 32, 32, 0.7);
            padding: 8px 6px;
            border-radius: 3px;
            border: 1px solid rgba(225, 236, 0, 0.2);
        }

        .kills-number {
            font-family: 'Space Mono', monospace;
            font-size: 26px;
            font-weight: 700;
            color: #e1ec00;
        }

        .kills-label {
            font-size: 9px;
            color: #c8c8ab;
            letter-spacing: 1px;
        }

        /* Footer Timer */
        .footer-bar {
            height: 4px;
            background: rgba(255, 59, 48, 0.15);
            position: relative;
        }

        .progress-bar {
            height: 100%;
            background: linear-gradient(90deg, #ff3b30, #e1ec00);
            animation: progress 3.4s linear forwards;
        }

        @keyframes progress {
            from { width: 100%; }
            to { width: 0%; }
        }

        .hidden { display: none !important; }
    </style>
</head>
<body>

<div id="eliminate-broadcast" class="eliminate-broadcast hidden">
    <div class="alert-header">
        <div class="live-badge">LIVE</div>
        <div class="eliminated-title" id="elim-title">ELIMINATED</div>
        <div id="match-info" style="margin-left:auto; color:#c8c8ab; font-family:'Space Mono',monospace; font-size:11px;">MATCH 1</div>
    </div>

    <div id="eliminate-main" class="eliminate-main">
        <!-- JavaScript injects here -->
    </div>

    <div class="footer-bar">
        <div class="progress-bar"></div>
    </div>
</div>

<script>
const apiUrl = '/livepoint/wp-content/plugins/livePoint/api.php';
const STORAGE_KEY = 'eliminated_teams';
const DISPLAY_DURATION = 3400;

let teamQueue = [];
let isDisplaying = false;
let displayedTeamIds = JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];

const container = document.getElementById('eliminate-broadcast');
const mainContent = document.getElementById('eliminate-main');
const matchInfoEl = document.getElementById('match-info');

function showElimination(team) {
    if (isDisplaying) return;
    isDisplaying = true;

    matchInfoEl.textContent = team.match_info || 'MATCH 1';

    const logoUrl = team.logo || 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';

    mainContent.innerHTML = `
        <div class="rank-big">#${team.rank || '?'}</div>
        <div class="team-info">
            <div class="team-logo-elim">
                <img src="${logoUrl}" alt="${team.name}">
            </div>
            <div class="team-name-elim">${team.name || 'UNKNOWN TEAM'}</div>
        </div>
        <div class="kills-box">
            <div class="kills-number">${team.kills || 0}</div>
            <div class="kills-label">KILLS</div>
        </div>
    `;

    container.classList.remove('hidden');

    // Reset progress bar
    const progress = container.querySelector('.progress-bar');
    const newProgress = progress.cloneNode(true);
    progress.parentNode.replaceChild(newProgress, progress);

    setTimeout(() => {
        container.classList.add('hidden');
        isDisplaying = false;
        
        if (teamQueue.length > 0) {
            setTimeout(() => showElimination(teamQueue.shift()), 300);
        }
    }, DISPLAY_DURATION);
}

function checkForNewEliminations() {
    fetch(apiUrl + '?_=' + Date.now())
        .then(res => res.json())
        .then(data => {
            const teams = data.teams || [];
            const general = data.general || {};

            const sortedTeams = [...teams].sort((a, b) => {
                if (b.total !== a.total) return b.total - a.total;
                if (b.win !== a.win) return b.win - a.win;
                if (b.plc !== a.plc) return b.plc - a.plc;
                return b.kills - a.kills;
            });

            const teamsWithRank = teams.map(t => ({
                ...t,
                rank: sortedTeams.findIndex(s => s.name === t.name) + 1,
                match_info: general.match_info || 'MATCH 1'
            }));

            // Add new dead teams
            teamsWithRank.filter(t => t.alive < 1).forEach(team => {
                if (!displayedTeamIds.includes(team.name)) {
                    displayedTeamIds.push(team.name);
                    teamQueue.push(team);
                    localStorage.setItem(STORAGE_KEY, JSON.stringify(displayedTeamIds));
                }
            });

            // Clean revived teams
            teamsWithRank.filter(t => t.alive > 0).forEach(team => {
                const idx = displayedTeamIds.indexOf(team.name);
                if (idx > -1) displayedTeamIds.splice(idx, 1);
            });

            if (teamQueue.length > 0 && !isDisplaying) {
                showElimination(teamQueue.shift());
            }
        })
        .catch(err => console.error(err));
}

setInterval(checkForNewEliminations, 900);
checkForNewEliminations();
</script>

</body>
</html>
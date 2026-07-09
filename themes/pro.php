<?php
/**
 * پوسته حرفه‌ای Apex Broadcast - با رده‌بندی ثابت بر اساس مجموع کل کشته‌ها
 */
require_once( dirname(__FILE__) . '/../../../../wp-load.php' );
?>
<!DOCTYPE html>
<html class="dark" lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Apex Broadcast - Live Standings</title>
   
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Space+Mono:wght@400;700&family=Archivo+Narrow:wght@400;700&display=swap" rel="stylesheet"/>
   
    <style>
        /* ===== همه استایل‌ها مانند نسخه قبل ===== */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background-color: transparent !important;
            font-family: 'Archivo Narrow', sans-serif;
            color: #e2e2e2;
            overflow: hidden;
        }
        :root {
            --primary-fixed: #e1ec00;
            --on-surface: #e2e2e2;
            --on-surface-variant: #c8c8ab;
        }
        .broadcast-container {
            width: 390px;
            margin: 0 auto;
            padding: 12px 14px;
            background: rgba(18, 20, 20, 0.75);
            backdrop-filter: blur(6px);
            -webkit-backdrop-filter: blur(6px);
            border: 1px solid rgba(225, 236, 0, 0.15);
            position: relative;
            overflow: hidden;
        }
        .broadcast-container::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.15), transparent);
            animation: shimmer-sweep 8s infinite linear;
            pointer-events: none;
            z-index: 1;
        }
        @keyframes shimmer-sweep {
            0% { transform: translateX(-100%) skewX(-20deg); }
            20% { transform: translateX(200%) skewX(-20deg); }
            100% { transform: translateX(200%) skewX(-20deg); }
        }
        .broadcast-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #e1ec00, transparent);
            opacity: 0.6;
            z-index: 0;
        }
        .header-apex {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding-bottom: 4px;
            border-bottom: 1px solid rgba(225, 236, 0, 0.2);
            margin-bottom: 6px;
            position: relative;
            z-index: 2;
            gap: 8px;
        }
        .logo-box {
            width: 44px;
            height: 44px;
            background: #e1ec00;
            display: flex;
            align-items: center;
            justify-content: center;
            clip-path: polygon(0 0, 100% 0, 90% 100%, 0 100%);
            overflow: hidden;
            flex-shrink: 0;
            padding: 4px;
        }
        .logo-box span {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 20px;
            color: #1b1d00;
            letter-spacing: 0.05em;
            font-weight: 700;
        }
        .header-title {
            text-align: right;
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            justify-content: flex-end;
        }
        .header-title .org-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 16px;
            font-weight: 700;
            color: #e1ec00;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            line-height: 1.2;
            margin-bottom: 2px;
        }
        .header-title .match-name {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 22px;
            color: #ffffff;
            letter-spacing: 0.02em;
            line-height: 1;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-header-apex {
            display: grid;
            grid-template-columns: 34px 42px 1fr 50px 44px 44px;
            gap: 4px;
            padding: 5px 6px;
            background: rgba(30, 32, 32, 0.7);
            border-bottom: 1px solid rgba(225, 236, 0, 0.25);
            margin-bottom: 2px;
            position: relative;
            z-index: 2;
        }
        .table-header-apex div {
            font-family: 'Space Mono', monospace;
            font-size: 9px;
            font-weight: 700;
            color: #c8c8ab;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            text-align: center;
        }
        .table-header-apex div:nth-child(2) { /* خالی */ }
        .table-header-apex div:nth-child(3) { text-align: left; padding-left: 2px; }
        #scoreboard-rows {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-height: 150px;
            position: relative;
            z-index: 2;
        }

        /* ===== استایل ردیف ===== */
        .row-apex {
            display: grid;
            grid-template-columns: 34px 42px 1fr 50px 44px 44px;
            gap: 4px;
            padding: 6px 6px;
            align-items: center;
            background: linear-gradient(90deg, rgba(18, 20, 20, 0.85) 0%, rgba(26, 28, 28, 0.7) 100%);
            border-left: 2px solid transparent;
            border-radius: 2px;
            transition: all 0.3s ease;
            min-height: 38px;
            position: relative;
            overflow: hidden;
        }
        .row-apex.entering {
            animation: slide-up-apex 0.35s ease forwards;
        }
        .row-apex:hover {
            background: linear-gradient(90deg, rgba(30, 32, 32, 0.95) 0%, rgba(40, 42, 42, 0.85) 100%);
            border-left-color: #e1ec00;
            box-shadow: 0 0 18px rgba(225, 236, 0, 0.08);
            z-index: 3;
        }

        /* ===== نوار زرد حذف ===== */
        .row-apex .eliminate-bar {
            position: absolute;
            top: 0;
            left: 0;
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #f1ff00, #ffe600, #f1ff00);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Bebas Neue', sans-serif;
            font-size: 24px;
            font-weight: 900;
            color: #000;
            letter-spacing: 6px;
            text-transform: uppercase;
            pointer-events: none;
            z-index: 10;
            white-space: nowrap;
            border-radius: 2px;
            text-shadow: 0 0 8px rgba(0,0,0,0.6), 0 0 20px #ffeb3b;
            box-shadow: 0 0 25px rgba(241, 255, 0, 0.85);
            overflow: hidden;
        }

        .row-apex.eliminated .eliminate-bar {
            animation: eliminate-slide 1.35s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }

        @keyframes eliminate-slide {
            0%   { width: 0%; opacity: 0; transform: scaleX(0.7) translateX(-25px); }
            30%  { opacity: 1; transform: scaleX(1) translateX(0); }
            100% { width: 100%; }
        }

        .row-apex.eliminated.fade-out .eliminate-bar {
            animation: eliminate-fade-out 0.65s ease forwards;
        }

        @keyframes eliminate-fade-out {
            to {
                opacity: 0;
                transform: translateY(-10px) scale(0.95);
            }
        }

        .rank-apex {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 22px;
            color: #c8c8ab;
            text-align: center;
            z-index: 2;
            position: relative;
        }
        .rank-apex.top1 { color: #ffffff; font-size: 26px; }
        .logo-apex { display: flex; justify-content: center; align-items: center; z-index: 2; position: relative; }
        .logo-apex img {
    width: 34px;           /* ← عرض ثابت */
    height: 24px;          /* ← ارتفاع ثابت */
    object-fit: cover;     /* ← لوگو را به‌طور کامل در کادر قرار می‌دهد */
    border-radius: 2px;
}
        .team-name-apex {
            font-family: 'Bebas Neue', sans-serif;
            font-size: 22px;
            color: #ffffff;
            letter-spacing: 0.02em;
            padding-left: 2px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            z-index: 2;
            position: relative;
        }
        .alive-bar-apex {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 3px;
            z-index: 2;
            position: relative;
        }
        .alive-dot {
            width: 5px;
            height: 18px;
            background-color: #e1ec00;
            border-radius: 1px;
            transition: background 0.3s ease;
            box-shadow: 0 0 8px #e1ec00, 0 0 16px rgba(225, 236, 0, 0.6);
        }
        .alive-dot.dead {
            background-color: rgba(225, 236, 0, 0.15);
            box-shadow: none;
        }
        .stat-apex {
            font-family: 'Space Mono', monospace;
            font-size: 18px;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
            font-variant-numeric: tabular-nums;
            min-width: 30px;
            display: inline-block;
            padding: 0 2px;
            z-index: 2;
            position: relative;
        }
        .stat-apex.kills { 
            color: #e1ec00;
            font-size: 18px;
        }

        .promotion-divider {
            width: 100%;
            height: 2px;
            background: #00c853;
            margin: 3px 0;
            box-shadow: 0 0 15px rgba(0, 200, 83, 0.8), 0 0 30px rgba(0, 200, 83, 0.4);
            flex-shrink: 0;
            border-radius: 2px;
            position: relative;
        }
        .promotion-divider::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: -4px;
            bottom: -4px;
            background: radial-gradient(ellipse at center, rgba(0,200,83,0.15) 0%, transparent 70%);
            pointer-events: none;
        }

        .ticker-apex {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 4px;
            padding-top: 3px;
            border-top: 1px solid rgba(225, 236, 0, 0.08);
            position: relative;
            z-index: 2;
        }
        .ticker-apex span {
            font-family: 'Space Mono', monospace;
            font-size: 7px;
            font-weight: 700;
            color: #c8c8ab;
            letter-spacing: 0.15em;
            text-transform: uppercase;
        }
        .live-dot {
            display: inline-block;
            width: 5px;
            height: 5px;
            background-color: #ff3b30;
            border-radius: 50%;
            animation: pulse-dot 1.2s ease-in-out infinite;
        }
        @keyframes pulse-dot {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.3; transform: scale(0.8); }
        }
        @keyframes slide-up-apex {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }
        ::-webkit-scrollbar { display: none; }
    </style>
</head>
<body>
<div class="broadcast-container">
    <div class="header-apex">
        <div class="logo-box"><span>LIVE</span></div>
        <div class="header-title">
            <span class="org-name" id="org-name">GRAND FINALS</span>
            <span class="match-name" id="match-name">MATCH 1</span>
        </div>
    </div>
    <div class="table-header-apex">
        <div>#</div><div></div><div>TEAM</div><div>ALIVE</div><div>PTS</div><div>KILLS</div>
    </div>
    <div id="scoreboard-rows"></div>
    <div class="ticker-apex">
        <span>Live standings updating every match</span>
        <span><span class="live-dot"></span> STREAM LIVE</span>
    </div>
</div>
<script>
const apiUrl = 'http://localhost/livepoint/wp-content/plugins/livePoint/api.php';
let isFirstLoad = true;
let previousRank = {};
let previousValues = {};
let previousAlive = {};

// ===== تابع انیمیشن شمارنده =====
function animateNumber(element, start, end, duration = 800) {
    const startTime = performance.now();
    function update(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const eased = 1 - Math.pow(1 - progress, 3);
        const current = Math.round(start + (end - start) * eased);
        element.textContent = current;
        if (progress < 1) requestAnimationFrame(update);
        else element.textContent = end;
    }
    requestAnimationFrame(update);
}

function renderTeams() {
    fetch(apiUrl + '?_=' + Date.now())
        .then(res => res.json())
        .then(data => {
            let teams = data.teams || [];
            const general = data.general || {};

            document.getElementById('org-name').textContent = general.org || 'GRAND FINALS';
            document.getElementById('match-name').textContent = general.match_info || 'MATCH 1';

            const promoted = parseInt(general.promoted_teams) || 0;

            // ===== اضافه کردن total_kills به تیم‌ها =====
            teams = teams.map(t => ({
                ...t,
                kills: parseInt(t.kills) || 0,
                total: parseInt(t.total) || 0,
                alive: parseInt(t.alive) || 0,
                win: parseInt(t.win) || 0,
                plc: parseInt(t.plc) || 0,
                total_kills: parseInt(t.total_kills) || 0 // ← مقدار مجموع کل کشته‌ها
            }));

            // ================================================================
            // ===== مرتب‌سازی با استفاده از total_kills به جای kills =====
            // ================================================================
            teams.sort((a, b) => {
                if (b.total !== a.total) return b.total - a.total;   // اولویت ۱: امتیاز کل
                if (b.win !== a.win) return b.win - a.win;         // اولویت ۲: تعداد بردها
                if (b.plc !== a.plc) return b.plc - a.plc;         // اولویت ۳: PLC
                return (b.total_kills || 0) - (a.total_kills || 0); // اولویت ۴: مجموع کل کشته‌ها
            });

            const container = document.getElementById('scoreboard-rows');
            const topTeams = teams.slice(0, 37);

            const newlyEliminated = {};
            topTeams.forEach((team) => {
                const prevAlive = previousAlive[team.name];
                if (prevAlive !== undefined && prevAlive > 0 && team.alive === 0) {
                    newlyEliminated[team.name] = true;
                }
            });

            let html = '';
            topTeams.forEach((team, index) => {
                const rankClass = index === 0 ? 'rank-apex top1' : 'rank-apex';
                const logoUrl = team.logo || 'data:image/gif;base64,R0lGODlhAQABAAD/ACwAAAAAAQABAAACADs=';

                let dots = '';
                for (let i = 0; i < 4; i++) {
                    const isDead = i >= team.alive;
                    dots += `<div class="alive-dot ${isDead ? 'dead' : ''}"></div>`;
                }

                let extraClass = '';
                if (isFirstLoad) {
                    extraClass = ' entering';
                }

                const isEliminated = newlyEliminated[team.name] || false;
                if (isEliminated) extraClass += ' eliminated';

                html += `
                    <div class="row-apex${extraClass}" data-team="${team.name}" style="animation-delay: ${0.04 * index}s">
                        ${isEliminated ? '<div class="eliminate-bar">ELIMINATED</div>' : ''}
                        <div class="${rankClass}">${index + 1}</div>
                        <div class="logo-apex"><img src="${logoUrl}" alt="${team.name}"></div>
                        <div class="team-name-apex">${team.name || '---'}</div>
                        <div class="alive-bar-apex">${dots}</div>
                        <div class="stat-apex" id="pts-${index}">${team.total}</div>
                        <div class="stat-apex kills" id="kills-${index}">${team.kills}</div>
                    </div>
                `;

                if (promoted > 0 && index === promoted - 1 && index < topTeams.length - 1) {
                    html += `<div class="promotion-divider"></div>`;
                }
            });

            container.innerHTML = html;

            // ===== حذف نوار پس از ۲.۶ ثانیه =====
            setTimeout(() => {
                document.querySelectorAll('.row-apex.eliminated').forEach(row => {
                    row.classList.add('fade-out');
                    setTimeout(() => {
                        row.classList.remove('eliminated', 'fade-out');
                    }, 700);
                });
            }, 2600);

            // ===== انیمیشن شمارنده =====
            topTeams.forEach((team, index) => {
                const ptsId = 'pts-' + index;
                const killsId = 'kills-' + index;
                const ptsEl = document.getElementById(ptsId);
                const killsEl = document.getElementById(killsId);

                const prevPts = previousValues[team.name + '-pts'] ?? 0;
                const prevKills = previousValues[team.name + '-kills'] ?? 0;
                const newPts = team.total;
                const newKills = team.kills;

                if (ptsEl) {
                    const isFirstTimePts = !previousValues.hasOwnProperty(team.name + '-pts');
                    const currentPts = parseInt(ptsEl.textContent) || 0;
                    if (currentPts !== prevPts || isFirstTimePts) {
                        ptsEl.textContent = prevPts;
                    }
                    if (prevPts !== newPts || isFirstTimePts) {
                        animateNumber(ptsEl, prevPts, newPts);
                    }
                }

                if (killsEl) {
                    const isFirstTimeKills = !previousValues.hasOwnProperty(team.name + '-kills');
                    const currentKills = parseInt(killsEl.textContent) || 0;
                    if (currentKills !== prevKills || isFirstTimeKills) {
                        killsEl.textContent = prevKills;
                    }
                    if (prevKills !== newKills || isFirstTimeKills) {
                        animateNumber(killsEl, prevKills, newKills);
                    }
                }

                previousValues[team.name + '-pts'] = newPts;
                previousValues[team.name + '-kills'] = newKills;
            });

            previousRank = {};
            previousAlive = {};
            topTeams.forEach((team, index) => {
                previousRank[team.name] = index;
                previousAlive[team.name] = team.alive;
            });

            if (isFirstLoad) {
                isFirstLoad = false;
                setTimeout(() => document.querySelectorAll('.row-apex.entering').forEach(el => el.classList.remove('entering')), 600);
            }

        })
        .catch(err => console.error('❌ خطا:', err));

    setTimeout(renderTeams, 2000);
}

renderTeams();
</script>
</body>
</html>
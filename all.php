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
body {
   font-family:Bebas Neue;
   font-size:30px;
}
p {
    display: flex;
    width: 400px;
    flex-direction: row;
    justify-content: space-between;
}
.info {
    margin: 50px auto !important;
    display: block;
    width: 400px !important;
    text-align:center;
}
.img {
    background: url(https://itsalib2.ir/wp-content/uploads/2024/08/frame-teams.png);
    background-repeat: no-repeat;
    width: 300px;
    height: 300px;
    display: flex;
    justify-content: center;
    align-items: center;
}
    </style>
  </head>
<body>

<div id="output"></div>

<script>
// آدرس API
const url = "https://itsalib2.ir/wp-content/plugins/livePoint/api.php";

// گرفتن پارامترهای URL
const urlParams = new URLSearchParams(window.location.search);
const pos = urlParams.get('pos');
const rank = urlParams.get('rank');
const color = urlParams.get('color') || 'black'; // پیش‌فرض به رنگ مشکی

// تابع برای دریافت داده‌ها از API
function fetchData() {
    // درخواست به API برای دریافت داده‌ها
    fetch(url)
        .then(response => response.json())
        .then(data => {
            let output = '';
            let teamFound = false;

            // اگر pos یا rank برابر با 'img' باشد، فقط عکس‌های تیم‌ها نمایش داده شود
            if (pos === 'img' || rank === 'img') {
                teamFound = true;
                 output += `<div class="wrap">`;
                data.teams.forEach(team => {
                    output += `<div class="img"><img src="${team.logo}" alt="Team Logo" style="width: 100px; height: auto; margin: 50px;"></div>`;
                });
                output += `</div>`;
            } 
            // اگر pos یا rank برابر با 'all' باشد، همه اطلاعات تیم‌ها نمایش داده شود
            else if (pos === 'all' || rank === 'all') {
                teamFound = true;
               
                data.teams.forEach(team => {
                    output += `
                        <div class="info" style="color: #${color}; margin-bottom: 20px;">
                        <img src="${team.logo}" alt="Team Logo" style="width: 100px; height: auto;"><br>
                            <p><strong>Name:</strong> ${team.name}</p>
                            <p><strong>Alive:</strong> ${team.alive}</p>
                            <p><strong>Kill:</strong> ${team.kills}</p>
                            <p><strong>Pos:</strong> ${team.pos}</p>
                            <p><strong>Rank:</strong> ${team.rank}</p>
                            <p><strong>Win:</strong> ${team.win}</p>
                            <br><br><hr>
                        </div>
                    `;
                });
            } else {
                // جستجوی تیم با pos یا rank مشخص شده
                data.teams.forEach(team => {
                    if ((pos && team.pos == pos) || (rank && team.rank == rank)) {
                        teamFound = true;
                        output = `
                            <div class="info" style="color: #${color};">
                            <img src="${team.logo}" alt="Team Logo" style="width: 100px; height: auto;"><br>
                                <p><strong>Name:</strong> ${team.name}</p>
                                <p><strong>Alive:</strong> ${team.alive}</p>
                                <p><strong>Kill:</strong> ${team.kills}</p>
                                <p><strong>Pos:</strong> ${team.pos}</p>
                                <p><strong>Rank:</strong> ${team.rank}</p>
                                <p><strong>Win:</strong> ${team.win}</p>
                                
                            </div>
                        `;
                    }
                });
            }

            // اگر تیمی پیدا نشد
            if (!teamFound) {
                output = "تیمی با pos یا rank مشخص شده یافت نشد.";
            }

            // نمایش خروجی
            document.getElementById('output').innerHTML = output;
        })
        .catch(error => {
            document.getElementById('output').innerText = "خطا در درخواست به API.";
            console.error("Error fetching data: ", error);
        });
}

// فراخوانی تابع fetchData هر 5 ثانیه
setInterval(fetchData, 3000);

// اجرای اولین فراخوانی به صورت فوری
fetchData();
</script>


</body>
</html>

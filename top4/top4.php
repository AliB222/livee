<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas%20Neue"/>
    <title>TOP۴ TEAMS</title>
    <style>
        .logo {
            margin: 10px 0px;
        }
        .logo img {
            filter: drop-shadow(0px 0px 6px #00000090);
            width: 80px;
            height: 80px;
        }
        .logo {
            font-family: Bebas Neue;
            font-size: 30px;
            font-weight: bold;
            width: 25%;
            float: left;
            text-align: center;
        }
        div#liveBox {
            background: #67676700;
            width: 25%;
            height: 130px;
            background-image: url(/livepoint/wp-content/plugins/livePoint/top4/black%20smoke.png);
            background-repeat: no-repeat;
            background-size: cover;
            background-position: center;
            padding: 20px;
            margin: 50px auto;
        }
        div#live {
            width: 100%;
            height: 130px;
        }
        h1 {
            font-family: Bebas Neue;
            font-size: 50px;
            font-weight: bold;
            color: white;
            padding: 0px;
            margin: 0px;
            text-align: center;
            border-bottom: 1px solid white;
            text-shadow: 0px 0px 20px black;
        }
        .teams {
            display: flex;
            align-content: center;
            align-items: center;
            margin: 20px;
        }
        .teams * {
            padding: 0px 5px;
            width: 95px;
        }
        div#team {
            display: flex;
            flex-direction: row;
            flex-wrap: nowrap;
            align-content: center;
            justify-content: center;
            align-items: flex-start;
        }
        .teams>img {
            width: 40px;
            height: 40px;
        }
        .alive {
            height: 40px;
            line-height: 45px;
            position: relative;
            background: #e3e3e3;
            width: 135px;
        }
        .alive img {
            width: 23px;
        }
        span {
            color: white;
            font-family: 'Bebas Neue';
            font-size: 25px;
            text-align: center;
        }
        .teams>img {
            width: 30px;
            height: 30px;
            position: relative;
            transform: scale(1.8);
            margin: 0px 10px;
            filter: drop-shadow(2px 4px 6px #00000057);
        }
    </style>
</head>
<body>
<?php
// دریافت داده‌ها از دیتابیس جدید
$teams_data = get_option('lp_teams', []);
$alive_icon = 'http://localhost/livepoint/wp-content/uploads/2023/11/Asset-1.svg';

$teams_array = [];
$pos = 0;
foreach ($teams_data as $t) {
    $pos++;
    $kills = intval($t['km1'] ?? 0) + intval($t['km2'] ?? 0) + 
             intval($t['km3'] ?? 0) + intval($t['km4'] ?? 0) + intval($t['km5'] ?? 0);
    $total = $kills + intval($t['plc'] ?? 0) + intval($t['bonus'] ?? 0);
    
    if (intval($t['alive'] ?? 0) > 0) {
        $logo_url = '';
        if (!empty($t['logo_id'])) {
            $img = wp_get_attachment_image_src($t['logo_id'], 'medium');
            if ($img) $logo_url = $img[0];
        }
        
        $teams_array[] = [
            'name' => $t['name'] ?? 'بدون نام',
            'logo' => $logo_url,
            'alive' => intval($t['alive'] ?? 0),
            'kills' => $kills,
            'plc' => intval($t['plc'] ?? 0),
            'total' => $total,
            'win' => intval($t['win'] ?? 0),
            'pos' => $pos,
            'pos-color' => $t['color'] ?? '#ff9800'
        ];
    }
}

usort($teams_array, function ($a, $b) {
    if ($a['total'] != $b['total']) return $b['total'] - $a['total'];
    if ($a['win'] != $b['win']) return $b['win'] - $a['win'];
    if ($a['plc'] != $b['plc']) return $b['plc'] - $a['plc'];
    return $b['kills'] - $a['kills'];
});
?>
<div id="team">
    <?php if (count($teams_array) <= 4): ?>
        <?php foreach ($teams_array as $num => $team_data): ?>
            <div class="teams" style="background:<?php echo $team_data['pos-color']; ?>">
                <img width="150px" src="<?php echo esc_url($team_data['logo']); ?>" alt="">
                <span><?php echo esc_html($team_data['name']); ?></span>
                <div class="alive">
                    <?php if ($team_data['alive'] == 0): ?>
                        <img src="http://localhost/livepoint/wp-content/plugins/livePoint/img/helmet2.svg" alt="">
                    <?php else: ?>
                        <?php for ($i = 1; $i <= $team_data['alive']; $i++): ?>
                            <img src="<?php echo esc_url($alive_icon); ?>" alt="">
                        <?php endfor; ?>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<div id="live">
    <?php if (count($teams_array) <= 4): ?>
        <div id="liveBox" class="">
            <h1>TOP 4</h1>
            <?php foreach ($teams_array as $num => $team_data): ?>
                <div class="logo">
                    <img width="150px" src="<?php echo esc_url($team_data['logo']); ?>" alt="">
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<script src="../wp-content/plugins/livePoint/top4/top4.js"></script>
<script src="../wp-content/plugins/livePoint/top4/top4-box.js"></script>
</body>
</html>
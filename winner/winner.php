<?php
require_once( dirname(__FILE__) . '/../../../../wp-load.php' );

$alive_icon = get_field("alive-icon", 'options');

$teams_array = array();
$pos = 0;
while (have_rows('teams', 'option')) : the_row();
    $team_name = get_sub_field('team_name');
    $team_logo = get_sub_field('team_logo');
    $alive = get_sub_field('alive');
    $KM1 = get_sub_field('KM1');
    $KM2 = get_sub_field('KM2');
    $KM3 = get_sub_field('KM3');
    $KM4 = get_sub_field('KM4');
    $PLC = get_sub_field('PLC');
    $win = get_sub_field('win');
    $kills = $KM1 + $KM2 + $KM3 + $KM4;
    $total = $kills + $PLC;
    $pos_color = get_sub_field('pos-color');
    $pos++;

    $team_data = array(
        'name' => $team_name,
        'logo' => $team_logo,
        'alive' => $alive,
        'kills' => $kills,
        'plc' => $PLC,
        'total' => $total,
        'win' => $win,
        'pos' => $pos,
        'pos-color' => $pos_color
    );
    if ($alive > 0){
        $teams_array[] = $team_data;
    }
endwhile;

usort($teams_array, function ($a, $b) {
    if ($a['total'] != $b['total']) {
        return $b['total'] - $a['total'];
    } elseif ($a['win'] != $b['win']) {
        return $b['win'] - $a['win'];
    } elseif ($a['plc'] != $b['plc']) {
        return $b['plc'] - $a['plc'];
    } else {
        return $b['kills'] - $a['kills'];
    }
});

$count = 0;
while (have_rows('teams', 'option')) : the_row();
    $count++;
endwhile;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas%20Neue"/>
    <title>Winner</title>
    <style>
        #team {
            display: flex;
            flex-wrap: nowrap;
            flex-direction: column;
            align-items: center;
            height: 200px;
            margin: auto;
            margin-top: 10%;
            position: relative;
        }
        p {
            font-family: Bebas Neue;
            font-size: 150px;
            margin: 100px 0px;
            color: #e2ad21;
        }
        @keyframes myAnim {
            0% { transform: scale(5); }
            100% { transform: scale(1); }
        }
        .count{
            text-align: right;
            color: #e2ad21;
            padding: 20px;
        }
        #win {
            color: #e2ad21;
            text-align: center;
        }
        #win p {
            font-size: 120px;
        }
    </style>
</head>
<body>

    <div class="count">
        <p>#1/<?php echo $count; ?></p>
    </div>

    <div id="team"></div>

    <script src="../wp-content/plugins/livePoint/winner/winner.js"></script>

</body>
</html>
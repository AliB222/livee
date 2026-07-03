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
  </head>
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
    background-image: url(https://itsalib2.ir/wp-content/plugins/livePoint/top4/black%20smoke.png);
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
<?php
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
?>
<div id="team">
    <?php
    // چک کردن تعداد تیم‌ها
    if (count($teams_array) <= 4) {
        foreach ($teams_array as $num => $team_data) :
    ?>
            <div class="teams " <?php echo 'style="background:' . $team_data['pos-color']; ?>">
                <img width="150px" src="<?php echo $team_data['logo']; ?>" alt="">
                <span><?php echo $team_data['name']; ?></span>
                <div class="alive">
                    <?php
                    if ($team_data['alive'] == 0) {
                        echo '<img src="https://itsalib2.ir/wp-content/uploads/2023/11/white-helmet.svg" alt="">';
                    } else {
                        for ($i = 1; $i <= $team_data['alive']; $i++) {
                            echo '<img src="' . $alive_icon . '" alt="">';
                        }
                    }
                    ?>
                </div>
            </div>
    <?php
        endforeach;
    }
    ?>
</div>
    <div id="live">
<?php if (count($teams_array) <= 4) { ?>

    <div id="liveBox" class="">
    <h1>TOP 4</h1>
<?php
if (count($teams_array) <= 4) {
 foreach ($teams_array as $num => $team_data) :
       ?>
       <div class="logo">
       <img width="150px" src="<?php echo $team_data['logo']; ?>" alt="">
       </div>
       <?php
       if ($num > 2) break;
  endforeach;
}
}
?>
</div>
</div>
<script src="../wp-content/plugins/livePoint/top4/top4.js"></script>
<script src="../wp-content/plugins/livePoint/top4/top4-box.js"></script> 
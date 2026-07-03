<?php
$color = get_field("top3-color", 'options');
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas%20Neue"/>
    <title>TOP3 TEAM LOGO</title>
  </head>
  <style>
.logo {
    width:200px;
    text-align: center;
    border-radius: 20px;
    padding: 15px;
    margin: 10px;
}
.logo img {
    filter: drop-shadow(0px 0px 3px #00000073);
    width: 150px;
    height: 150px;
}
div#liveBox {
    display: flex;
    flex-wrap: nowrap;
    align-content: center;
    justify-content: flex-start;
    align-items: center;
}
.logo {
    font-family: Bebas Neue;
    font-size: 30px;
    font-weight: bold;
    height: 450px;
}
  </style>
<?php
$teams_array = array();
$pos = 0;
$max_kills_team = null;
$max_kills = 0;
$max_plc_team = null;
$max_plc = 0;

while (have_rows('teams', 'option')) : the_row();
    $team_name = get_sub_field('team_name');
    $team_logo = get_sub_field('team_logo');
    $KM1 = get_sub_field('KM1');
    $KM2 = get_sub_field('KM2');
    $KM3 = get_sub_field('KM3');
    $KM4 = get_sub_field('KM4');
    $PLC = get_sub_field('PLC');
    $win = get_sub_field('win');
    $kills = $KM1 + $KM2 + $KM3 + $KM4;
    $total = $kills + $PLC;
    $pos++;

    $team_data = array(
        'name' => $team_name,
        'logo' => $team_logo,
        'kills' => $kills,
        'plc' => $PLC,
        'total' => $total,
        'win' => $win,
        'pos' => $pos,
    );
    $teams_array[] = $team_data;

    if ($kills > $max_kills) {
        $max_kills = $kills;
        $max_kills_team = $team_data;
    }
    if ($PLC > $max_plc) {
        $max_plc = $PLC;
        $max_plc_team = $team_data;
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

<div id="liveBox">
    <?php
    foreach ($teams_array as $num => $team_data) :
        ?>
        <div class="logo">
            <img width="150px" src="<?php echo $team_data['logo']; ?>" alt="">
             <p style="color:<?php echo $color; ?>" class="name"><?php echo $team_data['name']; ?></p>
            <p style="color:<?php echo $color; ?>" class="kills"><?php echo $team_data['kills']; ?></p>
            <p style="color:<?php echo $color; ?>" class="plc"><?php echo $team_data['plc']; ?></p>
            <p style="color:<?php echo $color; ?>" class="total"><?php echo $team_data['total']; ?></p>
        </div>

            <?php
        
        if ($num > 1) break;
    endforeach;
    foreach ($teams_array as $num => $team_data) :
        if ($num == 1) {
            // اگر تیم در دو تیم اول باشد، باکس نمایش داده شود
            ?>
            <div class="logo">
                <img width="150px" src="<?php echo $max_kills_team['logo']; ?>" alt="">
                <p style="color:<?php echo $color; ?>"><?php echo $max_kills_team['name']; ?></p>
                <p style="color:<?php echo $color; ?>"><?php echo $max_kills_team['kills']; ?> Kills</p>
            </div>
            <?php
        }
        if ($num > 1) break;
    endforeach;
        foreach ($teams_array as $num => $team_data) :
        if ($num == 1) {
            // اگر تیم در دو تیم اول باشد، باکس نمایش داده شود
            ?>
            <div class="logo">
                <img width="150px" src="<?php echo $max_plc_team['logo']; ?>" alt="">
                <p style="color:<?php echo $color; ?>"><?php echo $max_plc_team['name']; ?></p>
                <p style="color:<?php echo $color; ?>"><?php echo $max_plc_team['plc']; ?> PLC</p>
            </div>
            <?php
        }
        if ($num > 1) break;
    endforeach;
    ?>
</div>
<script src="../wp-content/plugins/livePoint/top3/top3.js"></script> 
<?php
$org = get_field("org", 'option');
$match_info = get_field("match_info", 'option');
$org_logo = get_field("org_logo", 'option');
$color_set = get_field("color_set", 'options');
$team_num_color = get_field("team-number", 'options');
$team_color = get_field("team-color", 'options');
$alive_icon = get_field("alive-icon", 'options');
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
    <link rel="stylesheet" href="../wp-content/plugins/livePoint/css/main.css" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Bebas%20Neue"/>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <title>LivePoint</title>
    <style>
#displayBox {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.team-info-box {
    border: 1px solid #ccc;
    padding: 10px;
    border-radius: 5px;
    background-color: #f9f9f9;
}

.team-info-box img {
    max-width: 100px;
    max-height: 100px;
    margin-bottom: 5px;
}
    <?php
        if ($color_set == "top3"){
            echo '
            #team1 .rank {background: linear-gradient(0deg, rgba(255,215,0,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}
            #team2 .rank {background: linear-gradient(0deg, rgba(192,192,192,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}
            #team3 .rank {background: linear-gradient(0deg, rgba(205,127,50,1) 0%, rgba(255,255,255,1) 84%, rgba(255,255,255,1) 100%);}';
        }else{
            for ($i = 1; $i <= $team_num_color; $i++) {
                //echo '#team' . $i . ' .rank {background:' . $team_color .'};';
                printf("#team%u .rank {background:%s} \n",$i,$team_color);
            }
        }
    ?>
    </style>
  </head>
  <body>
  <div id="displayBox">
</div>
    <div id="liveBox">
  <div id="info">
        <div class="infoImg">
          <img src="<?php echo $org_logo; ?>" alt="">
        </div>
        <div class="infoTxt">
            <p><?php echo $org; ?></p>
            <p><?php echo $match_info; ?></p>
        </div>
      </div>
      <div id="teamsHead">
        <div class="headText"><p>team</p></div>
        <div class="headText"><p>alive</p></div>
        <div class="headText"><p>kills</p></div>
        <div class="headText"><p>plc</p></div>
        <div class="headText"><p>total</p></div>
      </div>
    </div>
</div>
    <script src="../wp-content/plugins/livePoint/js/header.js"></script> 
  </body>
</html>

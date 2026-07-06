<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />
    <title>لوگوها</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { background:transparent; display:flex; justify-content:center; align-items:center; min-height:100vh; }
        #images-container { 
            display:flex; 
            flex-wrap:wrap; 
            justify-content:center; 
            align-items:center; 
            gap: 40px;  /* ← فاصله بیشتر بین لوگوها */
            padding: 20px;
        }
        #images-container img { 
            display:block; 
            max-width:200px; 
            height:auto; 
        }
    </style>
</head>
<body>
    <div id="images-container"></div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
    <script>
    $(document).ready(function() {
        function updateImages() {
            $.ajax({
                url: '../wp-content/plugins/livePoint/image-ajax.php?_=' + new Date().getTime(),
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    if (data.html) {
                        $('#images-container').html(data.html);
                    }
                },
                error: function() {
                    // خطا را نادیده بگیر
                }
            });
        }

        updateImages();

        let lastUpdate = localStorage.getItem('lp_match_logos_update') || '0';
        function checkForUpdates() {
            const currentUpdate = localStorage.getItem('lp_match_logos_update') || '0';
            if (currentUpdate !== lastUpdate) {
                lastUpdate = currentUpdate;
                updateImages();
            }
        }
        setInterval(checkForUpdates, 2000);
    });
    </script>
</body>
</html>
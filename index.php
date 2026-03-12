<?php require_once 'app/auth.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>PiVPN</title>
    <style>
        :root { --success: #28a745; --danger: #dc3545; }
        body { background: #f8f9fa; font-family: -apple-system, sans-serif; margin: 0; padding: 0; }
        .container { padding: 8px !important; }
        
        /* THE COMPACT TABLE */
        table { width: 100% !important; border-collapse: collapse !important; background: #fff; border-radius: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.1); }
        th { font-size: 10px; text-transform: uppercase; color: #888; padding: 10px 5px; border-bottom: 2px solid #eee; text-align: left; }
        td { padding: 6px 5px; border-bottom: 1px solid #f0f0f0; }

        /* TYPOGRAPHY */
        .client-name { font-size: 12px; font-weight: bold; color: #333; display: block; }
        small { font-size: 10px !important; display: block; color: #666; line-height: 1.2; }
        .total-usage { color: var(--success); font-weight: 500; }
        .last-seen { color: #007bff; font-size: 9px !important; }

        /* BUTTONS */
        .custom-btn-new { background: var(--success); color: #fff; border: none; padding: 4px 8px; font-size: 10px; border-radius: 4px; }
        .toggle_btn { width: 32px; height: 16px; background: #ccc; border-radius: 16px; position: relative; display: inline-block; cursor: pointer; transition: 0.3s; }
        .toggle_btn::after { content: ''; position: absolute; top: 1px; left: 1px; width: 14px; height: 14px; background: #fff; border-radius: 50%; transition: 0.3s; }
        .toggle_btn.active { background: #4cd964; }
        .toggle_btn.active::after { left: 17px; }
        .toggle_btn.inactive { background: var(--danger); }

        /* POPUP */
        #popup { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); z-index: 1000; align-items: center; justify-content: center; }
        .modal-content { background: #fff; width: 90%; max-width: 400px; padding: 20px; border-radius: 10px; }
        input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; font-size: 16px; box-sizing: border-box; }
        
        nav { background: #333; color: #fff; padding: 10px 15px; display: flex; justify-content: space-between; align-items: center; }
    </style>
</head>
<body>
    <nav>
        <div style="display:flex; align-items:center;">
            <img src="img/pivpnlogo64.png" width="24" style="margin-right:10px;">
            <h1 style="font-size:16px; margin:0;">PiVPN Dashboard</h1>
        </div>
        <button onclick="location.reload()" style="background:none; border:none; color:#fff; font-size:18px;">↻</button>
    </nav>

    <div class="container">
        <?php
        $script = __DIR__ . '/clients.sh';
        echo shell_exec("sh " . escapeshellarg($script) . " 2>&1");
        ?>
    </div>

    <div id="popup">
        <div class="modal-content">
            <h3 style="margin:0 0 10px 0;">New Client</h3>
            <form action="app/new.php" method="POST">
                <input type="text" name="name" placeholder="Name" required>
                <input type="number" name="days" value="365">
                <div style="display:flex; gap:10px; margin-top:10px;">
                    <button type="button" onclick="document.getElementById('popup').style.display='none'" style="flex:1; padding:10px;">Cancel</button>
                    <input type="submit" value="Create" style="flex:1; background:var(--success); color:#fff; border:none; border-radius:6px;">
                </div>
            </form>
        </div>
    </div>

    <script>
    function toggleClient(name) {
        if(!confirm("Toggle " + name + "?")) return;
        fetch('app/toggle.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: 'name=' + encodeURIComponent(name)
        }).then(() => location.reload());
    }
    </script>
</body>
</html>

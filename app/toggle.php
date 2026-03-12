<?php
// File: /var/www/html/pivpn/app/toggle.php
require_once 'auth.php';

if (isset($_POST['name'])) {
    $name = $_POST['name'];
    $file = "/etc/openvpn/ccd/" . $name;

    if (file_exists($file)) {
        $content = file_get_contents($file);
        if (substr($content, 0, 1) === "#") {
            // ENABLE: Remove the #
            $new_content = ltrim($content, "#");
        } else {
            // DISABLE: Add the #
            $new_content = "#" . $content;
        }
        // Save using sudo (ensure www-data has sudoers permission for 'tee')
        shell_exec("echo " . escapeshellarg($new_content) . " | sudo tee " . escapeshellarg($file));
        echo "success";
    } else {
        echo "CCD file not found";
    }
}

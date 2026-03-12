<?php
// File: /var/www/html/pivpn/app/get_history.php
error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json');

$dbPath = '/var/www/html/pivpn/usage_history.db';

if (!file_exists($dbPath)) {
    echo json_encode(["error" => "Database missing"]);
    exit;
}

try {
    $db = new SQLite3($dbPath, SQLITE3_OPEN_READONLY);
    
    // Check both 'name' and 'client' just in case
    $name = $_GET['name'] ?? $_GET['client'] ?? '';
    $name = trim(str_replace('.ovpn', '', $name));

    if (empty($name)) {
        // This is the error you were seeing
        echo json_encode(["error" => "No client name provided"]);
        exit;
    }

    $stmt = $db->prepare("SELECT up, down, timestamp FROM usage WHERE name = :name ORDER BY timestamp DESC LIMIT 24");
    $stmt->bindValue(':name', $name, SQLITE3_TEXT);
    $result = $stmt->execute();

    $history = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $history[] = [
            "up" => (float)$row['up'],
            "down" => (float)$row['down'],
            "timestamp" => $row['timestamp']
        ];
    }
    
    echo json_encode(array_reverse($history));

} catch (Exception $e) {
    echo json_encode(["error" => "SQL Error"]);
}

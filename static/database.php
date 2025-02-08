<?php
header('Content-Type: application/json');

// Load environment variables
$envFile = __DIR__ . "/../.env"; // Adjust path as needed

if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
} else {
    echo json_encode(["error" => "Environment file not found."]);
    exit();
}

$host = $env["DB_HOST"];
$dbname = $env["DB_NAME"];
$username = $env["DB_USER"];
$password = $env["DB_PASS"];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed", "message" => $e->getMessage()]);
    exit();
}
?>

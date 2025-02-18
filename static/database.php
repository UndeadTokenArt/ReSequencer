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

$dbFile = $env["DB_FILE"];

try {
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed", "message" => $e->getMessage()]);
    exit();
}
?>

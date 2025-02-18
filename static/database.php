<?php
header('Content-Type: application/json');

// Load environment variables
$envFile = __DIR__ . "/../.env";

if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
} else {
    echo json_encode(["error" => "Environment file not found."]);
    exit();
}

$dbFile = $env["DB_FILE"];

// Create database directory if it doesn't exist
$dbDir = dirname($dbFile);
if (!file_exists($dbDir)) {
    if (!mkdir($dbDir, 0777, true)) {
        echo json_encode(["error" => "Failed to create database directory"]);
        exit();
    }
}

try {
    // This will create the database file if it doesn't exist
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Create tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS addresses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        date_created DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $pdo->exec("CREATE TABLE IF NOT EXISTS waypoints (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        route_id INTEGER,
        address TEXT NOT NULL,
        latitude REAL,
        longitude REAL,
        sequence_order INTEGER,
        FOREIGN KEY (route_id) REFERENCES addresses(id)
    )");

    // Verify tables were created
    $tables = $pdo->query("SELECT name FROM sqlite_master WHERE type='table'")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('addresses', $tables) || !in_array('waypoints', $tables)) {
        throw new Exception("Failed to create database tables");
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed", "message" => $e->getMessage()]);
    exit();
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
    exit();
}

// Return success if everything worked
echo json_encode(["success" => true, "message" => "Database initialized successfully"]);
?>

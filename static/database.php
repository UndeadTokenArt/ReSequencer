<?php

// this file is a script that is supposed to initialize a SQLite database for storing addresses.
// The script is supposed to be run from the command line and uses the PDO extension to interact
// with the SQLite database. The script loads environment variables from a .env file and creates
// the database file if it doesn't exist. The script then creates tables for storing addresses
// and outputs a JSON response indicating whether the database was initialized successfully.

header('Content-Type: application/json');

// Load environment variables
$envFile = __DIR__ . "/../.env";

if (file_exists($envFile)) {
    $env = parse_ini_file($envFile);
} else {
    echo json_encode(["error" => "Environment file not found."]);
    exit();
}

$dbFile = "../data/GeolocationDataMarchtwentythird.sqlite";

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

    // Create tables
    $pdo->exec("CREATE TABLE IF NOT EXISTS locations (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        address_full TEXT NOT NULL,
        street_name TEXT NOT NULL,
        mail_city TEXT NOT NULL,
        state_abbrieviation TEXT NOT NULL,
        lat REAL,
        lon REAL
    )");

} catch (PDOException $e) {
    echo json_encode(["error" => "Database connection failed", "message" => $e->getMessage()]);
    exit();
}

if (basename(__FILE__) == basename($_SERVER['SCRIPT_FILENAME'])) {
    // Only output a JSON response if this file is run directly.
    echo json_encode(["success" => true, "message" => "Database initialized successfully"]);
}
?>

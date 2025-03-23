<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include "database.php"; // Ensure database connection

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["address"])) {
    $address = trim($_GET["address"]);
    
    // Debug logging
    error_log("Received address: '" . $address . "'");
    
    // Check if address exists in the database
    $stmt = $pdo->prepare("
        SELECT 
            address_full,
            lat,
            lon 
        FROM locations 
        WHERE address_full = ?
    ");
    $stmt->execute([$address]);
    
    // Debug the query parameters
    error_log("SQL Parameters: " . print_r([$address], true));
    
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Debug the result
    error_log("Query result: " . print_r($result, true));

    if ($result) {
        $lat = round($result["lat"], 7);
        $lon = round($result["lon"], 7);
        
        // Debug logging
        error_log("Coordinates being sent: lat={$lat}, lon={$lon}");
        
        echo json_encode([
            "lat" => $lat,
            "lon" => $lon,
            "source" => "database",
            "debug" => [
                "original_lat" => $result["lat"],
                "original_lon" => $result["lon"],
                "rounded_lat" => $lat,
                "rounded_lon" => $lon
            ]
        ]);
        exit;
    }

    // If no exact match found, return error with debug info
    echo json_encode([
        "error" => "Address not found in database",
        "message" => "Exact match required for testing",
        "debug" => [
            "received_address" => $address
        ]
    ]);
    exit;
}
?>

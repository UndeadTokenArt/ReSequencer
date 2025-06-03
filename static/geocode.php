<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the response content type to JSON
header('Content-Type: application/json');

// Include the database connection file
include "database.php"; // Ensure database connection

// Check if the request is a GET and the 'address' parameter is set
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["address"])) {
    $address = trim($_GET["address"]); // Get and trim the address from the query string

    // Debug logging: log the received address
    error_log("Received address: '" . $address . "'");

    // Prepare and execute a SQL statement to find the address in the database
    $stmt = $pdo->prepare("
        SELECT 
            address_full,
            lat,
            lon 
        FROM locations 
        WHERE address_full = ?
    ");
    $stmt->execute([$address]);

    // Debug logging: log the SQL parameters used
    error_log("SQL Parameters: " . print_r([$address], true));

    // Fetch the result as an associative array
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug logging: log the query result
    error_log("Query result: " . print_r($result, true));

    // If a matching address is found in the database
    if ($result) {
        $lat = round($result["lat"], 7); // Round latitude to 7 decimal places
        $lon = round($result["lon"], 7); // Round longitude to 7 decimal places

        // Debug logging: log the coordinates being sent
        error_log("Coordinates being sent: lat={$lat}, lon={$lon}");

        // Return the coordinates and debug info as JSON
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

    // If no exact match, try fuzzy search (case-insensitive, partial match)
    $fuzzyStmt = $pdo->prepare("
        SELECT 
            address_full,
            lat,
            lon 
        FROM locations 
        WHERE LOWER(address_full) LIKE LOWER(?) 
        LIMIT 5
    ");
    $fuzzyStmt->execute(['%' . $address . '%']);
    $fuzzyResults = $fuzzyStmt->fetchAll(PDO::FETCH_ASSOC);

    if ($fuzzyResults && count($fuzzyResults) > 0) {
        // Return all fuzzy matches
        echo json_encode([
            "fuzzy_matches" => array_map(function ($row) {
                return [
                    "address_full" => $row["address_full"],
                    "lat" => round($row["lat"], 7),
                    "lon" => round($row["lon"], 7)
                ];
            }, $fuzzyResults),
            "source" => "database",
            "match_type" => "fuzzy",
            "debug" => [
                "received_address" => $address
            ]
        ]);
        exit;
    }

    // If no exact match is found, return an error message with debug info
    echo json_encode([
        "error" => "Address not found in database",
        "message" => "Exact match required for testing",
        "debug" => [
            "received_address" => $address
        ]
    ]);
    exit;
}

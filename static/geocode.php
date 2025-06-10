<?php

// This handles checking if the addresses instered into the text field are in the database. This is so that the API calls to opencage are minimized.
// The functions here are called in the maps.js file inside the addmarkers() function. 
// first the input is cleaned to make it findable in the database.
// right now (6/4/25) the database only includes the portland metro area and in the locations table it looks up the address_full field for a match.


// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set the response content type to JSON
header('Content-Type: application/json');

// Include the database connection file
include "database.php"; // Ensure database connection

// Check if the request is a GET and the 'address' parameter is set
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["address"])) {
    $originalAddress = trim($_GET["address"]); // Store original address
    
    // First, remove everything after the first comma (city, state, zip, country)
    $address = trim(strtok($originalAddress, ','));
    
    // Define street types pattern
    $streetTypes = 'street|st|avenue|ave|road|rd|boulevard|blvd|lane|ln|drive|dr|court|ct|place|pl';
    
    // Extract everything up to and including the street type
    if (preg_match("/^(.*?\\b(?:$streetTypes)\\b)/i", $address, $matches)) {
        $streetAddress = trim($matches[1]); // This is the extracted street address
    } else {
        $streetAddress = $address; // If no match, use the comma-stripped address
    }

    // Clean and normalize the address
    $cleanedAddress = strtolower($streetAddress);
    // Normalize directionals first
    $cleanedAddress = preg_replace('/\bnortheast\b/i', 'ne', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bnorthwest\b/i', 'nw', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bsoutheast\b/i', 'se', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bsouthwest\b/i', 'sw', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bnorth\b/i', 'n', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bsouth\b/i', 's', $cleanedAddress);
    $cleanedAddress = preg_replace('/\beast\b/i', 'e', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bwest\b/i', 'w', $cleanedAddress);
    // Now normalize street types
    $cleanedAddress = preg_replace('/\bavenue\b/i', 'ave', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bstreet\b/i', 'st', $cleanedAddress);
    $cleanedAddress = preg_replace('/\broad\b/i', 'rd', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bboulevard\b/i', 'blvd', $cleanedAddress);
    $cleanedAddress = preg_replace('/\blane\b/i', 'ln', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bdrive\b/i', 'dr', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bcourt\b/i', 'ct', $cleanedAddress);
    $cleanedAddress = preg_replace('/\bplace\b/i', 'pl', $cleanedAddress);
    // Clean up whitespace
    $cleanedAddress = preg_replace('/\s+/i', ' ', $cleanedAddress);
    $cleanedAddress = trim($cleanedAddress);

    // Debug logging
    error_log("Original address: '" . $originalAddress . "'");
    error_log("After comma removal: '" . $address . "'");
    error_log("Street address extraction: '" . $streetAddress . "'");
    error_log("Final cleaned address: '" . $cleanedAddress . "'");

    // Modify the fuzzy search query to match exact number at start
    $fuzzyStmt = $pdo->prepare("
        SELECT 
            address_full,
            lat,
            lon 
        FROM locations 
        WHERE LOWER(address_full) LIKE LOWER(?)
        LIMIT 5
    ");

    // Debug logging: log the received address
    error_log("Received address: '" . $address . "'");
    error_log("Extracted street address: '" . $streetAddress . "'");
    error_log("Cleaned address: '" . $cleanedAddress . "'");

    // Prepare and execute a SQL statement to find the address in the database
    $stmt = $pdo->prepare("
        SELECT 
            address_full,
            lat,
            lon 
        FROM locations 
        WHERE address_full = ?
    ");
    $stmt->execute([$cleanedAddress]);

    // Debug logging: log the SQL parameters used
    error_log("SQL Parameters: " . print_r([$cleanedAddress], true));

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
    $fuzzyStmt->execute(['%' . $cleanedAddress . '%']);
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
                "received_address" => $address,
                "extracted_address" => $streetAddress,
                "cleaned_address" => $cleanedAddress
            ]
        ]);
        exit;
    }

    // If no exact match is found, return an error message with debug info
    echo json_encode([
        "error" => "Address not found in database",
        "received_address" => $address,
        "cleaned_address" => $cleanedAddress
    ]);
    exit;
}

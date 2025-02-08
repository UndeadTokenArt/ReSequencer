<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json'); // Ensure JSON response

include "database.php"; // Check if this file exists and works properly

if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET["address"])) {
    $address = trim($_GET["address"]);
    $forceApi = isset($_GET["forceApi"]); // Flag to force API call

    if (!$forceApi) {
        // Check if address exists in the database
        $stmt = $pdo->prepare("SELECT latitude, longitude FROM locations WHERE address = ?");
        $stmt->execute([$address]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            echo json_encode(["latitude" => $result["latitude"], "longitude" => $result["longitude"], "source" => "database"]);
            exit;
        }
    }

    // If not found in DB, query OpenCage API
    $apiKey = "your_opencage_api_key";
    $url = "https://api.opencagedata.com/geocode/v1/json?q=" . urlencode($address) . "&key=" . $apiKey;
    $response = file_get_contents($url);
    $data = json_decode($response, true);

    if (!empty($data["results"])) {
        $latitude = $data["results"][0]["geometry"]["lat"];
        $longitude = $data["results"][0]["geometry"]["lng"];

        // Save to database
        $stmt = $pdo->prepare("INSERT INTO locations (address, latitude, longitude) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE latitude = VALUES(latitude), longitude = VALUES(longitude)");
        $stmt->execute([$address, $latitude, $longitude]);

        echo json_encode(["latitude" => $latitude, "longitude" => $longitude, "source" => "api"]);
        exit;
    }

    echo json_encode(["error" => "Address not found"]);
}
?>


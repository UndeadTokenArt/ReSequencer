<?php

// this file is a script that is supposed to fetch addresses from the OpenStreetMap Nominatim API
// and store them in a SQLite database. The script is supposed to be run from the command line
// and will fetch addresses in the Portland, Oregon area. The script uses the PDO extension to
// interact with the SQLite database and the file_get_contents function to make requests to the Nominatim API.
// The script also uses the parse_ini_file function to load environment variables from a .env file.
// The script defines a function fetchLocationsInBbox that fetches addresses in a given bounding box
// from the Nominatim API. The script also defines a function createSubBoxes that creates sub-boxes
// within a given bounding box. The script then loops through the sub-boxes, fetches addresses from
// the Nominatim API, and stores them in the SQLite database. The script outputs debug information to the
// console and saves the API response to a file named debug_output.json. The script also defines a function
// addressExists that checks if an address already exists in the database. The script outputs the total number
// of locations added to the database when it is done processing addresses.
// The script uses the following environment variables from the .env file:


error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load environment variables
$envFile = __DIR__ . "/../.env";
if (!file_exists($envFile)) {
    die("Error: .env file not found\n");
}

$env = parse_ini_file($envFile);
$dbFile = $env['DB_FILE'];
$email = $env['MY_EMAIL'];

// Add global configuration variables
$limit = 50; // Maximum results per request to Nominatim

// Initialize database connection
try {
    $pdo = new PDO("sqlite:" . __DIR__ . "/../" . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage() . "\n");
}

// Create tables if they don't exist
try {
    // Create addresses table
    $pdo->exec("CREATE TABLE IF NOT EXISTS addresses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL
    )");

    // Create waypoints table
    $pdo->exec("CREATE TABLE IF NOT EXISTS waypoints (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        route_id INTEGER NOT NULL,
        address TEXT NOT NULL,
        latitude REAL NOT NULL,
        longitude REAL NOT NULL,
        sequence_order INTEGER NOT NULL,
        FOREIGN KEY (route_id) REFERENCES addresses(id)
    )");

    echo "Database tables verified.\n";
} catch (PDOException $e) {
    die("Error creating tables: " . $e->getMessage() . "\n");
}

// Portland area boundaries
$bbox = [
    'south' => 45.3835,
    'west' => -122.7966,
    'north' => 45.6097,
    'east' => -122.3977
];

function fetchLocationsInBbox($bbox, $email) {
    global $limit;
    $viewbox = "{$bbox['west']},{$bbox['north']},{$bbox['east']},{$bbox['south']}";
    
    $url = 'https://nominatim.openstreetmap.org/search?' . http_build_query([
        'viewbox' => $viewbox,
        'bounded' => 1,
        'format' => 'json',
        'addressdetails' => 1,
        'extratags' => 1, // Include extra tags
        'limit' => $limit,
        'country' => 'united states',
        'state' => 'oregon',
        'city' => 'portland',
        'type' => 'house'
    ]);

    echo "DEBUG: Requesting URL: $url\n";

    $opts = [
        'http' => [
            'method' => 'GET',
            'header' => [
                'User-Agent: ReSequencer/1.0 (' . $email . ')',
            ]
        ]
    ];

    $context = stream_context_create($opts);
    $response = file_get_contents($url, false, $context);
    if ($response === false) {
        die("Error: API request failed\n");
    }
    
    $decoded = json_decode($response, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        die("Error: Failed to decode JSON response\n");
    }
    
    file_put_contents("debug_output.json", json_encode($decoded, JSON_PRETTY_PRINT)); // Save response
    echo "DEBUG: API Response saved to debug_output.json\n";
    

    // Filter results to include only residential addresses
    $residential_locations = $decoded;

    echo "DEBUG: Filtered to " . count($residential_locations) . " residential results\n";
    return $residential_locations;
}

function createSubBoxes($bbox, $divisions) {
    $boxes = [];
    $lat_step = ($bbox['north'] - $bbox['south']) / $divisions;
    $lon_step = ($bbox['east'] - $bbox['west']) / $divisions;
    
    for ($i = 0; $i < $divisions; $i++) {
        for ($j = 0; $j < $divisions; $j++) {
            $boxes[] = [
                'south' => $bbox['south'] + ($i * $lat_step),
                'north' => $bbox['south'] + (($i + 1) * $lat_step),
                'west' => $bbox['west'] + ($j * $lon_step),
                'east' => $bbox['west'] + (($j + 1) * $lon_step)
            ];
        }
    }
    return $boxes;
}

function addressExists($pdo, $formatted_address) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM addresses WHERE name = ?");
    $stmt->execute([$formatted_address]);
    return (int)$stmt->fetchColumn() > 0;
}

// Main processing loop
$divisions = 4; // 142 x 142 sub-boxes is 20,164 total
$subBoxes = createSubBoxes($bbox, $divisions);
$total_processed = 0;
$max_locations = 50000;

foreach ($subBoxes as $boxIndex => $currentBox) {
    echo "\nProcessing sub-box " . ($boxIndex + 1) . " of " . count($subBoxes) . "\n";
    echo "Boundaries: N:{$currentBox['north']}, S:{$currentBox['south']}, E:{$currentBox['east']}, W:{$currentBox['west']}\n";
    
    // Respect Nominatim's usage policy
    sleep(rand(2, 5));
    
    $locations = fetchLocationsInBbox($currentBox, $email);
    
    if (empty($locations)) {
        echo "No results in current sub-box\n";
        continue;
    }

    foreach ($locations as $location) {
        if (empty($location['address'])) {
            continue;
        }

        // Format address from address details
        $address_parts = [];
        if (!empty($location['address']['house_number'])) {
            $address_parts[] = $location['address']['house_number'];
        }
        if (!empty($location['address']['road'])) {
            $address_parts[] = $location['address']['road'];
        }
        $formatted_address = implode(' ', $address_parts) . ', Portland, OR';

        // Skip if address already exists
        if (addressExists($pdo, $formatted_address)) {
            echo "Skipping duplicate address: $formatted_address\n";
            continue;
        }

        try {
            $pdo->beginTransaction();
            
            // Insert into addresses table
            $stmt = $pdo->prepare("INSERT INTO addresses (name) VALUES (?)");
            $stmt->execute([$formatted_address]);
            $addressId = $pdo->lastInsertId();
            
            // Insert into waypoints table
            $stmt = $pdo->prepare("INSERT INTO waypoints (route_id, address, latitude, longitude, sequence_order) 
                                 VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([
                $addressId,
                $formatted_address,
                $location['lat'],
                $location['lon'],
                $total_processed + 1
            ]);
            
            $pdo->commit();
            echo "Added: $formatted_address (lat: {$location['lat']}, lon: {$location['lon']})\n";
            $total_processed++;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            echo "Error processing address: " . $e->getMessage() . "\n";
        }
        
        if ($total_processed >= $max_locations) {
            break 2; // Break both loops if we've reached the maximum
        }
    }
}

echo "Done processing addresses. Total locations added: $total_processed\n";
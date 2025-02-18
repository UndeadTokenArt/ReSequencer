<?php
require_once('database.php');

try {
    // Test inserting a route
    $stmt = $pdo->prepare("INSERT INTO addresses (name) VALUES (?)");
    $stmt->execute(['Test Address']);
    $routeId = $pdo->lastInsertId();
    
    // Test inserting a waypoint
    $stmt = $pdo->prepare("INSERT INTO waypoints (route_id, address, latitude, longitude, sequence_order) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$routeId, '123 Test St', 40.7128, -74.0060, 1]);
    
    // Test querying the data
    $stmt = $pdo->query("SELECT r.name, w.address FROM addresses r JOIN waypoints w ON r.id = w.route_id");
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $results]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>
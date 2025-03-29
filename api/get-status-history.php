<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../auth/functions.php';

session_start();
if (!isAuthenticated() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

if (!isset($_GET['application_id']) || !is_numeric($_GET['application_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid application ID is required']);
    exit();
}

$appId = (int)$_GET['application_id'];

try {
    $db = (new Database())->connect();
    
    $stmt = $db->prepare("
        SELECT 
            h.*, 
            u.full_name as changed_by_name 
        FROM application_status_history h
        LEFT JOIN users u ON h.changed_by = u.id
        WHERE h.application_id = ?
        ORDER BY h.created_at DESC
    ");
    $stmt->execute([$appId]);
    $history = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($history);
    
} catch (PDOException $e) {
    error_log("Database Error in get-status-history.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}
?>
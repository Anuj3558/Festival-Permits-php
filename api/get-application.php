<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/functions.php';

// Verify the request is from an authenticated admin
session_start();
if (!isAuthenticated() || getUserRole() !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized access']);
    exit();
}

// Validate application ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Valid application ID is required']);
    exit();
}

$appId = (int)$_GET['id'];

try {
    $db = (new Database())->connect();
    
    // Get application details with user information
    $stmt = $db->prepare("
        SELECT 
            *
        FROM applications a
        WHERE a.id = ?
    ");
    $stmt->execute([$appId]);
    $application = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$application) {
        http_response_code(404);
        echo json_encode(['error' => 'Application not found']);
        exit();
    }
    
    // Format the response data
    $response = [
        'application_number' => $application['application_number'],
        'created_at' => $application['created_at'],
        'status' => $application['status'],
        'applicant' => [
            'name' => $application['applicant_name'],
            'email' => $application['applicant_email'],
            'mobile' => $application['applicant_mobile'],
            'organization' => $application['applicant_organization']
        ],
        'festival' => [
            'type' => $application['festival_type'],
            'location' => $application['location_type'],
            'address' => $application['address'],
            'duration' => $application['duration'],
            'date_from' => $application['date_from'],
            'date_to' => $application['date_to']
        ],
        'pandal' => [
            'length' => $application['length'],
            'width' => $application['width'],
            'height' => $application['height'],
            'area' => $application['area']
        ],
        'sound_system' => [
            'required' => (bool)$application['sound_system'],
            'power' => $application['sound_system_power']
        ],
        'fees' => [
            'base_fee' => $application['base_fee'],
            'sound_fee' => $application['sound_fee'],
            'total_fee' => $application['fee_amount']
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database Error in get-application.php: " . $e->getMessage());
    http_response_code(500);
    echo '<script>console.error("Database Error: ' . addslashes($e->getMessage()) . '");</script>';
    echo json_encode(['error' => 'Internal server error']);
}
?>
<?php
// Turn off error display in the output
ini_set('display_errors', 0);
error_reporting(E_ALL);

// Set proper content type header
header('Content-Type: application/json');

// Start session
session_start();
require_once __DIR__ . '/../auth/functions.php';

// Verify the request is from an authenticated admin
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

require_once __DIR__ . '/../config/database.php';

try {
    $db = (new Database())->connect();
    
    // Get application details
    $stmt = $db->prepare("
        SELECT * FROM applications WHERE id = ?
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
        'id' => $application['id'],
        'application_number' => $application['application_number'],
        'created_at' => $application['created_at'],
        'status' => $application['status'],
        'applicant' => [
            'id' => $application['user_id'],
            'name' => $application['applicant_name'],
            'email' => $application['email'],
            'mobile' => $application['mobile'],
            'organization' => $application['organization'] ?? 'N/A'
        ],
        'festival' => [
            'type' => $application['festival_type'],
            'location' => $application['location_type'],
            'address' => $application['address'],
            'duration' => $application['duration'],
            'date_from' => $application['start_date'],
            'date_to' => $application['end_date']
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
            'total_fee' => $application['fee_amount']
        ],
        'payment_status' => $application['payment_received'] ? 'Received' : 'Pending',
        'id_proof' => [
            'type' => $application['id_proof_type'],
            'number' => $application['id_proof_number']
        ]
    ];
    
    echo json_encode($response);
    
} catch (PDOException $e) {
    error_log("Database Error in get-application.php: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
    exit();
}

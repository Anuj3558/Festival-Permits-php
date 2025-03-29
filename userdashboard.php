<?php
session_start();
require_once __DIR__ . '/auth/functions.php';

if (!isAuthenticated()) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    logout();
    header('Location: login.php');
    exit();
}

require_once __DIR__ . '/config/database.php';
$db = (new Database())->connect();

// Handle form submission
$submissionSuccess = false;
$applicationNumber = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_application'])) {
    // Validate and process form data
    $formData = $_POST['pandal'] ?? [];
    $personalInfo = $_POST['personal'] ?? [];
    
    // Validate pandal details
    if (empty($formData['festival'])) {
        $errors['pandal']['festival'] = 'Festival type is required';
    }
    
    if (empty($formData['length']) || !is_numeric($formData['length']) || $formData['length'] <= 0) {
        $errors['pandal']['length'] = 'Valid length is required';
    }
    
    if (empty($formData['width']) || !is_numeric($formData['width']) || $formData['width'] <= 0) {
        $errors['pandal']['width'] = 'Valid width is required';
    }
    
    if (empty($formData['height']) || !is_numeric($formData['height']) || $formData['height'] <= 0) {
        $errors['pandal']['height'] = 'Valid height is required';
    }
    
    if (empty($formData['duration']) || !is_numeric($formData['duration']) || $formData['duration'] <= 0) {
        $errors['pandal']['duration'] = 'Valid duration is required';
    }
    
    if (empty($formData['location'])) {
        $errors['pandal']['location'] = 'Location type is required';
    }
    
    // Validate sound system power if sound system is checked
    if (!empty($formData['sound_system']) && (empty($formData['sound_system_power']) || !is_numeric($formData['sound_system_power']) || $formData['sound_system_power'] <= 0)) {
        $errors['pandal']['sound_system_power'] = 'Valid sound system power is required';
    }
    
    // Validate personal info
    if (empty($personalInfo['name'])) {
        $errors['personal']['name'] = 'Name is required';
    }
    
    if (empty($personalInfo['mobile']) || !preg_match('/^\d{10}$/', $personalInfo['mobile'])) {
        $errors['personal']['mobile'] = 'Valid 10-digit mobile number is required';
    }
    
    if (empty($personalInfo['email']) || !filter_var($personalInfo['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['personal']['email'] = 'Valid email is required';
    }
    
    if (empty($personalInfo['address'])) {
        $errors['personal']['address'] = 'Address is required';
    }
    
    if (empty($personalInfo['id_proof'])) {
        $errors['personal']['id_proof'] = 'ID proof type is required';
    }
    
    if (empty($personalInfo['id_number'])) {
        $errors['personal']['id_number'] = 'ID number is required';
    }
    
    // Validate declaration checkbox
    if (empty($_POST['declaration'])) {
        $errors['declaration'] = 'You must accept the declaration';
    }
    
    // If no errors, save to database
    if (empty($errors)) {
        try {
            // Calculate area
            $area = $formData['length'] * $formData['width'];
            
            // Generate application number
            $applicationNumber = 'FEST' . date('Ymd') . substr(str_shuffle('0123456789'), 0, 4);
            
            // Calculate fee (simplified for example)
            $baseFee = 1000;
            $areaFee = $area * 10;
            $durationFee = $formData['duration'] * 200;
            $soundSystemFee = isset($formData['sound_system']) ? 500 : 0;
            $locationMultiplier = $formData['location'] === 'commercial' ? 1.5 : ($formData['location'] === 'high_traffic' ? 2 : 1);
            
            $feeAmount = ($baseFee + $areaFee + $durationFee + $soundSystemFee) * $locationMultiplier;
            
            // Insert application
            $stmt = $db->prepare("
                INSERT INTO applications (
                    user_id, 
                    application_number, 
                    festival_type, 
                    location_type, 
                    address, 
                    duration, 
                    area, 
                    length, 
                    width, 
                    height, 
                    sound_system, 
                    sound_system_power, 
                    fee_amount, 
                    status,
                    start_date,
                    end_date,
                    applicant_name,
                    applicant_mobile,
                    applicant_email,
                    id_proof_type,
                    id_proof_number
                ) VALUES (
                    :user_id, 
                    :application_number, 
                    :festival_type, 
                    :location_type, 
                    :address, 
                    :duration, 
                    :area, 
                    :length, 
                    :width, 
                    :height, 
                    :sound_system, 
                    :sound_system_power, 
                    :fee_amount, 
                    'pending',
                    DATE_ADD(NOW(), INTERVAL 7 DAY),
                    DATE_ADD(NOW(), INTERVAL :duration DAY),
                    :applicant_name,
                    :applicant_mobile,
                    :applicant_email,
                    :id_proof_type,
                    :id_proof_number
                )
            ");
            
            $stmt->execute([
                ':user_id' => $_SESSION['user']['id'],
                ':application_number' => $applicationNumber,
                ':festival_type' => $formData['festival'],
                ':location_type' => $formData['location'],
                ':address' => $personalInfo['address'],
                ':duration' => $formData['duration'],
                ':area' => $area,
                ':length' => $formData['length'],
                ':width' => $formData['width'],
                ':height' => $formData['height'],
                ':sound_system' => isset($formData['sound_system']) ? 1 : 0,
                ':sound_system_power' => $formData['sound_system_power'] ?? 0,
                ':fee_amount' => $feeAmount,
                ':applicant_name' => $personalInfo['name'],
                ':applicant_mobile' => $personalInfo['mobile'],
                ':applicant_email' => $personalInfo['email'],
                ':id_proof_type' => $personalInfo['id_proof'],
                ':id_proof_number' => $personalInfo['id_number']
            ]);
            
            $submissionSuccess = true;
            
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            $errors['database'] = 'An error occurred while saving your application. Please try again.';
            echo '<script>console.error("Database Error: ' . addslashes($e->getMessage()) . '");</script>';
        }
    }
}

// Get filter from query parameter for the dashboard
try {
    // Database connection

    // Check if 'user_auth' cookie is set
    if (!isset($_COOKIE['user_auth'])) {
        error_log("User authentication cookie not found.");
        die("Authentication error: User not logged in.");
    }

    // Get user ID from the 'user_auth' cookie
    $userId = $_COOKIE['user_auth'];

    // Filter parameter (default to 'all')
    $filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

    // Validate filter input
    $validFilters = ['all', 'pending', 'approved', 'rejected'];
    if (!in_array($filter, $validFilters)) {
        error_log("Invalid filter value: " . $filter);
        die("Invalid filter value.");
    }

    // Build query
    $query = "
        SELECT 
            a.id,
            a.application_number,
            a.festival_type,
            a.location_type,
            a.address,
            a.duration,
            a.area,
            a.fee_amount,
            a.status,
            a.created_at,
            a.start_date,
            a.end_date,
            CASE 
                WHEN a.payment_received = 1 THEN 'paid'
                WHEN a.status = 'approved' THEN 'pending'
                ELSE 'not_required'
            END as payment_status
        FROM applications a
        WHERE a.user_id = :user_id
    ";

    // Add filter condition if applicable
    $params = [':user_id' => $userId];
    if ($filter !== 'all') {
        $query .= " AND a.status = :filter";
        $params[':filter'] = $filter;
    }

    $query .= " ORDER BY a.created_at DESC";

    // Prepare and execute query
    $stmt = $db->prepare($query);
    $stmt->execute($params);

    // Fetch applications
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo '<script>console.error("Database Error: ' . addslashes($e->getMessage()) . '");</script>';
    die("Database error occurred.");
}

// Output applications (example JSON response)

function getStatusBadgeClass($status) {
    switch($status) {
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'pending':
        default:
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    }
}

function getPaymentStatusBadgeClass($status) {
    switch($status) {
        case 'paid':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
        case 'not_required':
        default:
            return 'bg-gray-100 text-gray-800 border-gray-200';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - Festival Permits</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .bg-gov-blue { background-color: #1d4ed8; }
        .text-gov-blue { color: #1d4ed8; }
        .bg-gov-orange { background-color: #f97316; }
        .text-gov-orange { color: #f97316; }
        .bg-gov-darkblue { background-color: #1e3a8a; }
        .text-gov-darkblue { color: #1e3a8a; }
        
        :root {
            --gov-blue: #1a4b8c;
            --gov-darkblue: #0d2b4e;
            --gov-lightgray: #f3f4f6;
            --gov-darkgray: #6b7280;
        }
        
        .bg-gov-blue {
            background-color: var(--gov-blue);
        }
        
        .bg-gov-darkblue {
            background-color: var(--gov-darkblue);
        }
        
        .text-gov-blue {
            color: var(--gov-blue);
        }
        
        .text-gov-darkblue {
            color: var(--gov-darkblue);
        }
        
        .border-gov-blue {
            border-color: var(--gov-blue);
        }
        
        /* Animation classes */
        .stagger-animation > * {
            animation: fadeInUp 0.5s ease-out forwards;
            opacity: 0;
        }
        
        .stagger-animation > *:nth-child(1) {
            animation-delay: 0.1s;
        }
        
        .stagger-animation > *:nth-child(2) {
            animation-delay: 0.3s;
        }
        
        .stagger-animation > *:nth-child(3) {
            animation-delay: 0.5s;
        }
        
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .marquee {
            white-space: nowrap;
            overflow: hidden;
            box-sizing: border-box;
        }
        
        .marquee span {
            display: inline-block;
            padding-left: 100%;
            animation: marquee 20s linear infinite;
        }
        
        @keyframes marquee {
            0%   { transform: translate(0, 0); }
            100% { transform: translate(-100%, 0); }
        }
        
        .gov-input  w-full h-10  border-2 p-2  {
            @apply w-full px-3 py-2 border border-gray-300 rounded-sm focus:outline-none focus:ring-1 focus:ring-gov-blue focus:border-gov-blue;
        }
        
        .gov-label {
            @apply block text-sm font-medium text-gray-700 mb-1;
        }
        
        .gov-btn {
            @apply bg-gov-blue text-white hover:bg-opacity-90 px-4 py-2 rounded-sm text-sm font-medium;
        }
        
        .gov-btn-secondary {
            @apply bg-white border border-gray-300 text-gray-700 hover:bg-gray-50 px-4 py-2 rounded-sm text-sm font-medium;
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease-out forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-gray-50">
    
    <main class="flex-grow">
        <section class="bg-white text-gov-blue py-6 md:py-8">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <div class="flex items-center mb-3 justify-center md:justify-start">
                            <img src="https://www.pcmcindia.gov.in/images/logo.png" alt="Logo" class="h-12">
                        </div>
                        <p class="text-sm md:text-base mt-1">
                            Welcome back, <?= htmlspecialchars($_SESSION['user']['full_name'] ?? 'User'); ?>
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-3">
                        <button 
                            onclick="document.getElementById('applicationModal').classList.remove('hidden')"
                            class="bg-gov-blue text-white hover:bg-opacity-90 px-4 py-2 rounded-sm text-sm font-medium"
                        >
                            New Application
                        </button>
                        <a 
                            href="?action=logout" 
                            class="bg-transparent border border-gov-blue text-gov-blue hover:bg-gov-blue hover:text-white px-4 py-2 rounded-sm text-sm font-medium"
                        >
                            Logout
                        </a>
                    </div>
                </div>
                <div class="bg-yellow-100 mt-4 text-gov-blue text-sm py-2 overflow-hidden">
                    <div class="marquee">
                        <span class="inline-block">
                            Latest Update: Online permit applications now available for Ganpati and Durga Puja festivals | 
                            Processing time reduced to 3-5 working days | 
                            New digital payment options now supported
                        </span>
                    </div>
                </div>
            </div>
        </section>
        
        <section class="py-6 md:py-8">
            <div class="container mx-auto px-4">
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm">
                    <div class="p-4 border-b border-gray-200">
                        <h2 class="text-lg font-bold text-gov-darkblue">Your Festival Permit Applications</h2>
                    </div>
                    
                    <div class="p-4 bg-gray-50 border-b border-gray-200">
                        <div class="flex flex-wrap gap-2">
                            <?php
                            $filterButtons = [
                                'all' => 'All Applications',
                                'pending' => 'Pending',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected'
                            ];
                            
                            foreach ($filterButtons as $key => $label) {
                                $active = $filter === $key ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300';
                                echo "<a href='?filter=$key' class='$active px-4 py-2 rounded-sm text-sm font-medium'>$label</a>";
                            }
                            ?>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <?php if (!empty($applications)): ?>
                            <table class="w-full">
                                <thead class="bg-gray-50 text-left">
                                    <tr>
                                        <th class="px-4 py-3 text-sm font-semibold text-gray-700">Application ID</th>
                                        <th class="px-4 py-3 text-sm font-semibold text-gray-700">Festival Type</th>
                                        <th class="px-4 py-3 text-sm font-semibold text-gray-700">Location</th>
                                        <th class="px-4 py-3 text-sm font-semibold text-gray-700">Festival Dates</th>
                                        <th class="px-4 py-3 text-sm font-semibold text-gray-700">Status</th>
                                        <th class="px-4 py-3 text-sm font-semibold text-gray-700">Fee</th>
                                        <th class="px-4 py-3 text-sm font-semibold text-gray-700">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php foreach ($applications as $app): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= htmlspecialchars($app['application_number']) ?>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <?= date('d M Y', strtotime($app['created_at'])) ?>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= ucfirst(htmlspecialchars($app['festival_type'])) ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= ucfirst(htmlspecialchars($app['location_type'])) ?>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <?= substr(htmlspecialchars($app['address']), 0, 20) ?>...
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= date('d M Y', strtotime($app['start_date'])) ?> to 
                                                <?= date('d M Y', strtotime($app['end_date'])) ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-medium rounded-sm border <?= getStatusBadgeClass($app['status']) ?>">
                                                    <?= ucfirst(htmlspecialchars($app['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <div>₹<?= number_format($app['fee_amount'], 2) ?></div>
                                               
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <div class="flex space-x-2">
                                                    <button 
                                                        onclick="showApplicationDetails('<?= htmlspecialchars(json_encode($app), ENT_QUOTES, 'UTF-8') ?>')"
                                                        class="bg-white border border-gray-300 px-2 py-1 h-8 text-xs rounded-sm"
                                                    >
                                                        View Details
                                                    </button>
                                                   
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php else: ?>
                            <div class="p-8 text-center">
                                <p class="text-gray-500">No applications found.</p>
                                <button 
                                    onclick="document.getElementById('applicationModal').classList.remove('hidden')"
                                    class="mt-4 bg-gov-blue text-white px-4 py-2 rounded-sm inline-block"
                                >
                                    Create New Application
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <!-- Application Form Modal -->
    <div id="applicationModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center <?= isset($_POST['submit_application']) && $submissionSuccess ? '' : 'hidden' ?> z-50 overflow-y-auto py-8">
        <div class="bg-white rounded-sm p-6 max-w-4xl w-full mx-4 my-8 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold text-gov-darkblue">New Festival Permit Application</h3>
                <button 
                    onclick="document.getElementById('applicationModal').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <?php if ($submissionSuccess): ?>
                <div class="bg-green-50 border border-green-200 text-green-700 p-4 rounded-sm mb-4">
                    <div class="flex items-center">
                        <svg class="h-5 w-5 text-green-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        <span class="font-medium">Application Submitted Successfully!</span>
                    </div>
                    <p class="mt-2">Your application number is: <strong><?= $applicationNumber ?></strong></p>
                    <p class="mt-2">You can track the status of your application in your dashboard.</p>
                </div>
                
                <div class="flex justify-end">
                    <button 
                        onclick="document.getElementById('applicationModal').classList.add('hidden'); window.location.href = window.location.href.split('?')[0];"
                        class="gov-btn"
                    >
                        Close
                    </button>
                </div>
            <?php else: ?>
                <?php if (isset($errors['database'])): ?>
                    <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-sm mb-4">
                        <?= $errors['database'] ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <input type="hidden" name="submit_application" value="1">
                    
                    <?php if (!empty($errors) && !isset($errors['database'])): ?>
                        <div class="bg-red-50 border border-red-200 text-red-700 p-4 rounded-sm mb-4">
                            <div class="flex items-center">
                                <svg class="h-5 w-5 text-red-500 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                                </svg>
                                <span class="font-medium">Please fix the following errors:</span>
                            </div>
                            <ul class="mt-2 list-disc list-inside text-sm">
                                <?php 
                                // Flatten all errors into a single array
                                $allErrors = [];
                                foreach ($errors as $section) {
                                    if (is_array($section)) {
                                        foreach ($section as $fieldErrors) {
                                            if (is_array($fieldErrors)) {
                                                $allErrors = array_merge($allErrors, array_values($fieldErrors));
                                            } else {
                                                $allErrors[] = $fieldErrors;
                                            }
                                        }
                                    } else {
                                        $allErrors[] = $section;
                                    }
                                }
                                
                                foreach ($allErrors as $error): ?>
                                    <li><?= htmlspecialchars($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                    
                    <div class="space-y-6">
                        <!-- Pandal Details Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-lg font-medium text-gov-darkblue mb-4">Pandal Details</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="festival" class="gov-label">Festival Type</label>
                                    <select
                                        id="festival"
                                        name="pandal[festival]"
                                        class="gov-input  w-full h-10  border-2 p-2   w-full h-10  border-2<?= isset($errors['pandal']['festival']) ? 'border-red-500' : '' ?>"
                                    >
                                        <option value="ganpati" <?= ($_POST['pandal']['festival'] ?? '') === 'ganpati' ? 'selected' : '' ?>>Ganpati Festival</option>
                                        <option value="durgapuja" <?= ($_POST['pandal']['festival'] ?? '') === 'durgapuja' ? 'selected' : '' ?>>Durga Puja</option>
                                    </select>
                                    <?php if (isset($errors['pandal']['festival'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['pandal']['festival'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div>
                                        <label for="length" class="gov-label">Length (meters)</label>
                                        <input
                                            type="number"
                                            id="length"
                                            name="pandal[length]"
                                            value="<?= htmlspecialchars($_POST['pandal']['length'] ?? '') ?>"
                                            min="1"
                                            step="0.01"
                                            placeholder="Enter length"
                                            class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['pandal']['length']) ? 'border-red-500' : '' ?>"
                                            onchange="calculateArea()"
                                        />
                                        <?php if (isset($errors['pandal']['length'])): ?>
                                            <p class="text-red-500 text-xs mt-1"><?= $errors['pandal']['length'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div>
                                        <label for="width" class="gov-label">Width (meters)</label>
                                        <input
                                            type="number"
                                            id="width"
                                            name="pandal[width]"
                                            value="<?= htmlspecialchars($_POST['pandal']['width'] ?? '') ?>"
                                            min="1"
                                            step="0.01"
                                            placeholder="Enter width"
                                            class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['pandal']['width']) ? 'border-red-500' : '' ?>"
                                            onchange="calculateArea()"
                                        />
                                        <?php if (isset($errors['pandal']['width'])): ?>
                                            <p class="text-red-500 text-xs mt-1"><?= $errors['pandal']['width'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div>
                                        <label for="height" class="gov-label">Height (meters)</label>
                                        <input
                                            type="number"
                                            id="height"
                                            name="pandal[height]"
                                            value="<?= htmlspecialchars($_POST['pandal']['height'] ?? '') ?>"
                                            min="1"
                                            step="0.01"
                                            placeholder="Enter height"
                                            class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['pandal']['height']) ? 'border-red-500' : '' ?>"
                                        />
                                        <?php if (isset($errors['pandal']['height'])): ?>
                                            <p class="text-red-500 text-xs mt-1"><?= $errors['pandal']['height'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="area" class="gov-label">Total Area (square meters)</label>
                                    <input
                                        type="number"
                                        id="area"
                                        name="pandal[area]"
                                        value="<?= htmlspecialchars(($_POST['pandal']['length'] ?? 0) * ($_POST['pandal']['width'] ?? 0)) ?>"
                                        readonly
                                        class="gov-input  w-full h-10  border-2 p-2  bg-gray-50"
                                    />
                                    <p class="text-xs text-gray-500 mt-1">Automatically calculated from length and width</p>
                                </div>
                                
                                <div>
                                    <label for="duration" class="gov-label">Duration (days)</label>
                                    <input
                                        type="number"
                                        id="duration"
                                        name="pandal[duration]"
                                        value="<?= htmlspecialchars($_POST['pandal']['duration'] ?? '5') ?>"
                                        min="1"
                                        max="15"
                                        placeholder="Enter duration"
                                        class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['pandal']['duration']) ? 'border-red-500' : '' ?>"
                                    />
                                    <?php if (isset($errors['pandal']['duration'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['pandal']['duration'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <label for="location" class="gov-label">Location Type</label>
                                    <select
                                        id="location"
                                        name="pandal[location]"
                                        class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['pandal']['location']) ? 'border-red-500' : '' ?>"
                                    >
                                        <option value="residential" <?= ($_POST['pandal']['location'] ?? '') === 'residential' ? 'selected' : '' ?>>Residential Area</option>
                                        <option value="commercial" <?= ($_POST['pandal']['location'] ?? '') === 'commercial' ? 'selected' : '' ?>>Commercial Area</option>
                                        <option value="high_traffic" <?= ($_POST['pandal']['location'] ?? '') === 'high_traffic' ? 'selected' : '' ?>>High Traffic Area</option>
                                    </select>
                                    <?php if (isset($errors['pandal']['location'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['pandal']['location'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="flex items-center space-x-2">
                                    <input
                                        type="checkbox"
                                        id="sound_system"
                                        name="pandal[sound_system]"
                                        <?= isset($_POST['pandal']['sound_system']) ? 'checked' : '' ?>
                                        class="h-4 w-4 text-gov-blue focus:ring-gov-blue border-gray-300 rounded"
                                        onclick="toggleSoundSystem()"
                                    />
                                    <label for="sound_system" class="gov-label m-0">Sound System Required</label>
                                </div>
                                
                                <div id="soundSystemPowerContainer" class="<?= isset($_POST['pandal']['sound_system']) ? '' : 'hidden' ?>">
                                    <label for="sound_system_power" class="gov-label">Sound System Power (kW)</label>
                                    <input
                                        type="number"
                                        id="sound_system_power"
                                        name="pandal[sound_system_power]"
                                        value="<?= htmlspecialchars($_POST['pandal']['sound_system_power'] ?? '') ?>"
                                        min="0.1"
                                        step="0.1"
                                        placeholder="Enter power rating"
                                        class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['pandal']['sound_system_power']) ? 'border-red-500' : '' ?>"
                                    />
                                    <?php if (isset($errors['pandal']['sound_system_power'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['pandal']['sound_system_power'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Personal Information Section -->
                        <div class="border-b border-gray-200 pb-6">
                            <h4 class="text-lg font-medium text-gov-darkblue mb-4">Applicant Information</h4>
                            
                            <div class="space-y-4">
                                <div>
                                    <label for="name" class="gov-label">Full Name</label>
                                    <input
                                        type="text"
                                        id="name"
                                        name="personal[name]"
                                        value="<?= htmlspecialchars($_POST['personal']['name'] ?? '') ?>"
                                        placeholder="Enter your full name"
                                        class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['personal']['name']) ? 'border-red-500' : '' ?>"
                                    />
                                    <?php if (isset($errors['personal']['name'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['personal']['name'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="mobile" class="gov-label">Mobile Number</label>
                                        <input
                                            type="text"
                                            id="mobile"
                                            name="personal[mobile]"
                                            value="<?= htmlspecialchars($_POST['personal']['mobile'] ?? '') ?>"
                                            placeholder="Enter 10-digit mobile number"
                                            class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['personal']['mobile']) ? 'border-red-500' : '' ?>"
                                        />
                                        <?php if (isset($errors['personal']['mobile'])): ?>
                                            <p class="text-red-500 text-xs mt-1"><?= $errors['personal']['mobile'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div>
                                        <label for="email" class="gov-label">Email Address</label>
                                        <input
                                            type="email"
                                            id="email"
                                            name="personal[email]"
                                            value="<?= htmlspecialchars($_POST['personal']['email'] ?? '') ?>"
                                            placeholder="Enter your email"
                                            class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['personal']['email']) ? 'border-red-500' : '' ?>"
                                        />
                                        <?php if (isset($errors['personal']['email'])): ?>
                                            <p class="text-red-500 text-xs mt-1"><?= $errors['personal']['email'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div>
                                    <label for="address" class="gov-label">Full Address</label>
                                    <textarea
                                        id="address"
                                        name="personal[address]"
                                        rows="3"
                                        placeholder="Enter your complete address"
                                        class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['personal']['address']) ? 'border-red-500' : '' ?>"
                                    ><?= htmlspecialchars($_POST['personal']['address'] ?? '') ?></textarea>
                                    <?php if (isset($errors['personal']['address'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['personal']['address'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label for="id_proof" class="gov-label">ID Proof Type</label>
                                        <select
                                            id="id_proof"
                                            name="personal[id_proof]"
                                            class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['personal']['id_proof']) ? 'border-red-500' : '' ?>"
                                        >
                                            <option value="">Select ID Proof</option>
                                            <option value="aadhar" <?= ($_POST['personal']['id_proof'] ?? '') === 'aadhar' ? 'selected' : '' ?>>Aadhar Card</option>
                                            <option value="pan" <?= ($_POST['personal']['id_proof'] ?? '') === 'pan' ? 'selected' : '' ?>>PAN Card</option>
                                            <option value="voter" <?= ($_POST['personal']['id_proof'] ?? '') === 'voter' ? 'selected' : '' ?>>Voter ID</option>
                                            <option value="driving" <?= ($_POST['personal']['id_proof'] ?? '') === 'driving' ? 'selected' : '' ?>>Driving License</option>
                                        </select>
                                        <?php if (isset($errors['personal']['id_proof'])): ?>
                                            <p class="text-red-500 text-xs mt-1"><?= $errors['personal']['id_proof'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div>
                                        <label for="id_number" class="gov-label">ID Proof Number</label>
                                        <input
                                            type="text"
                                            id="id_number"
                                            name="personal[id_number]"
                                            value="<?= htmlspecialchars($_POST['personal']['id_number'] ?? '') ?>"
                                            placeholder="Enter ID proof number"
                                            class="gov-input  w-full h-10  border-2 p-2  <?= isset($errors['personal']['id_number']) ? 'border-red-500' : '' ?>"
                                        />
                                        <?php if (isset($errors['personal']['id_number'])): ?>
                                            <p class="text-red-500 text-xs mt-1"><?= $errors['personal']['id_number'] ?></p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Estimated Fee Section -->
                        <div class="bg-gray-50 p-4 rounded-sm">
                            <h4 class="text-lg font-medium text-gov-darkblue mb-2">Estimated Fee</h4>
                            <p class="text-gray-700">Your estimated fee will be calculated after you submit the application.</p>
                            <p class="text-sm text-gray-500 mt-2">Note: The final fee amount may vary based on verification.</p>
                        </div>
                        
                        <!-- Declaration -->
                        <div class="flex items-start">
                            <input
                                type="checkbox"
                                id="declaration"
                                name="declaration"
                                required
                                class="h-4 w-4 text-gov-blue focus:ring-gov-blue border-gray-300 rounded mt-1 <?= isset($errors['declaration']) ? 'border-red-500' : '' ?>"
                            />
                            <label for="declaration" class="ml-2 block text-sm text-gray-700">
                                I hereby declare that all the information provided in this application is true and correct to the best of my knowledge. I understand that any false information may result in rejection of my application or cancellation of the permit.
                            </label>
                        </div>
                        <?php if (isset($errors['declaration'])): ?>
                            <p class="text-red-500 text-xs mt-1"><?= $errors['declaration'] ?></p>
                        <?php endif; ?>
                    </div>
                    
                    <div class="flex justify-end mt-6 space-x-3">
                        <button
                            type="button"
                            onclick="document.getElementById('applicationModal').classList.add('hidden')"
                            class="gov-btn-secondary"
                        >
                            Cancel
                        </button>
                        <button
                            type="submit"
                            class="gov-btn"
                        >
                            Submit Application
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Application Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-sm p-6 max-w-2xl w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 id="modalTitle" class="text-lg font-bold text-gov-darkblue">Application Details</h3>
                <button 
                    onclick="document.getElementById('detailsModal').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            
            <div id="modalContent" class="mb-4">
                <!-- Content will be inserted here by JavaScript -->
            </div>
            
            <div class="flex justify-end space-x-3">
                <button
                    id="payNowBtn"
                    class="gov-btn hidden"
                >
                    Pay Now
                </button>
                <button
                    onclick="document.getElementById('detailsModal').classList.add('hidden')"
                    class="gov-btn-secondary"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <script>
        function calculateArea() {
            const length = parseFloat(document.getElementById('length').value) || 0;
            const width = parseFloat(document.getElementById('width').value) || 0;
            document.getElementById('area').value = (length * width).toFixed(2);
        }
        
        function toggleSoundSystem() {
            const container = document.getElementById('soundSystemPowerContainer');
            if (document.getElementById('sound_system').checked) {
                container.classList.remove('hidden');
            } else {
                container.classList.add('hidden');
            }
        }
        
        function showApplicationDetails(appJson) {
            const app = JSON.parse(appJson);
            document.getElementById('modalTitle').textContent = `Application: ${app.application_number}`;
            
            const content = `
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Festival Details</h4>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm"><span class="font-medium">Type:</span> ${app.festival_type}</p>
                            <p class="text-sm"><span class="font-medium">Location:</span> ${app.location_type}</p>
                            <p class="text-sm"><span class="font-medium">Address:</span> ${app.address}</p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Dates</h4>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm"><span class="font-medium">Submission:</span> ${new Date(app.created_at).toLocaleDateString()}</p>
                            <p class="text-sm"><span class="font-medium">Festival:</span> ${new Date(app.start_date).toLocaleDateString()} to ${new Date(app.end_date).toLocaleDateString()}</p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Status</h4>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm">
                                <span class="font-medium">Application:</span> 
                                <span class="px-2 py-1 text-xs font-medium rounded-sm border ${getStatusBadgeClass(app.status)}">
                                    ${app.status.charAt(0).toUpperCase() + app.status.slice(1)}
                                </span>
                            </p>
                            <p class="text-sm">
                                
                            </p>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-700">Financial</h4>
                        <div class="mt-2 space-y-1">
                            <p class="text-sm"><span class="font-medium">Fee Amount:</span> ₹${parseFloat(app.fee_amount).toFixed(2)}</p>
                        </div>
                    </div>
                    
                    <div class="col-span-2">
                        <h4 class="text-sm font-medium text-gray-700">Pandal Details</h4>
                        <div class="mt-2 grid grid-cols-3 gap-2">
                            <p class="text-sm"><span class="font-medium">Area:</span> ${app.area} m²</p>
                            <p class="text-sm"><span class="font-medium">Duration:</span> ${app.duration} days</p>
                        </div>
                    </div>
                </div>
            `;
            
            document.getElementById('modalContent').innerHTML = content;
            
            const payNowBtn = document.getElementById('payNowBtn');
            if (app.payment_status === 'pending' && app.status === 'approved') {
                payNowBtn.classList.remove('hidden');
                payNowBtn.onclick = function() {
                    window.location.href = `payment.php?appId=${app.id}`;
                };
            } else {
                payNowBtn.classList.add('hidden');
            }
            
            document.getElementById('detailsModal').classList.remove('hidden');
        }
        
        function getStatusBadgeClass(status) {
            switch(status) {
                case 'approved': return 'bg-green-100 text-green-800 border-green-200';
                case 'rejected': return 'bg-red-100 text-red-800 border-red-200';
                case 'pending':
                default: return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            }
        }
        
        function getPaymentStatusBadgeClass(status) {
            switch(status) {
                case 'paid': return 'bg-green-100 text-green-800 border-green-200';
                case 'pending': return 'bg-yellow-100 text-yellow-800 border-yellow-200';
                case 'not_required':
                default: return 'bg-gray-100 text-gray-800 border-gray-200';
            }
        }
    </script>
</body>
</html>
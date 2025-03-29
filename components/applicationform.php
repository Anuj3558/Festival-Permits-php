<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Initialize form data if not set
if (!isset($_SESSION['formData'])) {
    $_SESSION['formData'] = [
        'length' => 0,
        'width' => 0,
        'height' => 0,
        'area' => 0,
        'duration' => 5,
        'soundSystem' => false,
        'soundSystemPower' => 0,
        'location' => 'residential',
        'festival' => 'ganpati',
        'name' => '',
        'organization' => '',
        'mobile' => '',
        'email' => '',
        'address' => '',
        'idProof' => '',
        'idNumber' => '',
        'feeCalculated' => false
    ];
}

// Initialize errors
$errors = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];
    
    if (isset($_POST['calculateFee'])) {
        // Validate only the fields needed for fee calculation
        if (empty($_POST['length']) || $_POST['length'] <= 0) {
            $errors['length'] = 'Length is required and must be greater than 0';
        }
        
        if (empty($_POST['width']) || $_POST['width'] <= 0) {
            $errors['width'] = 'Width is required and must be greater than 0';
        }
        
        if (empty($_POST['duration']) || $_POST['duration'] <= 0) {
            $errors['duration'] = 'Duration is required and must be greater than 0';
        }
        
        if (isset($_POST['soundSystem']) && (empty($_POST['soundSystemPower']) || $_POST['soundSystemPower'] <= 0)) {
            $errors['soundSystemPower'] = 'Sound system power is required when sound system is enabled';
        }
        
        if (empty($errors)) {
            $_SESSION['formData'] = [
                'length' => (float)$_POST['length'],
                'width' => (float)$_POST['width'],
                'height' => (float)$_POST['height'],
                'area' => (float)$_POST['length'] * (float)$_POST['width'],
                'duration' => (int)$_POST['duration'],
                'soundSystem' => isset($_POST['soundSystem']),
                'soundSystemPower' => isset($_POST['soundSystem']) ? (float)$_POST['soundSystemPower'] : 0,
                'location' => $_POST['location'],
                'festival' => $_POST['festival'],
                'name' => $_POST['name'] ?? $_SESSION['formData']['name'],
                'organization' => $_POST['organization'] ?? $_SESSION['formData']['organization'],
                'mobile' => $_POST['mobile'] ?? $_SESSION['formData']['mobile'],
                'email' => $_POST['email'] ?? $_SESSION['formData']['email'],
                'address' => $_POST['address'] ?? $_SESSION['formData']['address'],
                'idProof' => $_POST['idProof'] ?? $_SESSION['formData']['idProof'],
                'idNumber' => $_POST['idNumber'] ?? $_SESSION['formData']['idNumber'],
                'feeCalculated' => true
            ];
        }
    } elseif (isset($_POST['submit'])) {
        // Validate all fields for final submission
        if (empty($_POST['length']) || $_POST['length'] <= 0) {
            $errors['length'] = 'Length is required and must be greater than 0';
        }
        
        if (empty($_POST['width']) || $_POST['width'] <= 0) {
            $errors['width'] = 'Width is required and must be greater than 0';
        }
        
        if (empty($_POST['height']) || $_POST['height'] <= 0) {
            $errors['height'] = 'Height is required and must be greater than 0';
        }
        
        if (empty($_POST['duration']) || $_POST['duration'] <= 0) {
            $errors['duration'] = 'Duration is required and must be greater than 0';
        }
        
        if (isset($_POST['soundSystem']) && (empty($_POST['soundSystemPower']) || $_POST['soundSystemPower'] <= 0)) {
            $errors['soundSystemPower'] = 'Sound system power is required when sound system is enabled';
        }
        
        if (empty($_POST['name'])) {
            $errors['name'] = 'Name is required';
        }
        
        if (empty($_POST['mobile'])) {
            $errors['mobile'] = 'Mobile number is required';
        } elseif (!preg_match('/^\d{10}$/', $_POST['mobile'])) {
            $errors['mobile'] = 'Mobile number must be 10 digits';
        }
        
        if (empty($_POST['email'])) {
            $errors['email'] = 'Email is required';
        } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email is invalid';
        }
        
        if (empty($_POST['address'])) {
            $errors['address'] = 'Address is required';
        }
        
        if (empty($_POST['idProof'])) {
            $errors['idProof'] = 'ID proof type is required';
        }
        
        if (empty($_POST['idNumber'])) {
            $errors['idNumber'] = 'ID number is required';
        }
        if (empty($errors)) {
            try {
                $db = (new Database())->connect();
                
                // Calculate fees
                $baseFee = $_SESSION['formData']['area'] * $_SESSION['formData']['duration'] * 10;
                $soundFee = $_SESSION['formData']['soundSystem'] ? ($_SESSION['formData']['soundSystemPower'] * 50) : 0;
                $totalFee = $baseFee + $soundFee;
                
                $stmt = $db->prepare("INSERT INTO applications (
                    application_number, user_id, festival_type, length, width, height, area, 
                    duration, location_type, sound_system, sound_system_power, applicant_name, 
                    organization, mobile, email, address, id_proof_type, id_proof_number, fee_amount
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
                
                $stmt->execute([
                    $applicationNumber,
                    $_SESSION['user']['id'],
                    $_SESSION['formData']['festival'],
                    $_SESSION['formData']['length'],
                    $_SESSION['formData']['width'],
                    $_SESSION['formData']['height'],
                    $_SESSION['formData']['area'],
                    $_SESSION['formData']['duration'],
                    $_SESSION['formData']['location'],
                    $_SESSION['formData']['soundSystem'] ? 1 : 0,
                    $_SESSION['formData']['soundSystemPower'],
                    $_SESSION['formData']['name'],
                    $_SESSION['formData']['organization'],
                    $_SESSION['formData']['mobile'],
                    $_SESSION['formData']['email'],
                    $_SESSION['formData']['address'],
                    $_SESSION['formData']['idProof'],
                    $_SESSION['formData']['idNumber'],
                    $totalFee
                ]);
                
                // Add status history
                $appId = $db->lastInsertId();
                $stmt = $db->prepare("INSERT INTO application_status_history (application_id, status) VALUES (?, 'pending')");
                $stmt->execute([$appId]);
                
                // Clear session data
                unset($_SESSION['formData']);
                
                // Show success message
                $successMessage = "Application submitted successfully! Your application number is: $applicationNumber";
                
            } catch (PDOException $e) {
                error_log("Database Error: " . $e->getMessage());
                $errors[] = "Failed to submit application. Please try again.";
            }
        }
    }
}

// Calculate area if not set
if (!isset($_SESSION['formData']['area']) && $_SESSION['formData']['length'] > 0 && $_SESSION['formData']['width'] > 0) {
    $_SESSION['formData']['area'] = $_SESSION['formData']['length'] * $_SESSION['formData']['width'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Permit Application</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .bg-gov-blue {
            background-color: #1e40af;
        }
        .text-gov-darkblue {
            color: #1e3a8a;
        }
        .gov-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        .gov-input {
            display: block;
            width: 100%;
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            background-color: #fff;
        }
        .gov-input:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .gov-btn {
            padding: 0.5rem 1.5rem;
            background-color: #1e40af;
            color: white;
            font-weight: 500;
            cursor: pointer;
        }
        .gov-btn:hover {
            background-color: #1e3a8a;
        }
        .gov-btn-secondary {
            padding: 0.5rem 1.5rem;
            background-color: #f3f4f6;
            color: #374151;
            font-weight: 500;
            border-radius: 0.25rem;
            cursor: pointer;
        }
        .gov-btn-secondary:hover {
            background-color: #e5e7eb;
        }
        .gov-btn-disabled {
            padding: 0.5rem 1.5rem;
            background-color: #e5e7eb;
            color: #9ca3af;
            font-weight: 500;
            cursor: not-allowed;
        }
    </style>
    <script>
        function calculateArea() {
            const length = parseFloat(document.getElementById('length').value) || 0;
            const width = parseFloat(document.getElementById('width').value) || 0;
            const area = length * width;
            document.getElementById('area').value = area.toFixed(2);
        }
        
        function toggleSoundSystem() {
            const soundSystem = document.getElementById('soundSystem');
            const soundSystemPowerDiv = document.getElementById('soundSystemPowerDiv');
            if (soundSystem.checked) {
                soundSystemPowerDiv.style.display = 'block';
            } else {
                soundSystemPowerDiv.style.display = 'none';
            }
        }
    </script>
</head>
<body class="bg-gray-100">
    <div class="container mx-auto py-8 px-4">
        <div class="max-w-4xl mx-auto bg-white border border-gray-200 -sm shadow-sm p-6 animate-fade-in">
            <h1 class="text-2xl font-bold text-gov-darkblue mb-6">Festival Permit Application</h1>
            
            <?php if (isset($successMessage)): ?>
                <div class="mb-6 p-4 bg-green-100 border border-green-200 text-green-700 ">
                    <?= htmlspecialchars($successMessage) ?>
                </div>
            <?php endif; ?>
            
            <form method="post">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Left Column - Pandal Details -->
                    <div>
                        <h2 class="text-xl font-semibold text-gov-darkblue mb-4 pb-2 border-b">Pandal Details</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label for="festival" class="gov-label">Festival Type</label>
                                <select id="festival" name="festival" class="gov-input">
                                    <option value="ganpati" <?= $_SESSION['formData']['festival'] === 'ganpati' ? 'selected' : '' ?>>Ganpati Festival</option>
                                    <option value="durgapuja" <?= $_SESSION['formData']['festival'] === 'durgapuja' ? 'selected' : '' ?>>Durga Puja</option>
                                </select>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label for="length" class="gov-label">Length (m)</label>
                                    <input type="number" id="length" name="length" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['length'] ?? '0') ?>" 
                                           min="1" step="0.01" class="gov-input <?= isset($errors['length']) ? 'border-red-500' : '' ?>"
                                           onchange="calculateArea()">
                                    <?php if (isset($errors['length'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['length'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <label for="width" class="gov-label">Width (m)</label>
                                    <input type="number" id="width" name="width" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['width'] ?? '0') ?>" 
                                           min="1" step="0.01" class="gov-input <?= isset($errors['width']) ? 'border-red-500' : '' ?>"
                                           onchange="calculateArea()">
                                    <?php if (isset($errors['width'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['width'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <label for="height" class="gov-label">Height (m)</label>
                                    <input type="number" id="height" name="height" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['height'] ?? '0') ?>" 
                                           min="1" step="0.01" class="gov-input <?= isset($errors['height']) ? 'border-red-500' : '' ?>">
                                    <?php if (isset($errors['height'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['height'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label for="area" class="gov-label">Total Area (m²)</label>
                                <input type="number" id="area" name="area" 
                                       value="<?= number_format($_SESSION['formData']['area'], 2) ?>" 
                                       readonly class="gov-input bg-gray-50">
                                <p class="text-xs text-gray-500 mt-1">Automatically calculated from length and width</p>
                            </div>
                            
                            <div>
                                <label for="duration" class="gov-label">Duration (days)</label>
                                <input type="number" id="duration" name="duration" 
                                       value="<?= htmlspecialchars($_SESSION['formData']['duration']) ?>" 
                                       min="1" max="15" class="gov-input <?= isset($errors['duration']) ? 'border-red-500' : '' ?>">
                                <?php if (isset($errors['duration'])): ?>
                                    <p class="text-red-500 text-xs mt-1"><?= $errors['duration'] ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <label for="location" class="gov-label">Location Type</label>
                                <select id="location" name="location" class="gov-input">
                                    <option value="residential" <?= $_SESSION['formData']['location'] === 'residential' ? 'selected' : '' ?>>Residential Area</option>
                                    <option value="commercial" <?= $_SESSION['formData']['location'] === 'commercial' ? 'selected' : '' ?>>Commercial Area</option>
                                    <option value="highTraffic" <?= $_SESSION['formData']['location'] === 'highTraffic' ? 'selected' : '' ?>>High Traffic Area</option>
                                </select>
                            </div>
                            
                            <div class="flex items-center space-x-2">
                                <input type="checkbox" id="soundSystem" name="soundSystem" 
                                       value="1" <?= $_SESSION['formData']['soundSystem'] ? 'checked' : '' ?> 
                                       class="h-4 w-4 text-gov-blue focus:ring-gov-blue border-gray-300 "
                                       onclick="toggleSoundSystem()">
                                <label for="soundSystem" class="gov-label m-0">Sound System Required</label>
                            </div>
                            
                            <div id="soundSystemPowerDiv" style="<?= $_SESSION['formData']['soundSystem'] ? 'display: block;' : 'display: none;' ?>">
                                <label for="soundSystemPower" class="gov-label">Sound System Power (kW)</label>
                                <input type="number" id="soundSystemPower" name="soundSystemPower" 
                                       value="<?= htmlspecialchars($_SESSION['formData']['soundSystemPower']) ?>" 
                                       min="0.1" step="0.1" class="gov-input <?= isset($errors['soundSystemPower']) ? 'border-red-500' : '' ?>">
                                <?php if (isset($errors['soundSystemPower'])): ?>
                                    <p class="text-red-500 text-xs mt-1"><?= $errors['soundSystemPower'] ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Right Column - Applicant Information -->
                    <div>
                        <h2 class="text-xl font-semibold text-gov-darkblue mb-4 pb-2 border-b">Applicant Information</h2>
                        
                        <div class="space-y-4">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="name" class="gov-label">Applicant Name</label>
                                    <input type="text" id="name" name="name" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['name'] ?? '') ?>" 
                                           class="gov-input <?= isset($errors['name']) ? 'border-red-500' : '' ?>">
                                    <?php if (isset($errors['name'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['name'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <label for="organization" class="gov-label">Organization Name (if applicable)</label>
                                    <input type="text" id="organization" name="organization" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['organization'] ?? '') ?>" 
                                           class="gov-input">
                                </div>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="mobile" class="gov-label">Mobile Number</label>
                                    <input type="text" id="mobile" name="mobile" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['mobile'] ?? '') ?>" 
                                           class="gov-input <?= isset($errors['mobile']) ? 'border-red-500' : '' ?>">
                                    <?php if (isset($errors['mobile'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['mobile'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <label for="email" class="gov-label">Email Address</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['email'] ?? '') ?>" 
                                           class="gov-input <?= isset($errors['email']) ? 'border-red-500' : '' ?>">
                                    <?php if (isset($errors['email'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['email'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div>
                                <label for="address" class="gov-label">Complete Address</label>
                                <textarea id="address" name="address" rows="3" 
                                          class="gov-input <?= isset($errors['address']) ? 'border-red-500' : '' ?>"><?= htmlspecialchars($_SESSION['formData']['address'] ?? '') ?></textarea>
                                <?php if (isset($errors['address'])): ?>
                                    <p class="text-red-500 text-xs mt-1"><?= $errors['address'] ?></p>
                                <?php endif; ?>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <label for="idProof" class="gov-label">ID Proof Type</label>
                                    <select id="idProof" name="idProof" 
                                            class="gov-input <?= isset($errors['idProof']) ? 'border-red-500' : '' ?>">
                                        <option value="">Select ID Proof</option>
                                        <option value="aadhar" <?= isset($_SESSION['formData']['idProof']) && $_SESSION['formData']['idProof'] === 'aadhar' ? 'selected' : '' ?>>Aadhar Card</option>
                                        <option value="pan" <?= isset($_SESSION['formData']['idProof']) && $_SESSION['formData']['idProof'] === 'pan' ? 'selected' : '' ?>>PAN Card</option>
                                        <option value="voter" <?= isset($_SESSION['formData']['idProof']) && $_SESSION['formData']['idProof'] === 'voter' ? 'selected' : '' ?>>Voter ID</option>
                                        <option value="driving" <?= isset($_SESSION['formData']['idProof']) && $_SESSION['formData']['idProof'] === 'driving' ? 'selected' : '' ?>>Driving License</option>
                                        <option value="passport" <?= isset($_SESSION['formData']['idProof']) && $_SESSION['formData']['idProof'] === 'passport' ? 'selected' : '' ?>>Passport</option>
                                    </select>
                                    <?php if (isset($errors['idProof'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['idProof'] ?></p>
                                    <?php endif; ?>
                                </div>
                                
                                <div>
                                    <label for="idNumber" class="gov-label">ID Number</label>
                                    <input type="text" id="idNumber" name="idNumber" 
                                           value="<?= htmlspecialchars($_SESSION['formData']['idNumber'] ?? '') ?>" 
                                           class="gov-input <?= isset($errors['idNumber']) ? 'border-red-500' : '' ?>">
                                    <?php if (isset($errors['idNumber'])): ?>
                                        <p class="text-red-500 text-xs mt-1"><?= $errors['idNumber'] ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Fee Estimate -->
                <div class="mt-8 p-4 bg-gray-50 border border-gray-200 -sm">
                    <h3 class="text-lg font-bold text-gov-darkblue mb-2">Estimated Fees</h3>
                    <?php if ($_SESSION['formData']['feeCalculated']): ?>
                        <p class="text-gray-600">
                            Total area: <?= number_format($_SESSION['formData']['area'], 2) ?> m² × 
                            Duration: <?= $_SESSION['formData']['duration'] ?> days = 
                            ₹<?= number_format($_SESSION['formData']['area'] * $_SESSION['formData']['duration'] * 10, 2) ?>
                            (estimated at ₹10 per m² per day)
                        </p>
                        <?php if ($_SESSION['formData']['soundSystem']): ?>
                            <p class="text-gray-600 mt-2">
                                Sound system: <?= $_SESSION['formData']['soundSystemPower'] ?> kW × ₹50 per kW = 
                                ₹<?= number_format($_SESSION['formData']['soundSystemPower'] * 50, 2) ?>
                            </p>
                        <?php endif; ?>
                        <p class="font-bold mt-2">
                            Total Estimated Fees: ₹<?= number_format(
                                ($_SESSION['formData']['area'] * $_SESSION['formData']['duration'] * 10) + 
                                ($_SESSION['formData']['soundSystem'] ? ($_SESSION['formData']['soundSystemPower'] * 50) : 0), 
                            2) ?>
                        </p>
                    <?php else: ?>
                        <p class="text-gray-600">Please calculate fees to see the estimated cost</p>
                    <?php endif; ?>
                    
                    <div class="mt-4 flex justify-between">
                        <button type="submit" name="calculateFee" class="gov-btn-secondary">
                            Calculate Fee
                        </button>
                        
                        <button type="submit" name="submit" class="<?= $_SESSION['formData']['feeCalculated'] ? 'gov-btn' : 'gov-btn-disabled' ?>" 
                                <?= $_SESSION['formData']['feeCalculated'] ? '' : 'disabled' ?>>
                            Submit Application
                        </button>
                    </div>
                </div>
                
                <!-- Terms -->
                <div class="mt-6 p-4 bg-blue-50 border border-blue-100 -sm">
                    <p class="text-sm text-gray-700">
                        By submitting this application, you confirm that all provided information is accurate and complete. 
                        You agree to comply with all local regulations regarding festival celebrations.
                    </p>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        // Initialize the sound system power div visibility
        toggleSoundSystem();
    </script>
</body>
</html>
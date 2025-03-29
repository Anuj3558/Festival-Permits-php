<?php
require_once __DIR__ . '/../config/database.php';

// Initialize variables to prevent undefined variable warnings
$applicationId = '';
$mobileNumber = '';
$error = '';
$applicationStatus = null;
$history = []; // Added missing history variable initialization

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $applicationId = trim($_POST['applicationId'] ?? '');
    $mobileNumber = trim($_POST['mobileNumber'] ?? '');
    
    // Validate inputs
    if (empty($applicationId)) {
        $error = 'Please enter an application ID';
    } elseif (empty($mobileNumber) || !preg_match('/^\d{10}$/', $mobileNumber)) {
        $error = 'Please enter a valid 10-digit mobile number';
    } else {
        try {
            $db = (new Database())->connect();
            
            $stmt = $db->prepare("
                SELECT a.*
                FROM applications a
                JOIN users u ON a.user_id = u.id
                WHERE a.application_number = ? 
            ");
            $stmt->execute([$applicationId]);
            $application = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($application) {
                // Get status history
                $historyStmt = $db->prepare("
                    SELECT status, created_at 
                    FROM application_status_history 
                    WHERE application_id = ? 
                    ORDER BY created_at ASC
                ");
                $historyStmt->execute([$application['id']]);
                $history = $historyStmt->fetchAll(PDO::FETCH_ASSOC);
                
                // Format status for display
                $applicationStatus = [
                    'id' => $application['application_number'],
                    'status' => $application['status'],
                    'applicantName' => $application['applicant_name'],
                    'festival' => ucfirst(str_replace('_', ' ', $application['festival_type'])),
                    'submittedDate' => date('d M Y', strtotime($application['created_at'])),
                    'lastUpdated' => date('d M Y', strtotime($application['updated_at'])),
                    'steps' => [],
                    'comments' => $application['comments'] ?? null // Added comments field
                ];
                
                // Add status steps
                $statuses = ['pending', 'under_review', 'approved', 'rejected'];
                $currentStatusIndex = array_search($application['status'], $statuses);
                
                foreach ($statuses as $index => $status) {
                    $step = [
                        'label' => ucfirst(str_replace('_', ' ', $status)),
                        'status' => $index < $currentStatusIndex ? 'completed' : 
                                    ($index === $currentStatusIndex ? 'current' : 'upcoming')
                    ];
                    
                    // Find if this status exists in history
                    foreach ($history as $h) {
                        if ($h['status'] === $status) {
                            $step['date'] = date('d M Y', strtotime($h['created_at']));
                            break;
                        }
                    }
                    
                    $applicationStatus['steps'][] = $step;
                }
                
            } else {
                $error = 'No application found with the provided details';
            }
            
        } catch (PDOException $e) {
            error_log("Database Error: " . $e->getMessage());
            echo '<script>console.error("Database Error: ' . addslashes($e->getMessage()) . '");</script>';
            $error = 'Failed to fetch application details. Please try again.';
        }
    }
}

function getStatusIcon($status) {
    switch ($status) {
        case 'completed':
            return '<svg class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>';
        case 'current':
            return '<svg class="h-5 w-5 text-yellow-500 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>';
        case 'upcoming':
            return '<svg class="h-5 w-5 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>';
        default:
            return '';
    }
}

function getStatusBadge($status) {
    switch ($status) {
        case 'pending':
            return '<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded-none sm text-xs font-medium">Pending</span>';
        case 'under_review': // Fixed to match database value
            return '<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-none sm text-xs font-medium">Under Review</span>';
        case 'approved':
            return '<span class="bg-green-100 text-green-800 px-2 py-1 rounded-none sm text-xs font-medium">Approved</span>';
        case 'rejected':
            return '<span class="bg-red-100 text-red-800 px-2 py-1 rounded-none sm text-xs font-medium">Rejected</span>';
        default:
            return '<span class="bg-gray-100 text-gray-800 px-2 py-1 rounded-none sm text-xs font-medium">Unknown</span>';
    }
}
?>

<div class="animate-fade-in">
  <?php if (!empty($error)): ?>
    <div class="bg-red-50 border-l-4 border-red-400 p-4 mb-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm text-red-700"><?php echo htmlspecialchars($error); ?></p>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="bg-white border border-gray-200 rounded-none lg shadow-md p-8">
    <h3 class="text-2xl font-bold text-gov-darkblue mb-6 text-center">Track Your Application</h3>
    
    <form method="POST" action="" class="space-y-6">
        <div class="relative">
            <input
                type="text"
                id="applicationId"
                name="applicationId"
                value="<?php echo htmlspecialchars($applicationId); ?>"
                placeholder=" "
                class="peer w-full px-4 py-3 border border-gray-300 rounded-none md 
                       focus:outline-none focus:ring-2 focus:ring-gov-blue 
                       focus:border-transparent text-gray-700 
                       transition duration-300 ease-in-out"
                required
            />
            <label 
                for="applicationId"
                class="absolute left-3 top-0 px-1 text-gray-500 bg-white 
                       transform -translate-y-1/2 transition-all duration-300 
                       peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base 
                       peer-focus:top-0 peer-focus:text-xs peer-focus:text-gov-blue 
                       peer-focus:bg-white"
            >
                Application ID
            </label>
            <span class="text-xs text-gray-500 mt-1 block">
                Enter your unique application ID (e.g., FEST123456)
            </span>
        </div>
        
        <div class="relative">
            <input
                type="tel"
                id="mobileNumber"
                name="mobileNumber"
                value="<?php echo htmlspecialchars($mobileNumber); ?>"
                placeholder=" "
                pattern="[0-9]{10}"
                maxlength="10"
                class="peer w-full px-4 py-3 border border-gray-300 rounded-none md 
                       focus:outline-none focus:ring-2 focus:ring-gov-blue 
                       focus:border-transparent text-gray-700 
                       transition duration-300 ease-in-out"
                required
            />
            <label 
                for="mobileNumber"
                class="absolute left-3 top-0 px-1 text-gray-500 bg-white 
                       transform -translate-y-1/2 transition-all duration-300 
                       peer-placeholder-shown:top-1/2 peer-placeholder-shown:text-base 
                       peer-focus:top-0 peer-focus:text-xs peer-focus:text-gov-blue 
                       peer-focus:bg-white"
            >
                Registered Mobile Number
            </label>
            <span class="text-xs text-gray-500 mt-1 block">
                Enter your 10-digit mobile number
            </span>
        </div>
        
        <button
            type="submit"
            class="w-full bg-gov-blue text-white py-3 rounded-none md 
                   hover:bg-gov-darkblue transition duration-300 
                   ease-in-out transform hover:scale-[1.02] 
                   focus:outline-none focus:ring-2 focus:ring-offset-2 
                   focus:ring-gov-blue"
        >
            Track Application
        </button>
    </form>
  </div>
  
  <?php if ($applicationStatus !== null): ?>
    <div class="bg-white border border-gray-200 rounded-none sm shadow-sm p-6 animate-slide-up">
      <div class="flex flex-wrap items-start justify-between mb-6">
        <div>
          <h3 class="text-lg font-bold text-gov-darkblue">Application Status</h3>
          <p class="text-sm text-gray-600">Application ID: <?php echo htmlspecialchars($applicationStatus['id']); ?></p>
        </div>
        <?php echo getStatusBadge($applicationStatus['status']); ?>
      </div>
      
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div>
          <h4 class="text-sm font-medium text-gray-600 mb-1">Applicant</h4>
          <p class="font-medium"><?php echo htmlspecialchars($applicationStatus['applicantName']); ?></p>
        </div>
        
        <div>
          <h4 class="text-sm font-medium text-gray-600 mb-1">Festival</h4>
          <p class="font-medium"><?php echo htmlspecialchars($applicationStatus['festival']); ?></p>
        </div>
        
        <div>
          <h4 class="text-sm font-medium text-gray-600 mb-1">Submitted On</h4>
          <p class="font-medium"><?php echo htmlspecialchars($applicationStatus['submittedDate']); ?></p>
        </div>
        
        <div>
          <h4 class="text-sm font-medium text-gray-600 mb-1">Last Updated</h4>
          <p class="font-medium"><?php echo htmlspecialchars($applicationStatus['lastUpdated']); ?></p>
        </div>
      </div>
      
      <div class="mb-6">
        <h4 class="text-sm font-medium text-gray-600 mb-4">Application Progress</h4>
        
        <div class="space-y-6">
          <?php foreach ($applicationStatus['steps'] as $index => $step): ?>
            <div class="flex">
              <div class="flex items-center justify-center h-10 w-10 shrink-0">
                <?php echo getStatusIcon($step['status']); ?>
              </div>
              
              <div class="ml-3">
                <div class="flex items-center">
                  <p class="<?php 
                    echo $step['status'] === 'completed' ? 'text-green-700' : 
                         ($step['status'] === 'current' ? 'text-yellow-700' : 'text-gray-500');
                  ?> font-medium">
                    <?php echo htmlspecialchars($step['label']); ?>
                  </p>
                  <?php if (isset($step['date'])): ?>
                    <span class="ml-2 text-xs text-gray-500"><?php echo htmlspecialchars($step['date']); ?></span>
                  <?php endif; ?>
                </div>
                
                <?php if ($index < count($applicationStatus['steps']) - 1): ?>
                  <div class="<?php 
                    echo $step['status'] === 'completed' ? 'bg-green-200' : 'bg-gray-200';
                  ?> ml-4 mt-1 mb-1 h-12 w-0.5"></div>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      
      <?php if (!empty($applicationStatus['comments'])): ?>
        <div class="p-4 bg-yellow-50 border border-yellow-100 rounded-none sm flex items-start">
          <svg class="h-5 w-5 text-yellow-500 mr-2 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
          <div>
            <h4 class="text-sm font-medium text-yellow-800 mb-1">Official Comments</h4>
            <p class="text-sm text-yellow-700"><?php echo htmlspecialchars($applicationStatus['comments']); ?></p>
          </div>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>
</div>
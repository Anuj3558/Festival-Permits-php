<?php
session_start();
require_once __DIR__ . '/auth/functions.php';

if (!isAuthenticated() || getUserRole() !== 'admin') {
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

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $id = $_POST['id'];
    $newStatus = $_POST['status'];
    $comments = $_POST['comments'] ?? '';
    
    try {
        // Update application status
        $stmt = $db->prepare("UPDATE applications SET status = ? WHERE id = ?");
        $stmt->execute([$newStatus, $id]);
        
        // Add to status history
        $stmt = $db->prepare("
            INSERT INTO application_status_history 
            (application_id, status, comments, changed_by) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $id, 
            $newStatus, 
            $comments, 
            $_SESSION['user']['id']
        ]);
        
        $_SESSION['message'] = [
            'type' => 'success',
            'text' => "Application status updated to " . ucfirst($newStatus)
        ];
        
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        $_SESSION['message'] = [
            'type' => 'error',
            'text' => "Failed to update application status"
        ];
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Get filter from query parameter
$filter = $_GET['filter'] ?? 'all';

// Fetch applications with additional details
try {
    $query = "
        SELECT 
            a.*,
            u.full_name as applicant_name,
            u.email as applicant_email
        FROM applications a
        JOIN users u ON a.user_id = u.id
    ";
    $params = [];
    
    if ($filter !== 'all') {
        $query .= " WHERE a.status = ?";
        $params[] = $filter;
    }
    
    $query .= " ORDER BY a.created_at DESC";
    
    $stmt = $db->prepare($query);
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    echo '<script>console.error("Database Error: ' . addslashes($e->getMessage()) . '");</script>';
    $applications = [];
}

// Get status badge class
function getStatusBadgeClass($status) {
    switch($status) {
        case 'approved':
            return 'bg-green-100 text-green-800 border-green-200';
        case 'rejected':
            return 'bg-red-100 text-red-800 border-red-200';
        case 'under_review':
            return 'bg-blue-100 text-blue-800 border-blue-200';
        case 'pending':
        default:
            return 'bg-yellow-100 text-yellow-800 border-yellow-200';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Festival Permits</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .bg-gov-blue {
            background-color: #1e40af;
        }
        .text-gov-darkblue {
            color: #1e3a8a;
        }
        .animate-fade-in {
            animation: fadeIn 0.5s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .loading-spinner {
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-gray-50">
    
    <main class="flex-grow">
        <section class="bg-gov-blue text-white py-6 md:py-8">
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                    <div>
                        <h1 class="text-2xl md:text-3xl font-bold">Admin Dashboard</h1>
                        <p class="text-sm md:text-base mt-1">
                            Welcome back, <?= htmlspecialchars($_SESSION['user']['full_name']) ?>
                        </p>
                    </div>
                    <div class="mt-4 md:mt-0 flex space-x-2">
                        <a href="?filter=all" class="inline-flex items-center px-4 py-2 bg-white text-gov-blue hover:bg-gray-100 rounded-sm text-sm font-medium">
                            <i class="fas fa-sync-alt mr-2"></i> Refresh
                        </a>
                        <a href="?action=logout" class="inline-flex items-center px-4 py-2 bg-transparent border border-white text-white hover:bg-white hover:text-gov-blue rounded-sm text-sm font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i> Logout
                        </a>
                    </div>
                </div>
            </div>
        </section>
        
        <?php if (isset($_SESSION['message'])): ?>
            <div class="container mx-auto px-4 mt-4 animate-fade-in">
                <div class="p-4 mb-4 text-sm text-<?= $_SESSION['message']['type'] === 'success' ? 'green' : 'red' ?>-700 bg-<?= $_SESSION['message']['type'] === 'success' ? 'green' : 'red' ?>-100 rounded-sm">
                    <?= htmlspecialchars($_SESSION['message']['text']) ?>
                </div>
            </div>
            <?php unset($_SESSION['message']); ?>
        <?php endif; ?>
        
        <section class="py-6 md:py-8">
            <div class="container mx-auto px-4">
                <div class="bg-white border border-gray-200 rounded-sm shadow-sm">
                    <div class="p-4 border-b border-gray-200 flex flex-col md:flex-row md:items-center md:justify-between">
                        <h2 class="text-lg font-bold text-gov-darkblue">Festival Permit Applications</h2>
                        <div class="mt-2 md:mt-0">
                            <div class="flex flex-wrap gap-2">
                                <a href="?filter=all" class="px-4 py-2 text-sm font-medium rounded-sm <?= $filter === 'all' ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300' ?>">
                                    All
                                </a>
                                <a href="?filter=pending" class="px-4 py-2 text-sm font-medium rounded-sm <?= $filter === 'pending' ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300' ?>">
                                    Pending
                                </a>
                                <a href="?filter=under_review" class="px-4 py-2 text-sm font-medium rounded-sm <?= $filter === 'under_review' ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300' ?>">
                                    Under Review
                                </a>
                                <a href="?filter=approved" class="px-4 py-2 text-sm font-medium rounded-sm <?= $filter === 'approved' ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300' ?>">
                                    Approved
                                </a>
                                <a href="?filter=rejected" class="px-4 py-2 text-sm font-medium rounded-sm <?= $filter === 'rejected' ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300' ?>">
                                    Rejected
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 text-left">
                                <tr>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">App ID</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Applicant</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Festival</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Location</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Duration</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Area</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Fee (₹)</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Status</th>
                                    <th class="px-4 py-3 text-sm font-semibold text-gray-700">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php if (count($applications) > 0): ?>
                                    <?php foreach ($applications as $app): ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <span class="font-medium"><?= htmlspecialchars($app['application_number']) ?></span>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <?= date('d M Y', strtotime($app['created_at'])) ?>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <div><?= htmlspecialchars($app['applicant_name']) ?></div>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <?= htmlspecialchars($app['applicant_email']) ?>
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= ucfirst(htmlspecialchars($app['festival_type'])) ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= htmlspecialchars($app['location_type']) ?>
                                                <div class="text-xs text-gray-500 mt-1">
                                                    <?= substr(htmlspecialchars($app['address']), 0, 20) ?>...
                                                </div>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= htmlspecialchars($app['duration']) ?> days
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= number_format($app['area'], 2) ?> m²
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-700">
                                                <?= number_format($app['fee_amount'], 2) ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <span class="px-2 py-1 text-xs font-medium rounded-sm border <?= getStatusBadgeClass($app['status']) ?>">
                                                    <?= ucfirst(str_replace('_', ' ', $app['status'])) ?>
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm">
                                                <div class="flex space-x-1">
                                                    <button 
                                                        onclick="showDetails('<?= htmlspecialchars($app['id']) ?>')"
                                                        class="p-1 text-gray-600 hover:text-gov-blue"
                                                        title="View Details"
                                                    >
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($app['status'] !== 'approved'): ?>
                                                        <form method="post" class="inline">
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars($app['id']) ?>">
                                                            <input type="hidden" name="status" value="approved">
                                                            <button 
                                                                type="submit" 
                                                                name="action"
                                                                class="p-1 text-green-600 hover:text-green-800"
                                                                title="Approve"
                                                            >
                                                                <i class="fas fa-check"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <?php if ($app['status'] !== 'rejected'): ?>
                                                        <form method="post" class="inline">
                                                            <input type="hidden" name="id" value="<?= htmlspecialchars($app['id']) ?>">
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button 
                                                                type="submit" 
                                                                name="action"
                                                                class="p-1 text-red-600 hover:text-red-800"
                                                                title="Reject"
                                                            >
                                                                <i class="fas fa-times"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <button 
                                                        onclick="updateStatus('<?= htmlspecialchars($app['id']) ?>', '<?= htmlspecialchars($app['status']) ?>')"
                                                        class="p-1 text-blue-600 hover:text-blue-800"
                                                        title="Change Status"
                                                    >
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="p-8 text-center">
                                            <p class="text-gray-500">No applications found matching your criteria.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination would go here in a real application -->
                </div>
            </div>
        </section>
    </main>
    
    <!-- Application Details Modal -->
    <div id="detailsModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-sm p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-bold text-gov-darkblue" id="modalTitle">Application Details</h3>
                <button 
                    onclick="document.getElementById('detailsModal').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <div class="space-y-4" id="modalContent">
                <!-- Content will be filled by JavaScript -->
            </div>
            
            <div class="mt-6 pt-4 border-t border-gray-200">
                <h4 class="text-sm font-medium text-gray-700 mb-2">Status History</h4>
                <div class="space-y-2" id="statusHistory">
                    <!-- Status history will be loaded here -->
                </div>
            </div>
            
            <div class="mt-6 flex justify-end">
                <button 
                    onclick="document.getElementById('detailsModal').classList.add('hidden')"
                    class="px-4 py-2 bg-gray-200 text-gray-700 rounded-sm hover:bg-gray-300"
                >
                    Close
                </button>
            </div>
        </div>
    </div>
    
    <!-- Status Update Modal -->
    <div id="statusModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
        <div class="bg-white rounded-sm p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-start mb-4">
                <h3 class="text-lg font-bold text-gov-darkblue">Update Application Status</h3>
                <button 
                    onclick="document.getElementById('statusModal').classList.add('hidden')"
                    class="text-gray-500 hover:text-gray-700"
                >
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            <form id="statusForm" method="post">
                <input type="hidden" name="id" id="statusAppId">
                <input type="hidden" name="action" value="update">
                
                <div class="mb-4">
                    <label for="status" class="block text-sm font-medium text-gray-700 mb-1">New Status</label>
                    <select 
                        id="status" 
                        name="status"
                        class="w-full border border-gray-300 rounded-sm p-2 focus:ring-gov-blue focus:border-gov-blue"
                        required
                    >
                        <option value="pending">Pending</option>
                        <option value="under_review">Under Review</option>
                        <option value="approved">Approved</option>
                        <option value="rejected">Rejected</option>
                    </select>
                </div>
                
                <div class="mb-4">
                    <label for="comments" class="block text-sm font-medium text-gray-700 mb-1">Comments (Optional)</label>
                    <textarea 
                        id="comments" 
                        name="comments"
                        rows="3"
                        class="w-full border border-gray-300 rounded-sm p-2 focus:ring-gov-blue focus:border-gov-blue"
                        placeholder="Add any comments about this status change..."
                    ></textarea>
                </div>
                
                <div class="flex justify-end space-x-2">
                    <button 
                        type="button"
                        onclick="document.getElementById('statusModal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-sm hover:bg-gray-300"
                    >
                        Cancel
                    </button>
                    <button 
                        type="submit"
                        class="px-4 py-2 bg-gov-blue text-white rounded-sm hover:bg-blue-700"
                    >
                        Update Status
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        async function showDetails(appId) {
            try {
                // Show loading state
                document.getElementById('modalContent').innerHTML = `
                    <div class="flex justify-center py-8">
                        <i class="fas fa-circle-notch fa-spin text-4xl text-gov-blue loading-spinner"></i>
                    </div>
                `;
                
                document.getElementById('statusHistory').innerHTML = `
                    <div class="flex justify-center py-4">
                        <i class="fas fa-circle-notch fa-spin text-2xl text-gov-blue loading-spinner"></i>
                    </div>
                `;
                
                document.getElementById('detailsModal').classList.remove('hidden');
                
                // Fetch application details
                const response = await fetch(`api/get-application.php?id=${appId}`);
                console.log(response)
                if (!response.ok) {
                    const error = await response.json();
                    throw new Error(error.error || 'Failed to fetch application');
                }
                
                const app = await response.json();
                
                // Format dates
                const createdDate = new Date(app.created_at).toLocaleDateString();
                const startDate = new Date(app.date_from).toLocaleDateString();
                const endDate = new Date(app.date_to).toLocaleDateString();
                
                // Populate details
                document.getElementById('modalTitle').textContent = `Application: ${app.application_number}`;
                
                const content = `
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Application Information</h4>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm"><span class="font-medium">Application Date:</span> ${createdDate}</p>
                                <p class="text-sm"><span class="font-medium">Status:</span> <span class="px-2 py-0.5 rounded text-xs ${getStatusBadgeClass(app.status)}">${app.status.replace('_', ' ')}</span></p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Applicant Information</h4>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm"><span class="font-medium">Name:</span> ${app.applicant_name}</p>
                                <p class="text-sm"><span class="font-medium">Organization:</span> ${app.applicant_organization || 'N/A'}</p>
                                <p class="text-sm"><span class="font-medium">Contact:</span> ${app.applicant_mobile} | ${app.applicant_email}</p>
                                <p class="text-sm"><span class="font-medium">ID Proof:</span> ${app.id_proof_type} (${app.id_proof_number})</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Festival Details</h4>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm"><span class="font-medium">Type:</span> ${app.festival_type}</p>
                                <p class="text-sm"><span class="font-medium">Location:</span> ${app.location_type}</p>
                                <p class="text-sm"><span class="font-medium">Address:</span> ${app.address}</p>
                                <p class="text-sm"><span class="font-medium">Dates:</span> ${startDate} to ${endDate}</p>
                                <p class="text-sm"><span class="font-medium">Duration:</span> ${app.duration} days</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Pandal Specifications</h4>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm"><span class="font-medium">Dimensions:</span> ${app.length}m × ${app.width}m × ${app.height}m</p>
                                <p class="text-sm"><span class="font-medium">Area:</span> ${app.area} m²</p>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="text-sm font-medium text-gray-700">Sound System</h4>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm"><span class="font-medium">Required:</span> ${app.sound_system ? 'Yes' : 'No'}</p>
                                ${app.sound_system ? `<p class="text-sm"><span class="font-medium">Power:</span> ${app.sound_system_power} kW</p>` : ''}
                            </div>
                        </div>
                        
                        <div class="md:col-span-2">
                            <h4 class="text-sm font-medium text-gray-700">Financial Details</h4>
                            <div class="mt-2 space-y-1">
                                <p class="text-sm"><span class="font-medium">Base Fee:</span> ₹${(app.area * app.duration * 10).toFixed(2)} (${app.area}m² × ${app.duration} days × ₹10)</p>
                                ${app.sound_system ? `<p class="text-sm"><span class="font-medium">Sound Fee:</span> ₹${(app.sound_system_power * 50).toFixed(2)} (${app.sound_system_power}kW × ₹50)</p>` : ''}
                                <p class="text-sm font-medium"><span class="font-medium">Total Fee:</span> ₹${parseFloat(app.fee_amount).toFixed(2)}</p>
                            </div>
                        </div>
                    </div>
                `;
                
                document.getElementById('modalContent').innerHTML = content;
                
                // Fetch status history
                const historyResponse = await fetch(`/auth/api/get-status-history.php?application_id=${appId}`);
                if (!historyResponse.ok) {
                    const error = await historyResponse.json();
                    throw new Error(error.error || 'Failed to fetch status history');
                }
                
                const history = await historyResponse.json();
                
                let historyHtml = '';
                if (history.length > 0) {
                    history.forEach(item => {
                        const date = new Date(item.created_at).toLocaleString();
                        historyHtml += `
                            <div class="flex items-start pb-2 mb-2 border-b border-gray-100 last:border-0">
                                <div class="mr-3 mt-0.5">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium ${getStatusBadgeClass(item.status)}">
                                        ${item.status.replace('_', ' ')}
                                    </span>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm">
                                        ${date}
                                        ${item.changed_by_name ? `by ${item.changed_by_name}` : ''}
                                    </p>
                                    ${item.comments ? `<p class="text-xs text-gray-600 mt-1">${item.comments}</p>` : ''}
                                </div>
                            </div>
                        `;
                    });
                } else {
                    historyHtml = '<p class="text-sm text-gray-500">No status history available</p>';
                }
                
                document.getElementById('statusHistory').innerHTML = historyHtml;
                
            } catch (error) {
                console.error('Error loading application details:', error);
                document.getElementById('modalContent').innerHTML = `
                    <div class="p-4 bg-red-50 text-red-700 rounded-sm">
                        <p class="font-medium">Error loading application details</p>
                        <p class="text-sm mt-1">${error.message}</p>
                        <p class="text-xs mt-2">Please try again or contact support if the problem persists.</p>
                    </div>
                `;
                document.getElementById('statusHistory').innerHTML = '';
            }
        }
        
        function updateStatus(appId, currentStatus) {
            document.getElementById('statusAppId').value = appId;
            document.getElementById('status').value = currentStatus;
            document.getElementById('comments').value = '';
            document.getElementById('statusModal').classList.remove('hidden');
        }
        
        function getStatusBadgeClass(status) {
            switch(status) {
                case 'approved':
                    return 'bg-green-100 text-green-800 border-green-200';
                case 'rejected':
                    return 'bg-red-100 text-red-800 border-red-200';
                case 'under_review':
                    return 'bg-blue-100 text-blue-800 border-blue-200';
                case 'pending':
                default:
                    return 'bg-yellow-100 text-yellow-800 border-yellow-200';
            }
        }
    </script>
</body>
</html>
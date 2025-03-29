<?php
// Start the session
session_start();

// Database configuration
require_once 'config/database.php';

// Authentication functions
require_once 'auth/functions.php';

// Check if user is authenticated
if (isAuthenticated()) {
    // Get user role from cookie
    $userRole = $_COOKIE['user_role'] ?? 'user';
    
    // Redirect based on role
    switch ($userRole) {
        case 'admin':
            header('Location: admin/dashboard.php');
            exit();
        case 'officer':
            header('Location: officer/dashboard.php');
            exit();
        default:
            header('Location: userdashboard.php');
            exit();
    }
}

$pageTitle = "Festival Permits Portal";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
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
    </style>
</head>
<body class="min-h-screen flex flex-col bg-gray-50">
    <?php include 'includes/header.php'; ?>
    
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="bg-gov-blue text-white py-8 md:py-12">
            <div class="container mx-auto px-4">
                <div class="text-center max-w-3xl mx-auto animate-fade-in">
                    <h1 class="text-2xl md:text-3xl font-bold mb-4">Festival Permit Application Portal</h1>
                    <p class="text-lg mb-4">
                        Login to apply for Ganpati or Durga Puja festival permits
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-4">
                        <a href="login.php" class="bg-white text-gov-blue hover:bg-gray-100 transition-colors duration-300 font-medium py-2 px-6 rounded-sm text-center">
                            Login
                        </a>
                        <a href="register.php" class="bg-transparent hover:bg-blue-700 transition-colors duration-300 border border-white text-white font-medium py-2 px-6 rounded-sm text-center">
                            Register
                        </a>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Guidelines Section -->
        <section class="py-8 md:py-12 bg-gray-50">
            <div class="container mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-xl md:text-2xl font-bold text-gov-darkblue">Important Guidelines</h2>
                    <p class="text-gray-600 mt-2">Key requirements for festival permit applications</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-4xl mx-auto">
                    <?php
                    $guidelines = [
                        [
                            'title' => 'Documentation Requirements',
                            'items' => [
                                'Valid ID proof (Aadhar, Voter ID, etc.)',
                                'Land/property permission document',
                                'Organization registration certificate (if applicable)',
                                'Electrical safety certificate (for installations)'
                            ]
                        ],
                        [
                            'title' => 'Sound System Regulations',
                            'items' => [
                                'Maximum sound level: 75dB during day, 45dB during night',
                                'Operating hours: 6:00 AM to 10:00 PM',
                                'Additional restrictions in silent zones',
                                'Special permission required for 24-hour operation'
                            ]
                        ],
                        [
                            'title' => 'Environmental Guidelines',
                            'items' => [
                                'Use eco-friendly materials for decorations',
                                'Proper waste management plan',
                                'No hazardous materials allowed',
                                'Clean-up responsibility after festival'
                            ]
                        ],
                        [
                            'title' => 'Safety Requirements',
                            'items' => [
                                'Fire safety equipment on premises',
                                'Emergency exit plans',
                                'First aid kit availability',
                                'Structural safety certificate for large pandals'
                            ]
                        ]
                    ];
                    
                    foreach ($guidelines as $guideline) {
                        echo '<div class="bg-white p-5 border border-gray-200 rounded-sm shadow-sm">';
                        echo '<h3 class="text-lg font-bold text-gov-darkblue mb-2">' . htmlspecialchars($guideline['title']) . '</h3>';
                        echo '<ul class="list-disc list-inside text-gray-700 space-y-2">';
                        foreach ($guideline['items'] as $item) {
                            echo '<li>' . htmlspecialchars($item) . '</li>';
                        }
                        echo '</ul>';
                        echo '</div>';
                    }
                    ?>
                </div>
                
                <div class="text-center mt-8">
                    <a href="guidelines.php" class="inline-flex items-center text-gov-blue hover:text-gov-darkblue font-medium">
                        View complete guidelines
                        <svg xmlns="http://www.w3.org/2000/svg" class="ml-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                        </svg>
                    </a>
                </div>
            </div>
        </section>
        
        <!-- Application Process Section -->
        <section class="py-8 md:py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="text-center mb-8">
                    <h2 class="text-xl md:text-2xl font-bold text-gov-darkblue">How To Apply</h2>
                    <p class="text-gray-600 mt-2">Simple steps to get your festival permit</p>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <div class="text-center">
                        <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-gov-blue">1</span>
                        </div>
                        <h3 class="text-lg font-bold text-gov-darkblue mb-2">Create Account</h3>
                        <p class="text-gray-600">
                            Register with your email and basic information to get started.
                        </p>
                    </div>
                    
                    <div class="text-center">
                        <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-gov-blue">2</span>
                        </div>
                        <h3 class="text-lg font-bold text-gov-darkblue mb-2">Complete Application</h3>
                        <p class="text-gray-600">
                            Fill out the online form with your festival details and requirements.
                        </p>
                    </div>
                    
                    <div class="text-center">
                        <div class="h-16 w-16 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <span class="text-2xl font-bold text-gov-blue">3</span>
                        </div>
                        <h3 class="text-lg font-bold text-gov-darkblue mb-2">Submit & Pay</h3>
                        <p class="text-gray-600">
                            Review your application and pay the required fees online.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include 'includes/footer.php'; ?>
</body>
</html>
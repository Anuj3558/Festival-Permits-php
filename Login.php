<?php
// Start output buffering to prevent header errors
ob_start();

// Include header file which contains session_start()
include_once 'includes/header.php';

// Include the Database class
require_once 'config/database.php';

require_once 'auth/functions.php';
// Initialize database connection
$database = new Database();
$pdo = $database->connect();
if (isAuthenticated()) {
    $userRole = getUserRole();
    
    // Redirect based on role
    if ($userRole == 'admin') {
        header('Location: admindashboard.php');
        exit();
    } else if ($userRole == 'applicant') {
        header('Location: userdashboard.php');
        exit();
    } else if ($userRole == 'authority') {
        // If you have a specific dashboard for authorities
        header('Location: authoritydashboard.php');
        exit();
    } else {
        // Default redirection for any other role
        header('Location: index.php');
        exit();
    }
}

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $role = $_POST['role'] ?? 'applicant';
    $remember = isset($_POST['remember']) ? true : false;

    // Basic validation
    if (empty($email) || empty($password)) {
        $error = "Email and password are required";
    } else {
        try {
            loginUser($email, $password,$role);
        } catch (PDOException $e) {
            error_log("Login Error: " . $e->getMessage());
            $error = "A system error occurred. Please try again later." ;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Festival Permits Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
       
    </style>
</head>
<body class="min-h-screen flex flex-col bg-gray-50">
    <main class="flex-grow">
        <!-- Hero Section -->
        <section class="bg-gov-blue text-white py-8 md:py-12">
            <div class="container mx-auto px-4">
                <div class="text-center max-w-3xl mx-auto">
                    <h1 class="text-2xl md:text-3xl font-bold mb-4">Login to Festival Permits Portal</h1>
                    <p class="text-lg mb-4">Access your account to manage your festival permit applications</p>
                </div>
            </div>
        </section>
        
        <!-- Login Form Section -->
        <section class="py-8 md:py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex justify-center">
                    <div class="bg-white p-6 -none sm:border sm:border-gray-200 sm:shadow-sm max-w-md w-full">
                        <?php if (isset($error)): ?>
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3  relative mb-4" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <form method="POST" action="" class="space-y-4">
                            <!-- Email Field -->
                            <div class="space-y-2">
                                <label for="email" class="block text-sm font-medium text-gray-700">Email Address</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                    <input type="email" name="email" id="email" placeholder="Enter your email" required 
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 -none md: shadow-sm focus:outline-none focus:ring-gov-blue focus:border-gov-blue"
                                        value="<?php echo isset($_COOKIE['festival_permit_email']) ? htmlspecialchars($_COOKIE['festival_permit_email']) : ''; ?>">
                                </div>
                            </div>
                            
                            <!-- Password Field -->
                            <div class="space-y-2">
                                <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none text-gray-500">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </div>
                                    <input type="password" name="password" id="password" placeholder="Enter your password" required 
                                        class="block w-full pl-10 pr-3 py-2 border border-gray-300 -none md: shadow-sm focus:outline-none focus:ring-gov-blue focus:border-gov-blue">
                                </div>
                            </div>
                            
                            <!-- Role Selection -->
                            <div class="space-y-2">
                                <label class="block text-sm font-medium text-gray-700">Account Type</label>
                                <div class="grid grid-cols-2 gap-2">
                                    <button type="button" data-role="applicant"
                                        class="role-btn py-2 px-4 border border-transparent text-sm font-medium -none md: <?php echo (!isset($_POST['role']) || (isset($_POST['role']) && $_POST['role'] == 'applicant')) ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300'; ?>">
                                        Applicant
                                    </button>
                                    <button type="button" data-role="admin"
                                        class="role-btn py-2 px-4 border border-transparent text-sm font-medium -none md: <?php echo (isset($_POST['role']) && $_POST['role'] == 'admin') ? 'bg-gov-blue text-white' : 'bg-white text-gray-700 border border-gray-300'; ?>">
                                        Government Authority
                                    </button>
                                </div>
                                <input type="hidden" name="role" id="selectedRole" value="<?php echo isset($_POST['role']) ? htmlspecialchars($_POST['role']) : 'applicant'; ?>">
                            </div>
                            
                            <!-- Remember Me & Forgot Password -->
                            <div class="flex items-center justify-between">
                                <div class="flex items-center">
                                    <input id="remember" name="remember" type="checkbox" 
                                        class="h-4 w-4 text-gov-blue focus:ring-gov-blue border-gray-300 " <?php echo isset($_POST['remember']) ? 'checked' : ''; ?>>
                                    <label for="remember" class="ml-2 block text-sm text-gray-900">Remember me</label>
                                </div>
                                <a href="forgot-password.php" class="text-sm text-gov-blue hover:underline">Forgot password?</a>
                            </div>
                            
                            <!-- Submit Button -->
                            <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium -none md: text-white bg-gov-blue hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gov-blue">
                                Login
                            </button>
                            
                            <!-- Registration Link -->
                            <div class="text-center text-sm mt-4">
                                <p>Don't have an account? <a href="register.php" class="text-gov-blue hover:underline">Register here</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </section>
    </main>
    
    <?php include_once 'includes/footer.php'; ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Handle role selection
        const roleBtns = document.querySelectorAll('.role-btn');
        const roleInput = document.getElementById('selectedRole');
        
        roleBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                const role = this.getAttribute('data-role');
                roleInput.value = role;
                
                // Update button styles
                roleBtns.forEach(b => {
                    if (b === this) {
                        b.classList.add('bg-gov-blue', 'text-white');
                        b.classList.remove('bg-white', 'text-gray-700', 'border', 'border-gray-300');
                    } else {
                        b.classList.remove('bg-gov-blue', 'text-white');
                        b.classList.add('bg-white', 'text-gray-700', 'border', 'border-gray-300');
                    }
                });
            });
        });
    });
    </script>
</body>
</html>
<?php
// Flush the output buffer
ob_end_flush();
?>
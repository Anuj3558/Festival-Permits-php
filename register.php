<?php
// Assuming you have a Header and Footer component in separate files
require_once 'includes/header.php';
require_once 'loginfrom.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register for Festival Permits</title>
    <!-- Include Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Custom animation for fade-in -->
    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-fade-in {
            animation: fadeIn 1s ease-out;
        }
    </style>
</head>
<body class="min-h-screen flex flex-col bg-gray-50">
    
    <main class="flex-grow">
        <section class="bg-gov-blue text-white py-8 md:py-12">
            <div class="container mx-auto px-4">
                <div class="text-center max-w-3xl mx-auto animate-fade-in">
                    <h1 class="text-2xl md:text-3xl font-bold mb-4">Register for Festival Permits</h1>
                    <p class="text-lg mb-4">
                        Create an account to apply and manage your festival permit applications
                    </p>
                </div>
            </div>
        </section>
        
        <section class="py-8 md:py-12 bg-white">
            <div class="container mx-auto px-4">
                <div class="flex justify-center">
                    <?php renderLoginForm(true); ?>
                </div>
            </div>
        </section>
    </main>
    
</body>
</html>
<?php include_once 'includes/footer.php'; ?>
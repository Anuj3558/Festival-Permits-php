<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Include necessary files
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../auth/functions.php';

// Check authentication
$isAuthenticated = isAuthenticated();
$userRole = getUserRole();

// Set default headers
header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Festival Permits Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- Lucide icons -->
    <link rel="stylesheet" href="https://unpkg.com/lucide@latest">
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
        
        function showNotification(type, message) {
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            let icon, title;
            if (type === 'success') {
                icon = `<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2M10 17L5 12L6.41 10.59L10 14.17L17.59 6.58L19 8L10 17Z" /></svg>`;
                title = 'Success!';
            } else {
                icon = `<svg viewBox="0 0 24 24" width="24" height="24"><path fill="currentColor" d="M13 13H11V7H13M13 17H11V15H13M12 2C6.5 2 2 6.5 2 12S6.5 22 12 22 22 17.5 22 12 17.5 2 12 2Z" /></svg>`;
                title = 'Error';
            }
            
            notification.innerHTML = `
                <div class="notification-icon">${icon}</div>
                <div class="notification-content">
                    <h4>${title}</h4>
                    <p>${message}</p>
                </div>
                <button class="notification-close">&times;</button>
            `;
            
            document.body.appendChild(notification);
            
            // Show notification
            setTimeout(() => {
                notification.classList.add('show');
            }, 100);
            
            // Auto-remove after 5 seconds
            setTimeout(() => {
                hideNotification(notification);
            }, 5000);
            
            // Close button event
            notification.querySelector('.notification-close').addEventListener('click', () => {
                hideNotification(notification);
            });
        }

        function hideNotification(notification) {
            notification.classList.remove('show');
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    </script>
    <style>
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
        
        .container {
            width: 100%;
            margin-right: auto;
            margin-left: auto;
            padding-right: 1rem;
            padding-left: 1rem;
        }
        
        @media (min-width: 640px) {
            .container {
                max-width: 640px;
            }
        }
        
        @media (min-width: 768px) {
            .container {
                max-width: 768px;
            }
        }
        
        @media (min-width: 1024px) {
            .container {
                max-width: 1024px;
            }
        }
        
        @media (min-width: 1280px) {
            .container {
                max-width: 1280px;
            }
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
        
        /* Notification styles */
        .notification {
            position: fixed;
            top: 1rem;
            right: 1rem;
            width: 350px;
            padding: 1rem;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            display: flex;
            align-items: center;
            transform: translateX(100%);
            opacity: 0;
            transition: all 0.3s ease;
            z-index: 1000;
        }
        
        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }
        
        .notification.success {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
        }
        
        .notification.error {
            background-color: #fef2f2;
            border-left: 4px solid #ef4444;
        }
        
        .notification-icon {
            margin-right: 1rem;
            flex-shrink: 0;
        }
        
        .notification-content {
            flex-grow: 1;
        }
        
        .notification-content h4 {
            font-weight: 600;
            margin-bottom: 0.25rem;
        }
        
        .notification-content p {
            font-size: 0.875rem;
            color: #4b5563;
        }
        
        .notification-close {
            background: none;
            border: none;
            font-size: 1.25rem;
            cursor: pointer;
            color: #6b7280;
            margin-left: 0.5rem;
        }
        
        /* Dropdown styles */
        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            margin-top: 0.5rem;
            min-width: 160px;
            background-color: white;
            border-radius: 0.375rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            z-index: 50;
        }
        
        .dropdown:hover .dropdown-menu {
            display: block;
        }
        
        .dropdown-item {
            display: block;
            padding: 0.5rem 1rem;
            color: #374151;
            text-decoration: none;
        }
        
       
    </style>
</head>
<body class="bg-gray-50">
    <?php displayNotification(); ?>
    
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="gov-container max-full mx-3">
            <div class="py-2 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="flex items-center">
                        <span class="text-xs text-gov-darkgray">Government of India</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <a href="contact.php" class="text-xs text-gov-darkgray hover:text-gov-blue transition-colors">Contact Us</a>
                        <span class="text-xs text-gov-darkgray">English</span>
                    </div>
                </div>
            </div>
            
            <div class="py-4">
                <div class="flex justify-between items-center">
                    <div class="flex items-center space-x-4">
                        <div class="flex items-center justify-center">
                            <a href="index.php">
                                <img src="https://www.pcmcindia.gov.in/images/logo.png" alt="Logo" class="h-12">
                            </a>
                        </div>
                        <div class="hidden md:block">
                            <h1 class="text-lg font-bold text-gov-darkblue">Festival Permits Portal</h1>
                        </div>
                    </div>
                    
                    <div class="hidden md:flex items-center space-x-4">
                        <nav class="flex space-x-1 mr-4">
                            <a href="index.php" class="px-3 py-2 text-sm font-medium text-gov-darkblue hover:bg-gray-100 rounded-none transition-colors">
                                Home
                            </a>
                            <a href="application-status.php" class="px-3 py-2 text-sm font-medium text-gov-darkblue hover:bg-gray-100 rounded-none transition-colors">
                                Application Status
                            </a>
                            <a href="guidelines.php" class="px-3 py-2 text-sm font-medium text-gov-darkblue hover:bg-gray-100 rounded-none transition-colors">
                                Guidelines
                            </a>
                            <a href="contact.php" class="px-3 py-2 text-sm font-medium text-gov-darkblue hover:bg-gray-100 rounded-none transition-colors">
                                Contact
                            </a>
                        </nav>

                        <div class="flex space-x-2">
                            <?php if ($isAuthenticated): ?>
                                <div class="relative dropdown">
                                    <button class="flex items-center space-x-1  py-2 bg-gov-blue text-white rounded-none text-sm font-medium  transition-colors">
                                        <i data-lucide="user" class="h-4 w-4"></i>
                                        <a href="<?php echo ($userRole === 'admin') ? 'admindashboard.php' : 'userdashboard.php'; ?>" class="dropdown-item"><span class="text-white"><?php echo htmlspecialchars($_SESSION['user']['full_name'] ?? 'User'); ?></span></a>> 
                                    </button>
                                </div>
                            <?php else: ?>
                                <a href="login.php" class="px-4 py-2 bg-gov-blue text-white rounded-none text-sm font-medium hover:bg-gov-darkblue transition-colors">
                                    Login
                                </a>
                                <a href="register.php" class="px-4 py-2 border border-gov-blue text-gov-blue rounded-none text-sm font-medium hover:bg-gov-blue transition-colors">
                                    Sign Up
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="md:hidden flex items-center space-x-2">
                        <button
                            type="button"
                            class="p-2 rounded-none text-gov-darkblue hover:bg-gray-100 focus:outline-none"
                            onclick="toggleMobileMenu()"
                        >
                            <i data-lucide="menu" id="menu-icon" class="h-6 w-6"></i>
                        </button>
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
        </div>
        
        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="px-2 pt-2 pb-3 space-y-1 border-t border-gray-200 bg-white">
                <nav>
                    <a href="index.php" class="block px-3 py-2 text-base font-medium text-gov-darkblue hover:bg-gray-100 rounded-none" onclick="closeMobileMenu()">
                        Home
                    </a>
                    <a href="application-status.php" class="block px-3 py-2 text-base font-medium text-gov-darkblue hover:bg-gray-100 rounded-none" onclick="closeMobileMenu()">
                        Application Status
                    </a>
                    <a href="guidelines.php" class="block px-3 py-2 text-base font-medium text-gov-darkblue hover:bg-gray-100 rounded-none" onclick="closeMobileMenu()">
                        Guidelines
                    </a>
                    <a href="contact.php" class="block px-3 py-2 text-base font-medium text-gov-darkblue hover:bg-gray-100 rounded-none" onclick="closeMobileMenu()">
                        Contact
                    </a>
                </nav>
                
                <div class="border-t border-gray-200 pt-4 pb-3">
                    <div class="flex flex-col space-y-2 px-2">
                        <?php if ($isAuthenticated): ?>
                            <a href="<?php echo ($userRole === 'admin') ? 'admindashboard.php' : 'userdashboard.php'; ?>" class="block px-3 py-2 text-base font-medium text-white bg-gov-blue rounded-none text-center" onclick="closeMobileMenu()">
                                Dashboard
                            </a>
                            <a href="profile.php" class="block px-3 py-2 text-base font-medium text-white bg-gov-blue rounded-none text-center" onclick="closeMobileMenu()">
                                Profile
                            </a>
                            <a href="logout.php" class="block px-3 py-2 text-base font-medium text-gov-blue border border-gov-blue rounded-none text-center" onclick="closeMobileMenu()">
                                Logout
                            </a>
                        <?php else: ?>
                            <a href="login.php" class="block px-3 py-2 text-base font-medium text-white bg-gov-blue rounded-none text-center">
                                Login
                            </a>
                            <a href="register.php" class="block px-3 py-2 text-base font-medium text-gov-blue border border-gov-blue rounded-none text-center">
                                Sign Up
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <script>
        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const icon = document.getElementById('menu-icon');
            
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                icon.setAttribute('data-lucide', 'x');
            } else {
                menu.classList.add('hidden');
                icon.setAttribute('data-lucide', 'menu');
            }
            lucide.createIcons(); // Refresh icons
        }

        function closeMobileMenu() {
            document.getElementById('mobile-menu').classList.add('hidden');
            document.getElementById('menu-icon').setAttribute('data-lucide', 'menu');
            lucide.createIcons(); // Refresh icons
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(event) {
            const dropdowns = document.querySelectorAll('.dropdown');
            dropdowns.forEach(dropdown => {
                if (!dropdown.contains(event.target)) {
                    const menu = dropdown.querySelector('.dropdown-menu');
                    if (menu) menu.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
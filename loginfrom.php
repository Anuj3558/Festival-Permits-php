<?php
// Ensure this file is included before using the function
function renderLoginForm($isRegister = false) {
    ?>
    <div class="w-full max-w-md">
        <form action="process-<?php echo $isRegister ? 'register' : 'login'; ?>.php" method="POST" class="bg-white shadow-md rounded-none    px-8 pt-6 pb-8 mb-4">
            <h2 class="text-2xl font-bold mb-6 text-center">
                <?php echo $isRegister ? 'Create an Account' : 'Login'; ?>
            </h2>
            
            <?php if ($isRegister): ?>
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="fullName">
                        Full Name
                    </label>
                    <input class="shadow appearance-none border rounded-none    w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="fullName" name="fullName" type="text" placeholder="Enter your full name" required>
                </div>
            <?php endif; ?>
            
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                    Email Address
                </label>
                <input class="shadow appearance-none border rounded-none    w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="email" name="email" type="email" placeholder="Enter your email" required>
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="password">
                    Password
                </label>
                <input class="shadow appearance-none border rounded-none    w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" 
                       id="password" name="password" type="password" placeholder="Enter your password" required>
            </div>
            
            <?php if ($isRegister): ?>
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="confirmPassword">
                        Confirm Password
                    </label>
                    <input class="shadow appearance-none border rounded-none    w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline" 
                           id="confirmPassword" name="confirmPassword" type="password" placeholder="Confirm your password" required>
                </div>
            <?php endif; ?>
            
            <div class="flex items-center justify-between">
                <button class="bg-gov-blue hover:bg-blue-700 text-white font-bold py-2 px-4 rounded-none    focus:outline-none focus:shadow-outline" type="submit">
                    <?php echo $isRegister ? 'Register' : 'Login'; ?>
                </button>
                
                <?php if (!$isRegister): ?>
                    <a class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-800" href="register.php">
                        Create an Account
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    <?php
}
?>
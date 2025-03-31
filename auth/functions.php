<?php
require_once __DIR__ . '/../config/database.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer files
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

// Function to send email notifications for application status updates
function sendApplicationStatusEmail($applicantEmail, $applicantName, $status) {
    $mail = new PHPMailer(true);

    try {
        // SMTP Configuration
        $mail->isSMTP();
        $mail->Host       = 'smtp.mailtrap.io'; // Replace with your SMTP server
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_mailtrap_username'; // Replace with your Mailtrap username
        $mail->Password   = 'your_mailtrap_password'; // Replace with your Mailtrap password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Sender and recipient settings
        $mail->setFrom('noreply@festivalpermits.gov.in', 'Festival Permits System');
        $mail->addAddress($applicantEmail, $applicantName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Application Status Update';
        
        // Email body based on status
        if ($status === 'approved') {
            $mail->Body = "
                <h3>Dear $applicantName,</h3>
                <p>Your festival permit application has been <strong>APPROVED</strong>!</p>
                <p>You can now proceed with the next steps in your dashboard.</p>
                <p>Thank you for using our services.</p>
            ";
        } else {
            $mail->Body = "
                <h3>Dear $applicantName,</h3>
                <p>We regret to inform you that your festival permit application has been <strong>REJECTED</strong>.</p>
                <p>Please check your dashboard for more details or contact support.</p>
            ";
        }

        // Send the email
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mailer Error: " . $e->getMessage());
        return false;
    }
}

function registerUser($fullName, $email, $password) {
    $db = (new Database())->connect();
    
    // Check if user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->rowCount() > 0) {
        showNotification('error', 'Email already registered');
        return ['success' => false, 'message' => 'Email already registered'];
    }

    // Hash password
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    // Insert user
    $stmt = $db->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$fullName, $email, $hashedPassword])) {
        showNotification('success', 'Registration successful! You can now login.');
        return ['success' => true, 'userId' => $db->lastInsertId()];
    }
    
    showNotification('error', 'Registration failed. Please try again.');
    return ['success' => false, 'message' => 'Registration failed'];
}

function loginUser($email, $password ,$role) {
    $db = (new Database())->connect();
    
    $stmt = $db->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($user && password_verify($password, $user['password']) && $user['role'] === $role) {
        // Set session
        $_SESSION['user'] = [
            'id' => $user['id'],
            'email' => $user['email'],
            'full_name' => $user['full_name'],
            'role' => $user['role']
        ];
        
        // Set cookie (optional)
        setcookie('user_auth', $user['id'], time() + (86400 * 30), "/");
        setcookie('user_role', $user['role'], time() + (86400 * 30), "/");
        
        showNotification('success', 'Login successful! Redirecting...');
        
        // Redirect based on role
        if ($user['role'] == 'admin') {
            header('Location: admindashboard.php');
        } else if ($user['role'] == 'applicant') {
            header('Location: userdashboard.php');
        } else {
            // Default redirection if role is neither admin nor applicant
            header('Location: index.php');
        }
        exit();
        
        return ['success' => true, 'user' => $user];
    }
    
    showNotification('error', 'Invalid email or password');
    return ['success' => false, 'message' => 'Invalid credentials'];
}

function isAuthenticated() {
    return isset($_SESSION['user']);
}

function getUserRole() {
    return $_COOKIE['user_role'] ?? 'user';
}

function logout() {
    session_unset();
    session_destroy();
    setcookie('user_auth', '', time() - 3600, "/");
    setcookie('user_role', '', time() - 3600, "/");
    showNotification('success', 'You have been logged out successfully.');
    header('Location: login.php');
    exit();
}

function showNotification($type, $message) {
    $_SESSION['notification'] = [
        'type' => $type,
        'message' => $message
    ];
}

function displayNotification() {
    if (!empty($_SESSION['notification'])) {
        $notification = $_SESSION['notification'];
        $type = htmlspecialchars($notification['type']);
        $message = htmlspecialchars($notification['message']);
        
        echo <<<HTML
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            showNotification('$type', '$message');
        });
        </script>
        HTML;
        
        // Clear the notification after displaying
        unset($_SESSION['notification']);
    }
}
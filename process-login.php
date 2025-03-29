<?php
session_start();
require_once __DIR__ . '/../auth/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    
    $result = loginUser($email, $password);
    
    if ($result['success']) {
        // Redirect based on role
        switch ($result['user']['role']) {
            case 'admin':
                header('Location: admin/dashboard.php');
                break;
            case 'officer':
                header('Location: officer/dashboard.php');
                break;
            default:
                header('Location: UserDashboard.php');
        }
        exit();
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: login.php');
        exit();
    }
}
?>
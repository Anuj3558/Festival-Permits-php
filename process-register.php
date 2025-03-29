<?php
session_start();
require_once __DIR__ . '/auth/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullName = filter_input(INPUT_POST, 'fullName', FILTER_SANITIZE_STRING);
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    if ($password !== $confirmPassword) {
        $_SESSION['error'] = "Passwords don't match";
        header('Location: register.php');
        exit();
    }
    
    $result = registerUser($fullName, $email, $password);
    
    if ($result['success']) {
        $_SESSION['success'] = "Registration successful! Please login.";
        header('Location: login.php');
        exit();
    } else {
        $_SESSION['error'] = $result['message'];
        header('Location: register.php');
        exit();
    }
}
?>
<?php

require 'config/config.php';
require 'conection.php';

ini_set('display_errors', 1);

// Configure headers to allow CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$conn = DatabaseConnection::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $string = file_get_contents("php://input");
    $data = json_decode($string, true);

    $username = $data['username'];
    $email = $data['email'];
    $password = $data['password'];
    $confirm_password = $data['confirm_password'];

    // verify password length
    if (strlen($password) < 6) {
        echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters long']);
        exit();
    }


    if (!preg_match('/[A-Z]/', $password)) {
        echo json_encode(['success' => false, 'error' => 'Password must contain at least one uppercase letter']);
        exit();
    }

    if (!preg_match('/[a-z]/', $password)) {
        echo json_encode(['success' => false, 'error' => 'Password must contain at least one lowercase letter']);
        exit();
    }

    if (!preg_match('/[0-9]/', $password)) {
        echo json_encode(['success' => false, 'error' => 'Password must contain at least one number']);
        exit();
    }

    if ($password !== $confirm_password) {
        echo json_encode(['success' => false, 'error' => 'Passwords do not match']);
        exit();
    }


    // $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
    // if ($stmt->execute([$username, $email, $password])) {
    //     echo json_encode(['success' => true]);
    // } else {
    //     echo json_encode(['success' => false, 'error' => 'Registration failed']);
    // }
}

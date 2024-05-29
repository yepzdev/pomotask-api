<?php

require 'config/config.php';
require 'conection.php';
require 'helpers.php';

ini_set('display_errors', 1);

setHeaders();

$conn = DatabaseConnection::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $string = file_get_contents("php://input");
    $user_data = json_decode($string, true);

    $email = $user_data['email'] ?? '';
    $password = $user_data['password'] ?? '';

    // Verify that the fields are not empty
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email and password are required']);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        echo json_encode(['success' => true, 'user_id' => $user['id']]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Invalid credentials']);
    }
}

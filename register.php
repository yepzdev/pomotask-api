<?php

require 'config/config.php';
require 'conection.php';
require 'helpers.php';

ini_set('display_errors', 1);

setHeaders();

$conn = Connection::getInstance()->getConnection();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $string = file_get_contents("php://input");
    $user = json_decode($string, true);

    $name = $user['name'];
    $email = $user['email'];
    $password = $user['password'];
    $confirm_password = $user['confirm_password'];

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

    // encrypting password
    $encrypted_password = password_hash($password, PASSWORD_BCRYPT);


    $stmt = $conn->prepare("INSERT INTO users (name, email, password) VALUES (?, ?, ?)");
    if ($stmt->execute([$name, $email, $encrypted_password])) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Registration failed']);
    }
}

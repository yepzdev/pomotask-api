<?php

require "conection.php";

ini_set('display_errors', 1);

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$method = $_SERVER['REQUEST_METHOD'];

function filter_string_polyfill(string $string): string {
    return preg_replace("/[^A-Za-z0-9 ]/", '', $string);
}

$conn = DatabaseConnection::getInstance()->getConnection();

switch ($method) {

    case 'PUT':
        $string = file_get_contents("php://input");
        $data = json_decode($string, true);

        $id = filter_string_polyfill($data['id']);

        try {

            $sql = "UPDATE pomodoro SET current = current +1 WHERE task_id = :task_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':task_id', $id);
            $stmt->execute();

            echo json_encode(array("message" => "task updated successfully"));
        } catch (Exception $e) {
            echo json_encode(array("message" => "error updating task: " . $e->getMessage()));
        }

        break;

    default:
        echo json_encode(array("message" => "method not allowed"));
        break;
}

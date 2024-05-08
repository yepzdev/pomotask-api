<?php
require 'config/config.php';
require 'conection.php';

ini_set('display_errors', 1);

// Configure headers to allow CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get request method
$method = $_SERVER['REQUEST_METHOD'];

function filter_string_polyfill(string $string): string {
    return preg_replace("/[^A-Za-z0-9 ]/", '', $string);
}

// get conection instance
$conn = DatabaseConnection::getInstance()->getConnection();

// Handles GET, POST, PUT, DELETE requests
switch ($method) {
    case 'GET':
        $stmt = $conn->query("SELECT * FROM tasks INNER JOIN pomodoro ON tasks.id = pomodoro.task_id");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tasks);
        break;

    case 'POST':
        $string = file_get_contents("php://input");
        $data = json_decode($string, true);

        $description = filter_string_polyfill($data['description']);
        $status = filter_string_polyfill($data['status']);
        $spected = filter_string_polyfill($data['spected']);
        $current = filter_string_polyfill($data['current']);
        $highlighted = filter_string_polyfill($data['highlighted']);

        $conn->beginTransaction();

        try {
            $sql1 = "INSERT INTO tasks (description, status, highlighted) VALUES (:description, :status, :highlighted)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':description', $description);
            $stmt1->bindParam(':status', $status);
            $stmt1->bindParam(':highlighted', $highlighted);
            $stmt1->execute();

            $task_id = $conn->lastInsertId();

            $sql2 = "INSERT INTO pomodoro (task_id, spected, current) VALUES (:task_id, :spected, :current)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':task_id', $task_id);
            $stmt2->bindParam(':spected', $spected);
            $stmt2->bindParam(':current', $current);
            $stmt2->execute();

            $conn->commit();
            echo json_encode(array("message" => "task inserted correctly"));
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(array("message" => "error inserting task: " . $e->getMessage()));
        }

        break;


    case 'PUT': 
        $string = file_get_contents("php://input");
        $data = json_decode($string, true);

        $id = filter_string_polyfill($data['id']);
        $status = filter_string_polyfill($data['status']);
        // $description = filter_string_polyfill($data['description']);
        // $spected = filter_string_polyfill($data['spected']);
        // $current = filter_string_polyfill($data['current']);
        // $completed = filter_string_polyfill($data['highlighted']);

        // $conn->beginTransaction();

        try {
            // $sql1 = "UPDATE tasks SET description = :description, status = :status WHERE id = :id";
            $sql1 = "UPDATE tasks SET status = :status WHERE id = :id";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':id', $id);
            $stmt1->bindParam(':status', $status);
            // $stmt1->bindParam(':description', $description);
            $stmt1->execute();

            // $sql2 = "UPDATE pomodoro SET spected = :spected, current = :current WHERE task_id = :id";
            // $stmt2 = $conn->prepare($sql2);
            // $stmt2->bindParam(':spected', $spected);
            // $stmt2->bindParam(':current', $current);
            // $stmt2->bindParam(':id', $id);
            // $stmt2->execute();

            // $conn->commit();

            echo json_encode(array("message" => "task updated successfully"));
        } catch (Exception $e) {
            // $conn->rollBack();
            echo json_encode(array("message" => "error updating task: " . $e->getMessage()));
        }

        break;

    case 'DELETE':
        $string = file_get_contents("php://input");
        $data = json_decode($string, true);
        $id = filter_string_polyfill($data['id']);
        // Set a transaction to ensure both deletions succeed
        $conn->beginTransaction();

        try {
            $sql1 = "DELETE FROM tasks WHERE id = :id";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':id', $id);
            $stmt1->execute();

            $sql2 = "DELETE FROM pomodoro WHERE task_id = :task_id";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':task_id', $id);
            $stmt2->execute();
            // confirm transaction
            $conn->commit();

            echo json_encode(array("message" => "task deleted successfully"));
        } catch (Exception $e) {
            // roll back transaction in case of error;
            $conn->rollBack();
            echo json_encode(array("message" => "task could not be deleted: " . $e->getMessage()));
        }

        break;

    default:
        echo json_encode(array("message" => "method not allowed"));
        break;
}

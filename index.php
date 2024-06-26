<?php
require 'config/config.php';
require 'conection.php';
require 'helpers.php';

ini_set('display_errors', 1);

setHeaders();

// get request method
$method = $_SERVER['REQUEST_METHOD'];

// get conection instance
$conn = Connection::getInstance()->getConnection();

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
        $status = filter_integer($data['status']);
        $expected = filter_integer($data['expected']);
        $current = filter_integer($data['current']);
        $highlighted = filter_integer($data['highlighted']);

        $conn->beginTransaction();

        try {
            $sql1 = "INSERT INTO tasks (description, status, highlighted) VALUES (:description, :status, :highlighted)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':description', $description);
            $stmt1->bindParam(':status', $status);
            $stmt1->bindParam(':highlighted', $highlighted);
            $stmt1->execute();

            $task_id = $conn->lastInsertId();

            $sql2 = "INSERT INTO pomodoro (task_id, expected, current) VALUES (:task_id, :expected, :current)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':task_id', $task_id);
            $stmt2->bindParam(':expected', $expected);
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

        $id = filter_integer($data['id']);
        $status = filter_integer($data['status']);
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
        $id = filter_integer($data['id']);

        $conn->beginTransaction();

        try {
            // Delete records from the pomodoro table related to the task_id
            $sql_delete_pomodoro = "DELETE FROM pomodoro WHERE task_id = :task_id";
            $stmt_delete_pomodoro = $conn->prepare($sql_delete_pomodoro);
            $stmt_delete_pomodoro->bindParam(':task_id', $id);
            $stmt_delete_pomodoro->execute();

            // Then delete the record from the tasks table
            $sql_delete_task = "DELETE FROM tasks WHERE id = :id";
            $stmt_delete_task = $conn->prepare($sql_delete_task);
            $stmt_delete_task->bindParam(':id', $id);
            $stmt_delete_task->execute();

            // Confirm the transactionbla tasks
            $conn->commit();

            echo json_encode(array("message" => "task deleted successfully"));
        } catch (Exception $e) {
            // Revert transaction in case of error
            $conn->rollBack();
            echo json_encode(array("message" => "task could not be deleted: " . $e->getMessage()));
        }

        break;

    default:
        echo json_encode(array("message" => "method not allowed"));
        break;
}

<?php

require "conection.php";
require "helpers.php";

ini_set('display_errors', 1);

setHeaders();

$conn = Connection::getInstance()->getConnection();

switch ($_SERVER['REQUEST_METHOD']) {

    case 'PUT':
        $string = file_get_contents("php://input");
        $data = json_decode($string, true);

        $id = filter_integer($data['id']);

        try {

            // increases the number of current pomodoros of the highlighted task
            $sql = "UPDATE pomodoro SET current = current +1 WHERE task_id = :task_id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':task_id', $id);
            $stmt->execute();

            $sql2 = "SELECT * FROM pomodoro WHERE task_id = :task_id";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(":task_id", $id);
            $stmt2->execute();

            $result = $stmt2->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                echo json_encode($result);
            } else {
                echo json_encode(array("message" => "No records found"));
            }
        } catch (Exception $e) {
            echo json_encode(array("message" => "error updating task: " . $e->getMessage()));
        }

        break;

    default:
        echo json_encode(array("message" => "method not allowed"));
        break;
}

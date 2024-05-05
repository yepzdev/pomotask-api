<?php

require "conection.php";

ini_set('display_errors', 1);

// Configurar cabeceras para permitir CORS (Cross-Origin Resource Sharing)
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, GET, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Obtener el método de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

function filter_string_polyfill(string $string): string {
    return preg_replace("/[^A-Za-z0-9 ]/", '', $string);
}

// get conection instance
$conn = DatabaseConnection::getInstance()->getConnection();

switch ($method) {

    case 'PUT':
        // Editar una tarea existente
        $string = file_get_contents("php://input");
        $data = json_decode($string, true);

        $id = filter_string_polyfill($data['id']);
        $highlighted = filter_string_polyfill($data['highlighted']);

        try {

            // sets all highlighted tasks to false
            // !! This is not the most optimal but it is a temporary solution.
            $sql = "UPDATE tasks SET highlighted = 0";
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $sql1 = "UPDATE tasks SET highlighted = :highlighted WHERE id = :id";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':id', $id);
            $stmt1->bindParam(':highlighted', $highlighted);
            $stmt1->execute();

            echo json_encode(array("message" => "task updated successfully"));
        } catch (Exception $e) {
            echo json_encode(array("message" => "error updating task: " . $e->getMessage()));
        }

        break;

    default:
        echo json_encode(array("message" => "method not allowed"));
        break;
}

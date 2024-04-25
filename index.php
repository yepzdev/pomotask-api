<?php
require 'config/config.php';

// database configuration with PDO driver
try {
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASSWORD);
    // Establecer el modo de error de PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Conexión fallida: " . $e->getMessage());
}

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

// Manejar las solicitudes GET, POST, PUT, DELETE
switch ($method) {
    case 'GET':
        // Obtener todas las tareas
        $stmt = $conn->query("SELECT * FROM tasks INNER JOIN pomodoro ON tasks.id = pomodoro.task_id");
        $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($tasks);
        break;

    case 'POST':
        // Crear una nueva tarea

        $string = file_get_contents("php://input");
        $data = json_decode($string, true);

        $description = filter_string_polyfill($data['description']);
        $status = filter_string_polyfill($data['status']);
        $spected = filter_string_polyfill($data['spected']);
        $current = filter_string_polyfill($data['current']);
        $completed = filter_string_polyfill($data['completed']);

        $conn->beginTransaction();

        try {
            $sql1 = "INSERT INTO tasks (description, status) VALUES (:description, :status)";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':description', $description);
            $stmt1->bindParam(':status', $status);
            $stmt1->execute();

            $task_id = $conn->lastInsertId();

            $sql2 = "INSERT INTO pomodoro (task_id, spected, current) VALUES (:task_id, :spected, :current)";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':task_id', $task_id);
            $stmt2->bindParam(':spected', $spected);
            $stmt2->bindParam(':current', $current);
            $stmt2->execute();

            $conn->commit();
            echo json_encode(array("message" => "Tarea creada correctamente"));
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(array("message" => "Error al eliminar registros: " . $e->getMessage()));
        }

        break;


    case 'PUT':
        // Editar una tarea existente
        $string = file_get_contents("php://input");
        $data = json_decode($string, true);

        $id = filter_string_polyfill($data['id']);
        $description = filter_string_polyfill($data['description']);
        $status = filter_string_polyfill($data['status']);
        $spected = filter_string_polyfill($data['spected']);
        $current = filter_string_polyfill($data['current']);
        $completed = filter_string_polyfill($data['completed']);

        $conn->beginTransaction();

        try {
            $sql1 = "UPDATE tasks SET description = :description, status = :status WHERE id = :id";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bindParam(':description', $description);
            $stmt1->bindParam(':status', $status);
            $stmt1->bindParam(':id', $id);
            $stmt1->execute();

            $sql2 = "UPDATE pomodoro SET spected = :spected, current = :current WHERE task_id = :id";
            $stmt2 = $conn->prepare($sql2);
            $stmt2->bindParam(':spected', $spected);
            $stmt2->bindParam(':current', $current);
            $stmt2->bindParam(':id', $id);
            $stmt2->execute();

            $conn->commit();
            echo json_encode(array("message" => "Tarea creada correctamente"));
        } catch (Exception $e) {
            $conn->rollBack();
            echo json_encode(array("message" => "Error al actualizar tarea: " . $e->getMessage()));
        }

        break;

    case 'DELETE':
        // Eliminar una tarea

        // obtenemos los datos del body
        $string = file_get_contents("php://input");
        // convertimos a matriz asociativa
        $data = json_decode($string, true);
        $id = filter_string_polyfill($data['id']);
        // Establecer una transacción para garantizar que ambas eliminaciones se realicen correctamente o se deshagan en caso de error
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
        
            // Confirmar la transacción si ambas eliminaciones se realizaron correctamente
            $conn->commit();

            echo json_encode(array("message" => "Tarea eliminada correctamente"));
        } catch (Exception $e) {
            // revertir la transaccion en caso de error;

            $conn->rollBack();
            echo json_encode(array("message" => "Error al eliminar registros: " . $e->getMessage()));
        }

        break;

    default:
        echo json_encode(array("message" => "Método no permitido"));
        break;
}

$conn = null; // Cerrar la conexión

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
        $data = json_decode(file_get_contents('php://input'), true);
        $contenido = $data['contenido'];
        $score = $data['score'];
        $spected = $data['spected'];
        $actual = $data['actual'];
        $completada = $data['completada'];

        $sql = "INSERT INTO tareas (contenido, score, spected, actual, completada) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$contenido, $score, $spected, $actual, $completada]);
        echo json_encode(array("message" => "Tarea creada correctamente"));
        break;


    case 'PUT':
        // Editar una tarea existente
        parse_str(file_get_contents("php://input"), $data);
        $id = $data['id'];
        $contenido = $data['contenido'];
        $score = $data['score'];
        $spected = $data['spected'];
        $actual = $data['actual'];
        $completada = $data['completada'];

        $sql = "UPDATE tareas SET contenido=?, score=?, spected=?, actual=?, completada=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$contenido, $score, $spected, $actual, $completada, $id]);
        echo json_encode(array("message" => "Tarea actualizada correctamente"));
        break;

    case 'DELETE':
        // Eliminar una tarea

        // obtenemos los datos del body
        $data = file_get_contents("php://input");
        // convertimos a matriz asociativa
        $data_arr = json_decode($data, true);
        $id = $data_arr['id'];
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

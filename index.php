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
        // $stmt = $conn->query("SELECT * FROM tasks");
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
        parse_str(file_get_contents("php://input"), $data);
        $id = $data['id'];

        $sql = "DELETE FROM tareas WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$id]);
        echo json_encode(array("message" => "Tarea eliminada correctamente"));
        break;

    default:
        echo json_encode(array("message" => "Método no permitido"));
        break;
}

$conn = null; // Cerrar la conexión

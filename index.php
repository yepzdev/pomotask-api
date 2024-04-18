<?php
// Configurar conexión a la base de datos con PDO
$servername = "localhost";
$username = "tu_usuario";
$password = "tu_contraseña";
$dbname = "tu_base_de_datos";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Establecer el modo de error de PDO a excepción
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
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
        $stmt = $conn->query("SELECT * FROM tareas");
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

    // Aquí puedes manejar los casos para PUT y DELETE si los necesitas

    default:
        echo json_encode(array("message" => "Método no permitido"));
        break;
}

$conn = null; // Cerrar la conexión
?>

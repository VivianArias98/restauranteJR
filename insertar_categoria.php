<?php
// =====================================================
// ✅ Mostrar errores (solo en desarrollo)
// =====================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
include_once 'conexion.php';

// =====================================================
// ✅ Validar conexión
// =====================================================
if (!isset($conn) || $conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "Error de conexión: " . ($conn->connect_error ?? 'No se creó la conexión.')
    ]);
    exit;
}

// =====================================================
// ✅ Capturar datos del formulario
// =====================================================
$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

if ($nombre === '') {
    echo json_encode(["success" => false, "message" => "El nombre de la categoría es obligatorio"]);
    exit;
}

// =====================================================
// ✅ Verificar si la categoría ya existe (sin importar mayúsculas/minúsculas)
// =====================================================
$stmt = $conn->prepare("SELECT idCategoria FROM categoria WHERE LOWER(nombre) = LOWER(?) LIMIT 1");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "⚠️ La categoría '$nombre' ya existe en el sistema."
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

// =====================================================
// ✅ Insertar nueva categoría si no existe
// =====================================================
$stmt = $conn->prepare("INSERT INTO categoria (nombre, descripcion) VALUES (?, ?)");
if (!$stmt) {
    echo json_encode([
        "success" => false,
        "message" => "Error al preparar la consulta: " . $conn->error
    ]);
    exit;
}

$stmt->bind_param("ss", $nombre, $descripcion);
$ejecutado = $stmt->execute();


if ($ejecutado) {
    echo json_encode(["success" => true, "message" => "✅ Categoría registrada correctamente"]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "❌ Error al insertar: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>

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
// ✅ Preparar consulta segura
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

// =====================================================
// ✅ Verificar resultado
// =====================================================
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

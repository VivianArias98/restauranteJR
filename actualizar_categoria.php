<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

// Capturar datos
$id = $_POST['id'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

if ($id === '' || $nombre === '') {
    echo json_encode(["success" => false, "message" => "⚠️ Datos incompletos."]);
    exit;
}

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    echo json_encode([
        "success" => false,
        "message" => "❌ Error de conexión: " . ($conn->connect_error ?? 'No se creó la conexión.')
    ]);
    exit;
}

// Preparar consulta
$stmt = $conn->prepare("UPDATE categoria SET nombre = ?, descripcion = ? WHERE idCategoria = ?");
if (!$stmt) {
    echo json_encode(["success" => false, "message" => "❌ Error al preparar la consulta: " . $conn->error]);
    exit;
}

$stmt->bind_param("ssi", $nombre, $descripcion, $id);
$ejecutado = $stmt->execute();

// Verificar resultado
if ($ejecutado && $stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "✅ Categoría actualizada correctamente."]);
} else {
    echo json_encode(["success" => false, "message" => "⚠️ No se realizaron cambios o la categoría no existe."]);
}

$stmt->close();
$conn->close();
?>

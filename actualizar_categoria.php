<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$id = intval($_POST['idCategoria'] ?? 0);
$nombre = trim($_POST['nombre'] ?? '');

if ($id <= 0 || empty($nombre)) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
    exit;
}

$sql = "UPDATE categoria SET nombre = ? WHERE idCategoria = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("si", $nombre, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '✅ Categoría actualizada correctamente.']);
} else {
    echo json_encode(['success' => false, 'message' => '⚠️ No se pudo actualizar la categoría: ' . $stmt->error]);
}

$stmt->close();
$conn->close();
?>

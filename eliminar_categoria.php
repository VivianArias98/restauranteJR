<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$id = intval($_POST['idCategoria'] ?? 0);

if ($id <= 0) {
    echo json_encode(['success' => false, 'message' => 'ID inválido.']);
    exit;
}

// 🔹 Verificar si está asociada a otros registros
// Cambia "gasto" por el nombre real de tu tabla relacionada (si existe)
$checkSql = "SELECT COUNT(*) FROM gasto WHERE idCategoria = ?";
$checkStmt = $conn->prepare($checkSql);

if ($checkStmt) {
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode([
            'success' => false,
            'message' => '⚠️ No se puede eliminar esta categoría porque está asociada a otros registros (por ejemplo, gastos).'
        ]);
        $conn->close();
        exit;
    }
}

// 🔹 Si no está asociada, eliminar
$sql = "DELETE FROM categoria WHERE idCategoria = ?";
$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
    exit;
}

$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => '🗑️ Categoría eliminada correctamente.']);
} else {
    // Si MySQL devuelve error de integridad (clave foránea)
    if ($conn->errno == 1451) {
        echo json_encode(['success' => false, 'message' => '⚠️ No se puede eliminar esta categoría porque está relacionada con otros registros.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al eliminar: ' . $stmt->error]);
    }
}

$stmt->close();
$conn->close();
?>

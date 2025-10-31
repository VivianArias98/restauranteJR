<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

// Obtener el ID desde POST
$id = $_POST['id'] ?? '';

if ($id === '') {
    echo json_encode(["success" => false, "message" => "ID no recibido"]);
    exit;
}

// =====================================================
// ✅ 1. Verificar si la categoría está asociada a un insumo
// =====================================================
$verificar = $conn->prepare("SELECT COUNT(*) AS total FROM insumo WHERE idCategoria = ?");
$verificar->bind_param("i", $id);
$verificar->execute();
$resultado = $verificar->get_result();
$fila = $resultado->fetch_assoc();
$totalInsumos = $fila['total'] ?? 0;
$verificar->close();

// Si existen insumos asociados, no permitir eliminar
if ($totalInsumos > 0) {
    echo json_encode([
        "success" => false,
        "message" => "❌ No se puede eliminar la categoría porque está asociada a $totalInsumos insumo(s)."
    ]);
    exit;
}

// =====================================================
// ✅ 2. Eliminar la categoría (ya sin dependencias)
// =====================================================
$stmt = $conn->prepare("DELETE FROM categoria WHERE idCategoria = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

// Verificar resultado
if ($stmt->affected_rows > 0) {
    echo json_encode(["success" => true, "message" => "✅ Categoría eliminada correctamente."]);
} else {
    echo json_encode(["success" => false, "message" => "⚠️ No se encontró la categoría o no se pudo eliminar."]);
}

$stmt->close();
$conn->close();
?>

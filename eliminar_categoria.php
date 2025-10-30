<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Activar errores de MySQLi para ver causas exactas
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $id = intval($_POST['idCategoria'] ?? 0);

    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'ID inválido.']);
        exit;
    }

    // 🔹 Verificar si la categoría está asociada a algún insumo
    $checkSql = "SELECT COUNT(*) FROM insumo WHERE idCategoria = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("i", $id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        echo json_encode([
            'success' => false,
            'message' => '⚠️ No se puede eliminar esta categoría porque está asociada a uno o más insumos.'
        ]);
        $conn->close();
        exit;
    }

    // 🔹 Si no hay insumos asociados, eliminar la categoría
    $stmt = $conn->prepare("DELETE FROM categoria WHERE idCategoria = ?");
    $stmt->bind_param("i", $id);
    $ok = $stmt->execute();

    if ($ok) {
        echo json_encode(['success' => true, 'message' => '🗑️ Categoría eliminada correctamente.']);
    } else {
        echo json_encode(['success' => false, 'message' => '⚠️ No se pudo eliminar la categoría.']);
    }

    $stmt->close();
    $conn->close();

} catch (mysqli_sql_exception $e) {
    if ($e->getCode() == 1451) {
        // Error de integridad referencial
        echo json_encode([
            'success' => false,
            'message' => '⚠️ No se puede eliminar esta categoría porque está relacionada con otros registros (por ejemplo, insumos).'
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error SQL: ' . $e->getMessage()]);
    }
}
?>

<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Error de conexión a la base de datos.");
    }

    $sql = "
        SELECT i.nombre AS insumo, c.nombre AS categoria
        FROM insumo i
        INNER JOIN categoria c ON i.idCategoria = c.idCategoria
        ORDER BY i.nombre
    ";

    $result = $conn->query($sql);
    $data = [];

    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode($data, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>

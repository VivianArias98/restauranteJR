<?php
header('Content-Type: application/json');
include 'conexion.php';

$id = $_POST['id'] ?? '';

if ($id === '') {
    echo json_encode(["success" => false, "message" => "ID no recibido"]);
    exit;
}

$stmt = $conexion->prepare("DELETE FROM categoria WHERE idCategoria = ?");
$stmt->bind_param("i", $id);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Categoría eliminada correctamente"]);

$stmt->close();
$conexion->close();
?>

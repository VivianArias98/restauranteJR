<?php
header('Content-Type: application/json');
include 'conexion.php';

$id = $_POST['id'] ?? '';
$nombre = $_POST['nombre'] ?? '';
$descripcion = $_POST['descripcion'] ?? '';

if ($id === '' || $nombre === '') {
    echo json_encode(["success" => false, "message" => "Datos incompletos"]);
    exit;
}

$stmt = $conexion->prepare("UPDATE categoria SET nombre = ?, descripcion = ? WHERE idCategoria = ?");
$stmt->bind_param("ssi", $nombre, $descripcion, $id);
$stmt->execute();

echo json_encode(["success" => true, "message" => "Categoría actualizada correctamente"]);

$stmt->close();
$conexion->close();
?>

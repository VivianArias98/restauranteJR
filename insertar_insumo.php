<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
$descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';
$idCategoria = isset($_POST['idCategoria']) ? intval($_POST['idCategoria']) : 0;

if ($nombre === '') {
  echo json_encode(["success" => false, "message" => "El nombre del insumo es obligatorio."]);
  exit;
}

$stmt = $conn->prepare("INSERT INTO insumo (nombre, descripcion, idCategoria) VALUES (?, ?, ?)");
$stmt->bind_param("ssi", $nombre, $descripcion, $idCategoria);

if ($stmt->execute()) {
  echo json_encode(["success" => true, "message" => "Insumo agregado correctamente.", "id" => $stmt->insert_id]);
} else {
  echo json_encode(["success" => false, "message" => "Error al registrar el insumo."]);
}

$stmt->close();
$conn->close();
?>

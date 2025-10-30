<?php
// Mostrar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// Consulta la tabla "categoria" (asegúrate que exista con ese nombre)
$sql = "SELECT idCategoria, nombre FROM categoria ORDER BY nombre ASC";
$result = $conn->query($sql);

$categorias = [];
if ($result && $result->num_rows > 0) {
  while ($row = $result->fetch_assoc()) {
    $categorias[] = $row;
  }
}

echo json_encode(["success" => true, "data" => $categorias]);
$conn->close();
?>

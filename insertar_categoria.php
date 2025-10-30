<?php
// Mostrar errores para depurar (puedes quitar estas 2 líneas cuando funcione)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

// 🔹 Verificar que se haya enviado el nombre
if (!isset($_POST['nombre']) || trim($_POST['nombre']) === '') {
  echo json_encode(["success" => false, "message" => "El nombre de la categoría es obligatorio."]);
  exit;
}

$nombre = trim($_POST['nombre']);

// 🔹 Verificar si ya existe una categoría con ese nombre
$stmt = $conn->prepare("SELECT idCategoria FROM Categoria WHERE nombre = ?");
$stmt->bind_param("s", $nombre);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
  echo json_encode(["success" => false, "message" => "Esta categoría ya existe."]);
  $stmt->close();
  $conn->close();
  exit;
}
$stmt->close();

// 🔹 Insertar nueva categoría
$stmt = $conn->prepare("INSERT INTO Categoria (nombre) VALUES (?)");
$stmt->bind_param("s", $nombre);

if ($stmt->execute()) {
  echo json_encode([
    "success" => true,
    "message" => "Categoría registrada correctamente.",
    "id" => $stmt->insert_id
  ]);
} else {
  echo json_encode([
    "success" => false,
    "message" => "Error al guardar la categoría."
  ]);
}

$stmt->close();
$conn->close();
?>

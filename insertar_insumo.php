<?php
// insertar_insumo.php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;

$nombre = isset($input['nombre']) ? trim($input['nombre']) : '';
$cantidad = isset($input['cantidad']) ? (int)$input['cantidad'] : 1;
$precio = isset($input['precio']) ? (float)$input['precio'] : 0.00;

if ($nombre === '' ) {
    http_response_code(400);
    echo json_encode(["success" => false, "message" => "Nombre requerido."]);
    exit;
}

$stmt = $mysqli->prepare("INSERT INTO Insumo (nombre, cantidad, precio) VALUES (?, ?, ?)");
$stmt->bind_param("sid", $nombre, $cantidad, $precio);
$stmt->execute();
$insertId = $stmt->insert_id;
$stmt->close();

echo json_encode(["success" => true, "message" => "Insumo agregado.", "id" => $insertId]);
?>

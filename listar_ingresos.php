<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$idCaja = intval($_GET['idCaja'] ?? 0);
$sql = "SELECT fecha, descripcion, monto FROM ingreso WHERE idCaja = ? ORDER BY fecha DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idCaja);
$stmt->execute();
$res = $stmt->get_result();

$ingresos = [];
while ($fila = $res->fetch_assoc()) {
  $ingresos[] = $fila;
}

echo json_encode($ingresos, JSON_UNESCAPED_UNICODE);
$stmt->close();
$conn->close();
?>

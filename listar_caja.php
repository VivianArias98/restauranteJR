<?php
include 'conexion.php';

$sql = "SELECT idCaja, nombre, saldo FROM caja";
$result = $conn->query($sql);
$cajas = [];

while ($row = $result->fetch_assoc()) {
  $cajas[] = $row;
}

echo json_encode($cajas);
$conn->close();
?>

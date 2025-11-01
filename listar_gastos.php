<?php
include("conexion.php");

$query = "SELECT rg.*, mp.tipo AS medioPago, c.nombre AS nombreCaja 
          FROM registrogasto rg 
          JOIN mediodepago mp ON rg.idMedioPago = mp.idMedioPago 
          JOIN caja c ON rg.idCaja = c.idCaja 
          ORDER BY rg.fecha DESC";

$result = $conn->query($query);

$gastos = [];
while ($fila = $result->fetch_assoc()) {
  $gastos[] = [
    "idRegistroGasto" => $fila["idRegistroGasto"],
    "fecha" => $fila["fecha"],
    "concepto" => $fila["concepto"],
    "montoTotal" => $fila["montoTotal"],
    "medioPago" => $fila["medioPago"],
    "caja" => $fila["nombreCaja"],
    "observaciones" => $fila["observaciones"],
  ];
}

echo json_encode($gastos, JSON_UNESCAPED_UNICODE);
?>

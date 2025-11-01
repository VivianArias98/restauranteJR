<?php
include("conexion.php");
header('Content-Type: application/json; charset=utf-8');

error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
  echo json_encode(["error" => "❌ ID no válido o no recibido."]);
  exit;
}

$id = intval($_GET['id']);

// Buscar el gasto principal
$sql = "SELECT rg.idRegistroGasto, rg.concepto, rg.montoTotal, rg.idMedioPago, rg.observaciones,
               rg.idCaja, c.nombre AS nombreCaja
        FROM registrogasto rg
        JOIN caja c ON rg.idCaja = c.idCaja
        WHERE rg.idRegistroGasto = $id";
$result = $conn->query($sql);

if ($result->num_rows === 0) {
  echo json_encode(["error" => "❌ No se encontró el gasto."]);
  exit;
}

$gasto = $result->fetch_assoc();

// Obtener insumos relacionados
$insumos = [];
$qInsumos = $conn->query("
  SELECT i.insumo, i.categoria
  FROM registroinsumo ri
  JOIN insumo i ON ri.idInsumo = i.idInsumo
  WHERE ri.idRegistroGasto = $id
");
while ($fila = $qInsumos->fetch_assoc()) {
  $insumos[] = $fila;
}

// Devolver JSON
echo json_encode([
  "idRegistroGasto" => $gasto['idRegistroGasto'],
  "concepto" => $gasto['concepto'],
  "montoTotal" => $gasto['montoTotal'],
  "idMedioPago" => $gasto['idMedioPago'],
  "observaciones" => $gasto['observaciones'],
  "idCaja" => $gasto['idCaja'],
  "nombreCaja" => $gasto['nombreCaja'],
  "insumos" => $insumos
]);
?>

<?php
include("conexion.php");

header('Content-Type: application/json; charset=utf-8');

// Validar datos recibidos
if (
  !isset($_POST['id']) ||
  !isset($_POST['concepto']) ||
  !isset($_POST['monto']) ||
  !isset($_POST['medioPago'])
) {
  echo json_encode(["success" => false, "message" => "❌ Faltan datos para actualizar el gasto."]);
  exit;
}

$id = intval($_POST['id']);
$concepto = trim($_POST['concepto']);
$montoNuevo = floatval($_POST['monto']);
$medio = intval($_POST['medioPago']);
$obs = isset($_POST['observaciones']) ? trim($_POST['observaciones']) : "";

// Verificar que exista el gasto
$q = $conn->query("SELECT montoTotal, idCaja, eliminado FROM registrogasto WHERE idRegistroGasto = $id");

if ($q->num_rows === 0) {
  echo json_encode(["success" => false, "message" => "❌ Gasto no encontrado."]);
  exit;
}

$gastoAnt = $q->fetch_assoc();

if (isset($gastoAnt['eliminado']) && $gastoAnt['eliminado'] == 1) {
  echo json_encode(["success" => false, "message" => "⚠️ No se puede editar un gasto eliminado."]);
  exit;
}

// Calcular diferencia para ajustar caja
$montoAnterior = floatval($gastoAnt['montoTotal']);
$idCaja = intval($gastoAnt['idCaja']);
$diferencia = $montoAnterior - $montoNuevo; // si se reduce el gasto, se devuelve dinero

// Actualizar el saldo de la caja correspondiente
if ($diferencia != 0) {
  $conn->query("UPDATE caja SET saldo = saldo + ($diferencia) WHERE idCaja = $idCaja");
}

// Actualizar el gasto con los nuevos datos
$conn->query("
  UPDATE registrogasto 
  SET concepto = '".$conn->real_escape_string($concepto)."',
      montoTotal = $montoNuevo,
      idMedioPago = $medio,
      observaciones = '".$conn->real_escape_string($obs)."'
  WHERE idRegistroGasto = $id
");

echo json_encode([
  "success" => true,
  "message" => "✅ Gasto actualizado correctamente y saldo de caja ajustado."
]);
?>

<?php
include("conexion.php");

if (!isset($_POST['id'], $_POST['motivo'], $_POST['monto'])) {
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode(["success" => false, "message" => "❌ Faltan datos."]);
  exit;
}

$id = intval($_POST['id']);
$motivo = trim($_POST['motivo']);
$monto = floatval($_POST['monto']);

// Buscar la caja original del gasto
$gasto = $conn->query("SELECT idCaja FROM registrogasto WHERE idRegistroGasto = $id")->fetch_assoc();
if (!$gasto) {
  echo json_encode(["success" => false, "message" => "❌ Gasto no encontrado."]);
  exit;
}

$idCaja = intval($gasto['idCaja']);

// Devolver dinero a la caja original
$conn->query("UPDATE caja SET saldo = saldo + $monto WHERE idCaja = $idCaja");

// Guardar motivo y marcar como eliminado
$conn->query("
  UPDATE registrogasto 
  SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | ELIMINADO: ', '".$conn->real_escape_string($motivo)."'),
      eliminado = 1
  WHERE idRegistroGasto = $id
");

// Eliminar insumos asociados (solo si quieres limpiar)
$conn->query("DELETE FROM registroinsumo WHERE idRegistroGasto = $id");

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
  "success" => true,
  "message" => "✅ Gasto marcado como eliminado. Se devolvieron $monto a la caja. Motivo: $motivo"
]);
?>

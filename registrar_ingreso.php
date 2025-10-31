<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

try {
  $monto = floatval($_POST['monto'] ?? 0);
  $descripcion = trim($_POST['descripcion'] ?? '');
  $idCaja = intval($_POST['idCaja'] ?? 0);

  if ($monto <= 0 || $idCaja <= 0) {
    throw new Exception("Datos inválidos o incompletos.");
  }

  // Insertar ingreso
  $stmt = $conn->prepare("INSERT INTO ingreso (idCaja, monto, descripcion, fecha) VALUES (?, ?, ?, NOW())");
  $stmt->bind_param("ids", $idCaja, $monto, $descripcion);
  $stmt->execute();
  $stmt->close();

  // Actualizar saldo
  $stmt2 = $conn->prepare("UPDATE caja SET saldo = saldo + ? WHERE idCaja = ?");
  $stmt2->bind_param("di", $monto, $idCaja);
  $stmt2->execute();
  $stmt2->close();

  echo json_encode(["success" => true, "message" => "✅ Ingreso registrado correctamente."]);

} catch (Exception $e) {
  echo json_encode(["success" => false, "message" => "❌ Error: " . $e->getMessage()]);
}

$conn->close();
?>

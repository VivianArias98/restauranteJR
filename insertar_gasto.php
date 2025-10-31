<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
  $conn->begin_transaction();

  $concepto  = $_POST['concepto'] ?? '';
  $monto     = $_POST['monto'] ?? '';
  $medioPago = $_POST['medioPago'] ?? '';
  $idCaja    = $_POST['idCaja'] ?? '';
  $insumos   = isset($_POST['insumos']) ? json_decode($_POST['insumos'], true) : [];

  if ($concepto === '' || $monto === '' || $medioPago === '' || $idCaja === '') {
    throw new Exception("⚠️ Faltan datos obligatorios del gasto.");
  }

  // 1️⃣ Insertar gasto principal
  $stmt = $conn->prepare("
    INSERT INTO registrogasto (concepto, montoTotal, fecha, idMedioPago, idCaja)
    VALUES (?, ?, CURDATE(), ?, ?)
  ");
  $stmt->bind_param("sdii", $concepto, $monto, $medioPago, $idCaja);
  $stmt->execute();
  $idGasto = $stmt->insert_id;
  $stmt->close();

  // 2️⃣ Asociar insumos existentes
  foreach ($insumos as $insumo) {
    $nombre = trim($insumo['nombre'] ?? '');
    if ($nombre === '') continue;

    $stmt = $conn->prepare("SELECT idInsumo FROM insumo WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) continue; // si no existe, lo omite

    $idInsumo = $res->fetch_assoc()['idInsumo'];
    $stmt->close();

    $stmt2 = $conn->prepare("INSERT INTO registroinsumo (idRegistroGasto, idInsumo) VALUES (?, ?)");
    $stmt2->bind_param("ii", $idGasto, $idInsumo);
    $stmt2->execute();
    $stmt2->close();
  }

  // 3️⃣ Actualizar saldo caja
  $stmt3 = $conn->prepare("UPDATE caja SET saldo = saldo - ? WHERE idCaja = ?");
  $stmt3->bind_param("di", $monto, $idCaja);
  $stmt3->execute();
  $stmt3->close();

  $conn->commit();
  echo json_encode(["success" => true, "message" => "✅ Gasto registrado correctamente."]);

} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(["success" => false, "message" => "❌ Error: " . $e->getMessage()]);
}

$conn->close();
?>

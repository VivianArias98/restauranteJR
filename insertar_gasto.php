<?php
include 'conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->begin_transaction();

try {
  // 🔹 Capturar datos
  $concepto = trim($_POST['concepto'] ?? '');
  $monto = floatval($_POST['monto'] ?? 0);
  $medioPago = intval($_POST['medioPago'] ?? 0);
  $idCaja = intval($_POST['idCaja'] ?? 0);
  $observaciones = trim($_POST['observaciones'] ?? '');
  $insumos = json_decode($_POST['insumos'] ?? '[]', true);

  if ($concepto === '' || $monto <= 0 || $medioPago <= 0 || $idCaja <= 0) {
    throw new Exception("Datos incompletos para registrar el gasto.");
  }

  // 🔹 1️⃣ Verificar saldo de la caja
  $stmtSaldo = $conn->prepare("SELECT saldo FROM caja WHERE idCaja = ?");
  $stmtSaldo->bind_param("i", $idCaja);
  $stmtSaldo->execute();
  $resultadoSaldo = $stmtSaldo->get_result();
  $saldoCaja = $resultadoSaldo->fetch_assoc()['saldo'] ?? 0;
  $stmtSaldo->close();

  if ($monto > $saldoCaja) {
    throw new Exception("El monto del gasto ($monto) supera el saldo disponible en la caja ($saldoCaja). No se puede registrar.");
  }

  // 🔹 2️⃣ Insertar gasto principal
  $stmt = $conn->prepare("
    INSERT INTO registrogasto (concepto, montoTotal, observaciones, fecha, idMedioPago, idCaja)
    VALUES (?, ?, ?, CURDATE(), ?, ?)
  ");
  $stmt->bind_param("sdsii", $concepto, $monto, $observaciones, $medioPago, $idCaja);
  $stmt->execute();
  $idGasto = $stmt->insert_id;
  $stmt->close();

  $ultimoIdInsumo = null;
  $ultimoIdCategoria = null;

  // 🔹 3️⃣ Procesar insumos
  foreach ($insumos as $insumo) {
    $nombre = trim($insumo['nombre'] ?? '');
    $categoria = trim($insumo['categoria'] ?? '');

    if ($nombre === '' || $categoria === '') continue;

    // Buscar o crear categoría
    $stmt = $conn->prepare("SELECT idCategoria FROM categoria WHERE nombre = ?");
    $stmt->bind_param("s", $categoria);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows > 0) {
      $idCat = $res->fetch_assoc()['idCategoria'];
    } else {
      $stmt2 = $conn->prepare("INSERT INTO categoria (nombre) VALUES (?)");
      $stmt2->bind_param("s", $categoria);
      $stmt2->execute();
      $idCat = $stmt2->insert_id;
      $stmt2->close();
    }
    $stmt->close();

    // Buscar o crear insumo
    $stmt = $conn->prepare("SELECT idInsumo FROM insumo WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $res2 = $stmt->get_result();

    if ($res2->num_rows > 0) {
      $idInsumo = $res2->fetch_assoc()['idInsumo'];
    } else {
      $stmt2 = $conn->prepare("INSERT INTO insumo (nombre, idCategoria) VALUES (?, ?)");
      $stmt2->bind_param("si", $nombre, $idCat);
      $stmt2->execute();
      $idInsumo = $stmt2->insert_id;
      $stmt2->close();
    }
    $stmt->close();

    $ultimoIdInsumo = $idInsumo;
    $ultimoIdCategoria = $idCat;

    // Registrar relación gasto–insumo
    $stmt3 = $conn->prepare("
      INSERT INTO registroinsumo (idRegistroGasto, idInsumo, nombre, descripcion)
      VALUES (?, ?, ?, '')
    ");
    $stmt3->bind_param("iis", $idGasto, $idInsumo, $nombre);
    $stmt3->execute();
    $stmt3->close();
  }

  // 🔹 4️⃣ Actualizar registrogasto con último insumo y categoría
  if ($ultimoIdInsumo && $ultimoIdCategoria) {
    $stmt = $conn->prepare("UPDATE registrogasto SET idInsumo = ?, idCategoria = ? WHERE idRegistroGasto = ?");
    $stmt->bind_param("iii", $ultimoIdInsumo, $ultimoIdCategoria, $idGasto);
    $stmt->execute();
    $stmt->close();
  }

  // 🔹 5️⃣ Actualizar saldo de caja (solo si hay saldo suficiente)
  $stmt4 = $conn->prepare("UPDATE caja SET saldo = saldo - ? WHERE idCaja = ?");
  $stmt4->bind_param("di", $monto, $idCaja);
  $stmt4->execute();
  $stmt4->close();

  $conn->commit();
  echo json_encode(["success" => true, "message" => "✅ Gasto registrado correctamente."]);

} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(["success" => false, "message" => "❌ Error: " . $e->getMessage()]);
}

$conn->close();
?>

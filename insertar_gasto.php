<?php
include 'conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->begin_transaction();

try {
  $concepto = $_POST['concepto'] ?? '';
  $monto = $_POST['monto'] ?? 0;
  $medioPago = $_POST['medioPago'] ?? '';
  $idCaja = $_POST['idCaja'] ?? '';
  $insumos = json_decode($_POST['insumos'], true);

  if (!$concepto || !$monto || !$medioPago || !$idCaja) {
    throw new Exception("Datos incompletos para registrar el gasto.");
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

  // 2️⃣ Procesar insumos
  foreach ($insumos as $insumo) {
    $nombre = trim($insumo['nombre']);
    $categoria = trim($insumo['categoria']);

    // Verificar o crear categoría
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

    // Verificar o crear insumo
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

    // 3️⃣ Relacionar gasto con insumo
    $stmt3 = $conn->prepare("
      INSERT INTO registroinsumo (idRegistroGasto, idInsumo, nombre, descripcion)
      VALUES (?, ?, ?, '')
    ");
    $stmt3->bind_param("iis", $idGasto, $idInsumo, $nombre);
    $stmt3->execute();
    $stmt3->close();
  }

  // 4️⃣ Actualizar saldo de la caja
  $stmt4 = $conn->prepare("UPDATE caja SET saldo = saldo - ? WHERE idCaja = ?");
  $stmt4->bind_param("di", $monto, $idCaja);
  $stmt4->execute();
  $stmt4->close();

  $conn->commit();
  echo json_encode(["success" => true, "message" => "✅ Gasto e insumos registrados correctamente"]);

} catch (Exception $e) {
  $conn->rollback();
  echo json_encode(["success" => false, "message" => "❌ Error: " . $e->getMessage()]);
}

$conn->close();
?>

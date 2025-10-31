<?php
include 'conexion.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn->begin_transaction();

try {
  $concepto = $_POST['concepto'];
  $monto = $_POST['monto'];
  $medioPago = $_POST['medioPago'];
  $idCaja = $_POST['idCaja'];
  $insumos = json_decode($_POST['insumos'], true);

  // 1️⃣ Insertar el gasto principal
  $stmt = $conn->prepare("INSERT INTO registrogasto (concepto, montoTotal, fecha, idMedioPago, idCaja) VALUES (?, ?, CURDATE(), ?, ?)");
  $stmt->bind_param("sdii", $concepto, $monto, $medioPago, $idCaja);
  $stmt->execute();
  $idGasto = $stmt->insert_id;
  $stmt->close();

  // 2️⃣ Registrar los insumos del gasto
  foreach ($insumos as $insumo) {
    $nombre = $insumo['nombre'];
    $categoria = $insumo['categoria'];

    // Asegurar categoría
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

    // Asegurar insumo
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

    // Registrar relación gasto-insumo (solo IDs)
    $stmt2 = $conn->prepare("INSERT INTO registroinsumo (idRegistroGasto, idInsumo) VALUES (?, ?)");
    $stmt2->bind_param("ii", $idGasto, $idInsumo);
    $stmt2->execute();
    $stmt2->close();
  }

  // 3️⃣ Actualizar saldo de la caja
  $stmt3 = $conn->prepare("UPDATE caja SET saldo = saldo - ? WHERE idCaja = ?");
  $stmt3->bind_param("di", $monto, $idCaja);
  $stmt3->execute();
  $stmt3->close();

  $conn->commit();

  echo "✅ Gasto registrado correctamente";
} catch (Exception $e) {
  $conn->rollback();
  echo "❌ Error: " . $e->getMessage();
}

$conn->close();
?>

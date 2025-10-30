<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_POST['concepto'], $_POST['monto'], $_POST['medioPago'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos."]);
    exit;
}

$concepto = trim($_POST['concepto']);
$monto = isset($_POST['monto']) ? floatval(str_replace(['.', ','], ['', '.'], $_POST['monto'])) : null;
$medioPago = intval($_POST['medioPago']);

// 🔹 ID de la caja principal (ajusta si tienes varias)
$idCaja = 1;

if ($concepto === '' || $monto <= 0 || $medioPago <= 0) {
    echo json_encode(["success" => false, "message" => "Datos inválidos."]);
    exit;
}

$conn->begin_transaction();

try {
    // ✅ Verificar saldo actual de la caja
    $stmtSaldo = $conn->prepare("SELECT saldo FROM caja WHERE idCaja = ?");
    $stmtSaldo->bind_param("i", $idCaja);
    $stmtSaldo->execute();
    $stmtSaldo->bind_result($saldoActual);
    $stmtSaldo->fetch();
    $stmtSaldo->close();

    if ($saldoActual < $monto) {
        echo json_encode(["success" => false, "message" => "Saldo insuficiente en la caja para registrar este gasto."]);
        $conn->rollback();
        $conn->close();
        exit;
    }

    // ✅ Insertar el registro del gasto
    $stmt = $conn->prepare("INSERT INTO RegistroGasto (concepto, montoTotal, idMedioPago, idCaja) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("sdii", $concepto, $monto, $medioPago, $idCaja);
    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();

    // ✅ Descontar saldo de la caja
    $stmt2 = $conn->prepare("UPDATE caja SET saldo = saldo - ? WHERE idCaja = ?");
    $stmt2->bind_param("di", $monto, $idCaja);
    $stmt2->execute();
    $stmt2->close();

    // Confirmar transacción
    $conn->commit();

    // ✅ Devolver el nuevo registro insertado
    $sql = "SELECT rg.idRegistroGasto, rg.concepto, rg.montoTotal, rg.fecha, 
                   md.tipo AS medio, c.nombre AS caja
            FROM RegistroGasto rg
            LEFT JOIN MedioDePago md ON rg.idMedioPago = md.idMedioPago
            LEFT JOIN Caja c ON rg.idCaja = c.idCaja
            WHERE rg.idRegistroGasto = ?";
    $stmt3 = $conn->prepare($sql);
    $stmt3->bind_param("i", $insertId);
    $stmt3->execute();
    $res = $stmt3->get_result();
    $row = $res->fetch_assoc();

    echo json_encode(["success" => true, "message" => "Gasto registrado correctamente.", "registro" => $row]);

} catch (Exception $e) {
    $conn->rollback();
    echo json_encode(["success" => false, "message" => "Error al guardar el gasto: " . $e->getMessage()]);
}

$conn->close();
?>

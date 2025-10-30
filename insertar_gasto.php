<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

if (!isset($_POST['concepto'], $_POST['monto'], $_POST['medioPago'])) {
    echo json_encode(["success" => false, "message" => "Faltan datos."]);
    exit;
}

$concepto = trim($_POST['concepto']);

// Convertir el monto desde formato COP (1.500.000,00 → 1500000.00)
$monto = isset($_POST['monto']) ? floatval(str_replace(['.', ','], ['', '.'], $_POST['monto'])) : null;

$medioPago = intval($_POST['medioPago']);

if ($concepto === '' || $monto <= 0 || $medioPago <= 0) {
    echo json_encode(["success" => false, "message" => "Datos inválidos."]);
    exit;
}

$conn->begin_transaction();

try {
    // Insertar registro
    $stmt = $conn->prepare("INSERT INTO RegistroGasto (concepto, montoTotal, idMedioPago) VALUES (?, ?, ?)");
    $stmt->bind_param("sdi", $concepto, $monto, $medioPago);
    $stmt->execute();
    $insertId = $stmt->insert_id;
    $stmt->close();

    // Actualizar saldo de la caja principal
    $stmt2 = $conn->prepare("UPDATE Caja SET saldo = saldo - ? WHERE idCaja = 1");
    $stmt2->bind_param("d", $monto);
    $stmt2->execute();
    $stmt2->close();

    $conn->commit();

    // Devolver el nuevo registro insertado
    $sql = "SELECT rg.idRegistroGasto, rg.concepto, rg.montoTotal, rg.fecha, md.tipo AS medio
            FROM RegistroGasto rg
            LEFT JOIN MedioDePago md ON rg.idMedioPago = md.idMedioPago
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

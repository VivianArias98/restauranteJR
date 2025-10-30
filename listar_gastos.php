<?php
header('Content-Type: application/json; charset=utf-8');
require_once 'conexion.php';

$limit = isset($_GET['limit']) ? intval($_GET['limit']) : 50;

$sql = "SELECT rg.idRegistroGasto, rg.concepto, rg.montoTotal, rg.fecha, 
               md.tipo AS medio, a.nombres, a.apellidos, c.nombre AS caja
        FROM RegistroGasto rg
        LEFT JOIN MedioDePago md ON rg.idMedioPago = md.idMedioPago
        LEFT JOIN Administrador a ON rg.idAdministrador = a.idAdministrador
        LEFT JOIN Caja c ON rg.idCaja = c.idCaja
        ORDER BY rg.fecha DESC, rg.idRegistroGasto DESC
        LIMIT ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $limit);
$stmt->execute();
$res = $stmt->get_result();

$rows = [];
while ($r = $res->fetch_assoc()) {
    $rows[] = $r;
}

echo json_encode(["success" => true, "data" => $rows]);

$stmt->close();
$conn->close();
?>

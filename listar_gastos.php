<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
include_once 'conexion.php';

if (!isset($conn) || $conn->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos: " . ($conn->connect_error ?? 'No se creó la conexión.')]);
    exit;
}

$sql = "
SELECT 
    g.idRegistroGasto,
    g.concepto,
    g.montoTotal,
    g.observaciones,
    g.fecha,
    g.idMedioPago,
    cja.nombre AS caja,
    GROUP_CONCAT(DISTINCT i.nombre SEPARATOR ', ') AS insumos,
    GROUP_CONCAT(DISTINCT cat.nombre SEPARATOR ', ') AS categorias
FROM registrogasto g
LEFT JOIN caja cja ON g.idCaja = cja.idCaja
LEFT JOIN registroinsumo ri ON g.idRegistroGasto = ri.idRegistroGasto
LEFT JOIN insumo i ON ri.idInsumo = i.idInsumo
LEFT JOIN categoria cat ON i.idCategoria = cat.idCategoria
GROUP BY g.idRegistroGasto
ORDER BY g.fecha DESC, g.idRegistroGasto DESC
";

try {
    $resultado = $conn->query($sql);
    $gastos = [];

    while ($fila = $resultado->fetch_assoc()) {
        $medioPagoTexto = match ((int)$fila["idMedioPago"]) {
            1 => "Efectivo",
            2 => "Tarjeta",
            3 => "Transferencia",
            default => "N/A"
        };

        $gastos[] = [
            "fecha" => $fila["fecha"],
            "concepto" => $fila["concepto"],
            "insumos" => $fila["insumos"] ?: "-",
            "categorias" => $fila["categorias"] ?: "-",
            "montoTotal" => $fila["montoTotal"],
            "medioPago" => $medioPagoTexto,
            "caja" => $fila["caja"],
            "observaciones" => $fila["observaciones"] ?: "-"
        ];
    }

    echo json_encode($gastos, JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}

$conn->close();
?>

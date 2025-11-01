<?php
// =====================================================
// ✅ Mostrar errores (solo en desarrollo)
// =====================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
include_once 'conexion.php';

// =====================================================
// ✅ Validar conexión
// =====================================================
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(["error" => "Error de conexión a la base de datos"]);
    exit;
}

// =====================================================
// ✅ Validar ID recibido
// =====================================================
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(["error" => "⚠️ ID de gasto no válido"]);
    exit;
}

$id = intval($_GET['id']);

// =====================================================
// ✅ Obtener los datos principales del gasto
// =====================================================
$sql = "SELECT rg.idRegistroGasto, rg.concepto, rg.montoTotal, rg.observaciones, 
               rg.idMedioPago, rg.idCaja, mp.tipo AS medioPago, c.nombre AS caja, rg.fecha
        FROM registrogasto rg
        INNER JOIN mediodepago mp ON rg.idMedioPago = mp.idMedioPago
        INNER JOIN caja c ON rg.idCaja = c.idCaja
        WHERE rg.idRegistroGasto = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(["error" => "❌ No se encontró el gasto."]);
    exit;
}

$gasto = $result->fetch_assoc();
$stmt->close();

// =====================================================
// ✅ Obtener insumos relacionados
// =====================================================
$sqlInsumos = "SELECT i.nombre AS insumo, cat.nombre AS categoria
               FROM registroinsumo ri
               INNER JOIN insumo i ON ri.idInsumo = i.idInsumo
               LEFT JOIN categoria cat ON i.idCategoria = cat.idCategoria
               WHERE ri.idRegistroGasto = ?";

$stmt2 = $conn->prepare($sqlInsumos);
$stmt2->bind_param("i", $id);
$stmt2->execute();
$resInsumos = $stmt2->get_result();

$insumos = [];
while ($fila = $resInsumos->fetch_assoc()) {
    $insumos[] = [
        "insumo" => $fila["insumo"],
        "categoria" => $fila["categoria"]
    ];
}
$stmt2->close();

// =====================================================
// ✅ Respuesta final en formato JSON limpio
// =====================================================
$gasto["insumos"] = $insumos;

echo json_encode($gasto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
$conn->close();
?>

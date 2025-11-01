<?php
include("conexion.php");

// =====================================================
// ✅ Consulta completa: gasto + medio de pago + caja + insumos + categoría
// =====================================================
$query = "
SELECT 
  rg.idRegistroGasto,
  rg.fecha,
  rg.concepto,
  rg.montoTotal,
  rg.observaciones,
  mp.tipo AS medioPago,
  c.nombre AS nombreCaja,
  GROUP_CONCAT(DISTINCT i.nombre ORDER BY i.nombre SEPARATOR ', ') AS insumos,
  GROUP_CONCAT(DISTINCT cat.nombre ORDER BY cat.nombre SEPARATOR ', ') AS categorias,
  rg.eliminado
FROM registrogasto rg
JOIN mediodepago mp ON rg.idMedioPago = mp.idMedioPago
JOIN caja c ON rg.idCaja = c.idCaja
LEFT JOIN registroinsumo ri ON rg.idRegistroGasto = ri.idRegistroGasto
LEFT JOIN insumo i ON ri.idInsumo = i.idInsumo
LEFT JOIN categoria cat ON i.idCategoria = cat.idCategoria
GROUP BY rg.idRegistroGasto
ORDER BY rg.fecha DESC
";

$result = $conn->query($query);

if (!$result) {
  echo json_encode(["error" => "❌ Error en la consulta: " . $conn->error]);
  exit;
}

$gastos = [];

while ($fila = $result->fetch_assoc()) {
  $gastos[] = [
    "idRegistroGasto" => intval($fila["idRegistroGasto"]),
    "fecha" => $fila["fecha"],
    "concepto" => $fila["concepto"],
    "insumos" => $fila["insumos"] ?: "-",
    "categorias" => $fila["categorias"] ?: "-",
    "montoTotal" => floatval($fila["montoTotal"]),
    "medioPago" => $fila["medioPago"],
    "caja" => $fila["nombreCaja"],
    "observaciones" => $fila["observaciones"] ?: "",
    "eliminado" => isset($fila["eliminado"]) ? intval($fila["eliminado"]) : 0
  ];
}

// =====================================================
// ✅ Enviar respuesta JSON limpia
// =====================================================
header("Content-Type: application/json; charset=utf-8");
echo json_encode($gastos, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);

$conn->close();
?>

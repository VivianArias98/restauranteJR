<?php
include 'conexion.php';
header('Content-Type: application/json; charset=utf-8');

$sql = "
SELECT 
  rg.idRegistroGasto,
  rg.fecha,
  rg.concepto,
  rg.montoTotal,
  rg.observaciones,
  c.nombre AS caja,
  md.tipo AS medioPago,
  i.nombre AS insumo,
  cat.nombre AS categoria
FROM registrogasto rg
LEFT JOIN caja c ON rg.idCaja = c.idCaja
LEFT JOIN mediodepago md ON rg.idMedioPago = md.idMedioPago
LEFT JOIN registroinsumo ri ON rg.idRegistroGasto = ri.idRegistroGasto
LEFT JOIN insumo i ON ri.idInsumo = i.idInsumo
LEFT JOIN categoria cat ON i.idCategoria = cat.idCategoria
ORDER BY rg.idRegistroGasto DESC, i.nombre ASC";

$result = $conn->query($sql);

$gastos = [];
while ($row = $result->fetch_assoc()) {
  $id = $row['idRegistroGasto'];

  // Si el gasto no está en el arreglo, lo agregamos
  if (!isset($gastos[$id])) {
    $gastos[$id] = [
      "fecha" => $row["fecha"],
      "concepto" => $row["concepto"],
      "montoTotal" => $row["montoTotal"],
      "medioPago" => $row["medioPago"],
      "caja" => $row["caja"],
      "observaciones" => $row["observaciones"],
      "insumos" => [] // Aquí se guardan los insumos relacionados
    ];
  }

  // Agregar los insumos y categorías al gasto correspondiente
  if ($row["insumo"] !== null) {
    $gastos[$id]["insumos"][] = [
      "nombre" => $row["insumo"],
      "categoria" => $row["categoria"]
    ];
  }
}

echo json_encode(array_values($gastos), JSON_UNESCAPED_UNICODE);
$conn->close();
?>

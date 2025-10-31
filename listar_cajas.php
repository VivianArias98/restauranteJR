<?php
// Mostrar todos los errores en pantalla (para depurar)
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

// Incluir la conexión
include 'conexion.php';

// Verificar conexión
if (!isset($conn) || $conn->connect_error) {
    echo json_encode(["error" => "Error de conexión: " . ($conn->connect_error ?? 'No se creó la variable $conn')]);
    exit;
}

// Verificar que la tabla exista
$check = $conn->query("SHOW TABLES LIKE 'caja'");
if ($check->num_rows === 0) {
    echo json_encode(["error" => "⚠️ La tabla 'caja' no existe en la base de datos actual."]);
    exit;
}

// Consultar cajas
$sql = "SELECT idCaja, nombre, saldo, estado FROM caja ORDER BY idCaja ASC";
$result = $conn->query($sql);

if (!$result) {
    echo json_encode(["error" => "Error en la consulta SQL: " . $conn->error]);
    exit;
}

$cajas = [];
while ($row = $result->fetch_assoc()) {
    $cajas[] = [
        "idCaja" => (int)$row["idCaja"],
        "nombre" => $row["nombre"],
        "saldo" => (float)$row["saldo"],
        "estado" => $row["estado"]
    ];
}

// Si no hay cajas
if (empty($cajas)) {
    echo json_encode(["mensaje" => "No hay cajas registradas."]);
    exit;
}

// Devolver resultado
echo json_encode($cajas, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

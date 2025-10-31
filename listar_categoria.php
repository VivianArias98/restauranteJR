<?php
// =====================================================
// ✅ CONFIGURACIÓN Y MANEJO DE ERRORES
// =====================================================
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');
include_once 'conexion.php';

// =====================================================
// ✅ VALIDAR CONEXIÓN
// =====================================================
if (!isset($conn) || $conn->connect_error) {
    echo json_encode([
        "error" => "Error de conexión: " . ($conn->connect_error ?? 'No se creó la variable $conn')
    ]);
    exit;
}

// =====================================================
// ✅ CONSULTAR CATEGORÍAS
// =====================================================
$sql = "SELECT idCategoria, nombre, descripcion FROM categoria ORDER BY idCategoria DESC";
$resultado = $conn->query($sql);

if (!$resultado) {
    echo json_encode(["error" => "Error en la consulta: " . $conn->error]);
    exit;
}

// =====================================================
// ✅ CREAR ARRAY DE RESULTADOS
// =====================================================
$categorias = [];
while ($fila = $resultado->fetch_assoc()) {
    $categorias[] = [
        "idCategoria" => $fila["idCategoria"],
        "nombre" => $fila["nombre"],
        "descripcion" => $fila["descripcion"] ?? ""
    ];
}

// =====================================================
// ✅ DEVOLVER JSON
// =====================================================
echo json_encode($categorias, JSON_UNESCAPED_UNICODE);
$conn->close();
?>

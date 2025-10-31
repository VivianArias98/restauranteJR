<?php
header('Content-Type: application/json; charset=utf-8');
include 'conexion.php';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $nombre = trim($_POST['nombre'] ?? '');
    $categoria = trim($_POST['categoria'] ?? '');

    if ($nombre === '' || $categoria === '') {
        throw new Exception("⚠️ Faltan datos: nombre o categoría vacíos.");
    }

    // 🔹 Verificar o insertar categoría
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

    // 🔹 Verificar si ya existe el insumo
    $stmt = $conn->prepare("SELECT idInsumo FROM insumo WHERE nombre = ?");
    $stmt->bind_param("s", $nombre);
    $stmt->execute();
    $res2 = $stmt->get_result();

    if ($res2->num_rows > 0) {
        throw new Exception("⚠️ El insumo '$nombre' ya existe en la base de datos.");
    }

    // 🔹 Insertar nuevo insumo
    $stmt2 = $conn->prepare("INSERT INTO insumo (nombre, idCategoria) VALUES (?, ?)");
    $stmt2->bind_param("si", $nombre, $idCat);
    $stmt2->execute();

    echo json_encode([
        "success" => true,
        "message" => "✅ Insumo '$nombre' registrado correctamente.",
        "idInsumo" => $stmt2->insert_id
    ]);

    $stmt2->close();

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => "❌ Error: " . $e->getMessage()]);
}

$conn->close();
?>

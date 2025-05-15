<?php
session_start();

// archivo1.php
require_once __DIR__ . '/config/config.php';  // Busca config.php dentro de la carpeta config
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $conn = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener datos de la vista
try {
    $stmt = $conn->query("SELECT * FROM inventario");
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar la vista: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario Prestado</title>
    <link rel="stylesheet" href="styles.css"> <!-- Aquí se enlaza el CSS -->
</head>
<body>

<div class="dashboard-container">
    <h2>Inventario Prestado</h2>
    <table>
        <tr>
            <th>ID Inventario</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Serial</th>
            <th>Categoría</th>
            <th>Estado</th>
            <th>ID Usuario</th>
            <th>Responsable</th>
        </tr>
        <?php if (count($inventario) > 0): ?>
            <?php foreach ($inventario as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['id_inventario']) ?></td>
                    <td><?= htmlspecialchars($item['marca']) ?></td>
                    <td><?= htmlspecialchars($item['modelo']) ?></td>
                    <td><?= htmlspecialchars($item['serial']) ?></td>
                    <td><?= htmlspecialchars($item['categoria']) ?></td>
                    <td><?= htmlspecialchars($item['estado']) ?></td>
                    <td><?= htmlspecialchars($item['id_persona']) ?></td>
                    <td><?= htmlspecialchars($item['responsable']) ?></td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="8" class="no-records">No hay registros en el inventario.</td>
            </tr>
        <?php endif; ?>
    </table>

    <!-- Botón de regresar -->
    <div class="button-container">
        <a href="digitador.php" class="btn-regresar">Regresar al Registro</a>
    </div>

</div>

</body>
</html>

<?php
session_start();

// archivo1.php
require_once __DIR__ . '/config/config.php';  // Busca config.php dentro de la carpeta config
require_once __DIR__ . '/../vendor/autoload.php';

try {
    $conn = new PDO("pgsql:host=$db_host;port=$db_port;dbname=$db_name", $db_user, $db_pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener datos de la vista
try {
    $stmt = $conn->query("SELECT * FROM inventario");
    $inventario = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al consultar la vista: " . $e->getMessage());
}
?>

<h2>Inventario Prestado</h2>
<table border="1" cellpadding="5" cellspacing="0">
    <tr>
        <th>ID Inventario</th>
        <th>Marca</th>
        <th>Modelo</th>
        <th>Serial</th>
        <th>Categoría</th>
        <th>Estado</th>
        <th>ID Usuario</th>
        <th>Responsable</th>
    </tr>
    <?php if (count($inventario) > 0): ?>
        <?php foreach ($inventario as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['id_inventario'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['marca'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['modelo'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['serial'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['categoria'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['estado'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['id_usuario'] ?? '') ?></td>
                <td><?= htmlspecialchars($item['responsable'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8">No hay registros en el inventario.</td>
        </tr>
    <?php endif; ?>
</table>

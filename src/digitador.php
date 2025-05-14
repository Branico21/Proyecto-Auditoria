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

$nombres = "";

// Verificar si el usuario está autenticado y obtener su nombre
$nombreUsuario = isset($_SESSION['usuario_nombre']) && !empty($_SESSION['usuario_nombre']) 
    ? $_SESSION['usuario_nombre'] 
    : htmlspecialchars($nombres);

// Cargar usuarios desde la tabla usuario
try {
    $stmtUsuarios = $conn->query("SELECT id, nombres, apellidos FROM usuarios");
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Digitador</title>
    <!-- Llamar al archivo CSS -->
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-container">
        <div class="welcome-header">
            <h2>¡Bienvenido, Digitador: <?= htmlspecialchars($nombreUsuario) ?>!</h2>
        </div>

        <h2>Registro Manual</h2>
        <form method="post">
            Marca: <input type="text" name="marca" required><br>
            Modelo: <input type="text" name="modelo" required><br>
            Serial: <input type="text" name="serial" required><br>
            Categoría: <input type="text" name="categoria" required><br>
            Estado: <input type="text" name="estado" required><br>
            
            Persona Responsable:
            <select name="id_persona" required>
                <option value="">-- Selecciona un usuario --</option>
                <?php foreach ($usuarios as $u): ?>
                    <option value="<?= htmlspecialchars($u['id']) ?>">
                        <?= htmlspecialchars($u['id'] . ' - ' . $u['nombres'] . ' ' . $u['apellidos']) ?>
                    </option>
                <?php endforeach; ?>
            </select><br>

            <input type="submit" name="registrar_manual" value="Registrar">
        </form>

        <h2>Cargar desde CSV</h2>
        <form method="post" enctype="multipart/form-data">
            Selecciona archivo CSV: <input type="file" name="archivo_csv" accept=".csv" required><br>
            <input type="submit" name="cargar_csv" value="Cargar CSV">
        </form>

        <form method="post" action="logout.php" style="text-align: center; margin-top: 20px;">
            <button type="submit" class="logout-button">Cerrar Sesión</button>
        </form>
    </div>
</body>
</html>
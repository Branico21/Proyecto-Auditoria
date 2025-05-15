<?php
// Iniciar sesión
session_start();

// Cargar configuración y dependencias
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Conectar a la base de datos PostgreSQL con PDO
try {
    $dsn = "pgsql:host=$db_host;port=$db_port;dbname=prueba1";
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Obtener información del usuario autenticado
$user_id = $_SESSION['user_id'];
$nombres = "";
$apellidos = "";
$rol = "";

try {
    $stmt = $pdo->prepare("SELECT nombres, apellidos, rol FROM usuarios WHERE id = :id");
    $stmt->execute([':id' => $user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $nombres = $usuario['nombres'];
        $apellidos = $usuario['apellidos'];
        $rol = $usuario['rol'];
    }
} catch (PDOException $e) {
    die("Error al obtener datos del usuario: " . $e->getMessage());
}

// Obtener lista de usuarios para el combo (responsable)
try {
    $stmtUsuarios = $pdo->query("SELECT id, nombres, apellidos FROM usuarios");
    $usuarios = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al cargar usuarios: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Digitador</title>
    <link rel="stylesheet" href="styles.css?v=<?php echo time(); ?>">
    <style>
        .boton-separado {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<div class="dashboard-container">
    <h2>¡Bienvenido, Digitador: <?= htmlspecialchars($nombres) ?>!</h2>

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

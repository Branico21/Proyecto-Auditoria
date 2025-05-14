<?php
// Iniciar sesión
session_start();
// archivo1.php
require_once __DIR__ . '/config/config.php';  // Busca config.php dentro de la carpeta config
require_once __DIR__ . '/../vendor/autoload.php';


// Aquí puedes usar la variable $pdo para interactuar con la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    // Si no hay sesión activa, redirigir al login
    header("Location: login.php");
    exit();
}



// Obtener información completa del usuario desde la base de datos
$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];
$nombres = "";
$apellidos = "";
$rol = "";

try {
    // Conectar a la base de datos PostgreSQL
    $conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

    if ($conexion) {
        // Consultar información del usuario
        $query = "SELECT nombres, apellidos, rol FROM usuarios WHERE id = $1";
        $resultado = pg_query_params($conexion, $query, array($user_id));
        
        if ($resultado && pg_num_rows($resultado) > 0) {
            $usuario = pg_fetch_assoc($resultado);
            $nombres = $usuario['nombres'];
            $apellidos = $usuario['apellidos'];
            $rol = $usuario['rol'];
        }
        
        // Cerrar conexión
        pg_close($conexion);
    }
} catch (Exception $e) {
    $error = "Error al obtener datos del usuario: " . $e->getMessage();
}

// Función para mostrar el rol en formato legible
function formatearRol($rol) {
    switch ($rol) {
        case 'super_usuario':
            return 'Super Usuario';
        case 'digitador':
            return 'Digitador';
        case 'Administrador':
            return 'Administrador';
        default:
            return 'Usuario';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control</title>
    <!-- Aseguramos que el navegador no use la versión en caché -->
    <link rel="stylesheet" href="/Sastoque/src/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="dashboard-container">
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        
        <div class="welcome-header">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($username, 0, 1)); ?>
                </div>

             <?php if ($rol === 'digitador'): ?>
    <script>
        window.location.href = "digitador.php";
    </script>
    <?php else: ?>
        <div class="user-details">
            <h2>¡Bienvenido/a, <?php echo htmlspecialchars($nombres); ?>!</h2>
            <span class="user-role <?php echo htmlspecialchars($rol); ?>">
                <?php echo htmlspecialchars(formatearRol($rol)); ?>
            </span>
        </div>
    <?php endif; ?>
            </div>
            <a href="logout.php" class="logout-btn">Cerrar sesión</a>
        </div>
        
        <div class="dashboard-content">
            <h3>Panel de control</h3>
            <p>Esta es tu área personal. Aquí podrás gestionar tus actividades según tu rol de <?php echo htmlspecialchars(formatearRol($rol)); ?>.</p>
            
            <?php if ($rol === 'super_usuario'): ?>
                <div class="admin-section">
                    <h4>Opciones de administrador</h4>
                    <p>Como Super Usuario, tienes acceso a todas las funcionalidades del sistema:</p>
                    <ul>
                        <li>Gestión de usuarios</li>
                        <li>Configuración del sistema</li>
                        <li>Reportes avanzados</li>
                        <li>Auditoría de actividades</li>
                    </ul>
                </div>
            <?php elseif ($rol === 'digitador'): ?>
                <div class="user-section">
                    <h4>Opciones de digitador</h4>
                    <p>Como Digitador, puedes realizar las siguientes acciones:</p>
                    <ul>
                        <li>Ingreso de datos</li>
                        <li>Consulta de información</li>
                        <li>Generación de reportes básicos</li>
                    </ul>
                </div>
           

                <?php elseif ($rol === 'Administrador'): ?>
                <div class="user-section">
                    <h4>Opciones de digitador</h4>
                    <p>Como Adminitrador, puedes realizar las siguientes acciones:</p>
                    <ul>
                        <li>Ingreso de datos</li>
                        <li>Consulta de información</li>
                        <li>Generación de cambios en las actividaes</li>
                    </ul>
                </div>
            <?php endif; ?>
            <div class="button-container">
                <a href="digitador.php" class="btn-recover">Cambiar Contraseña</a>
            </div>
        </div>

       
    </div>
</body>
</html>
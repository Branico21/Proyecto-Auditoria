<?php
header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; object-src 'none'; base-uri 'self';");
header("X-Frame-Options: SAMEORIGIN");
header("X-Content-Type-Options: nosniff");
header_remove("X-Powered-By");
header_remove("Server");
header_remove("Last-Modified");
header_remove("Date");

session_set_cookie_params([
    'httponly' => true,
    'samesite' => 'Strict'
]);
session_start();

// Generar token CSRF si no existe
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Iniciar sesión
// archivo1.php
require_once __DIR__ . '/config/config.php';  // Busca config.php dentro de la carpeta config

$errors = [];
$success = false;

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Verificar token CSRF
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        $errors[] = "Token CSRF inválido.";
    } else {
        $email = trim($_POST["email"] ?? '');

        if (empty($email)) {
            $errors[] = "El correo electrónico es obligatorio.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = "El formato del correo electrónico no es válido.";
        }

        if (empty($errors)) {
            try {
                // Conectar a la base de datos PostgreSQL
                $conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

                if (!$conexion) {
                    $errors[] = "Error de conexión a la base de datos.";
                } else {
                    // Verificar si el correo existe
                    $query = "SELECT id FROM usuarios WHERE email = $1";
                    $resultado = pg_query_params($conexion, $query, array($email));

                    if ($resultado && pg_num_rows($resultado) > 0) {
                        // Aquí puedes implementar el envío de un correo con un enlace para restablecer la contraseña
                        $success = true;
                    } else {
                        $errors[] = "No se encontró una cuenta con este correo.";
                    }

                    // Cerrar conexión
                    pg_close($conexion);
                }
            } catch (Exception $e) {
                $errors[] = "Error: " . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="/Sastoque/src/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="login-container">
        <h2 class="login-title">Recuperar Contraseña</h2>
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <p>Se ha enviado un enlace de recuperación a tu correo electrónico.</p>
            </div>
        <?php else: ?>
            <form action="recuperar_contrasena.php" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                <div class="form-group">
                    <label for="email">Correo Electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-login">Enviar Enlace</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

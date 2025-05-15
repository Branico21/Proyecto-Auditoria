<?php
// Iniciar sesión
session_start();
// archivo1.php
require_once __DIR__ . '/config/config.php';  // Busca config.php dentro de la carpeta config
require_once __DIR__ . '/../vendor/autoload.php';

// Aquí puedes usar la variable $pdo para interactuar con la base de datos


// Array para almacenar errores y mensajes
$errors = [];
$message = "";

// Obtener el token de la URL
$token = $_GET["token"] ?? '';

if (empty($token)) {
    $errors[] = "Token no válido.";
}

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';

    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    } elseif (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    }

    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden.";
    }

    if (empty($errors)) {
        try {
            // Conectar a la base de datos PostgreSQL
            $conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

            if (!$conexion) {
                $errors[] = "Error de conexión a la base de datos.";
            } else {
                // Verificar el token
                $query = "SELECT id FROM usuarios WHERE reset_token = $1 AND reset_token_expiration > NOW()";
                $resultado = pg_query_params($conexion, $query, array($token));

                if (pg_num_rows($resultado) === 0) {
                    $errors[] = "El token no es válido o ha expirado.";
                } else {
                    // Actualizar la contraseña
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE usuarios SET password_hash = $1, reset_token = NULL, reset_token_expiration = NULL WHERE reset_token = $2";
                    $resultado = pg_query_params($conexion, $query, array($password_hash, $token));

                    if ($resultado) {
                        $message = "¡Contraseña restablecida con éxito! <a href='login.php'>Iniciar sesión</a>";
                    } else {
                        $errors[] = "Error al actualizar la contraseña.";
                    }
                }

                // Cerrar conexión
                pg_close($conexion);
            }
        } catch (Exception $e) {
            $errors[] = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="/Sastoque/src/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="login-container">
        <h2 class="login-title">Restablecer Contraseña</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="success-message">
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php else: ?>
            <form action="restablecer_contrasena.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                <div class="form-group">
                    <label for="password">Nueva contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar nueva contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-login">Restablecer contraseña</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

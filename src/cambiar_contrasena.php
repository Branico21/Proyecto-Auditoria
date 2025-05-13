<?php
// Iniciar sesión
session_start();
// archivo1.php
require_once __DIR__ . '/config/config.php';  // Busca config.php dentro de la carpeta config
require_once __DIR__ . '/../vendor/autoload.php';


// Aquí puedes usar la variable $pdo para interactuar con la base de datos

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}


$errors = [];
$success = false;

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $current_password = trim($_POST["current_password"] ?? '');
    $new_password = trim($_POST["new_password"] ?? '');
    $confirm_new_password = trim($_POST["confirm_new_password"] ?? '');

    if (empty($current_password)) {
        $errors[] = "La contraseña actual es obligatoria.";
    }
    if (empty($new_password)) {
        $errors[] = "La nueva contraseña es obligatoria.";
    } elseif (strlen($new_password) < 8) {
        $errors[] = "La nueva contraseña debe tener al menos 8 caracteres.";
    }
    if ($new_password !== $confirm_new_password) {
        $errors[] = "Las nuevas contraseñas no coinciden.";
    }

    if (empty($errors)) {
        try {
            // Conectar a la base de datos PostgreSQL
            $conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

            if (!$conexion) {
                $errors[] = "Error de conexión a la base de datos.";
            } else {
                // Verificar la contraseña actual
                $query = "SELECT contrasena FROM usuarios WHERE id = $1";
                $resultado = pg_query_params($conexion, $query, array($_SESSION['user_id']));

                if ($resultado && pg_num_rows($resultado) > 0) {
                    $usuario = pg_fetch_assoc($resultado);

                    if (password_verify($current_password, $usuario['contrasena'])) {
                        // Actualizar la contraseña
                        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $update_query = "UPDATE usuarios SET contrasena = $1 WHERE id = $2";
                        $update_result = pg_query_params($conexion, $update_query, array($new_password_hash, $_SESSION['user_id']));

                        if ($update_result) {
                            $success = true;
                        } else {
                            $errors[] = "Error al actualizar la contraseña.";
                        }
                    } else {
                        $errors[] = "La contraseña actual es incorrecta.";
                    }
                } else {
                    $errors[] = "Usuario no encontrado.";
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
    <title>Cambiar Contraseña</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container">
        <h2 class="login-title">Cambiar Contraseña</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <p>¡Contraseña actualizada exitosamente!</p>
                <a href="index.php" class="btn-login">Volver al Panel</a>
            </div>
        <?php else: ?>
            <form action="cambiar_contrasena.php" method="POST">
                <div class="form-group">
                    <label for="current_password">Contraseña Actual:</label>
                    <input type="password" id="current_password" name="current_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="new_password">Nueva Contraseña:</label>
                    <input type="password" id="new_password" name="new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_new_password">Confirmar Nueva Contraseña:</label>
                    <input type="password" id="confirm_new_password" name="confirm_new_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-login">Actualizar Contraseña</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>

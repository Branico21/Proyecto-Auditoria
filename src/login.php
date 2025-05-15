<?php
// Iniciar sesión
session_start();
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/../vendor/autoload.php';

$errors = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = strtolower(trim($_POST["identifier"] ?? ''));
    $password = trim($_POST["password"] ?? '');

    if (empty($identifier)) {
        $errors[] = "El correo o usuario es obligatorio.";
    }
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    }

    if (empty($errors)) {
        try {
            $conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

            if (!$conexion) {
                $errors[] = "Error de conexión a la base de datos.";
            } else {
                $query = "
                    SELECT id, username, contrasena 
                    FROM usuarios 
                    WHERE TRIM(LOWER(email)) = LOWER($1) 
                       OR TRIM(LOWER(username)) = LOWER($1) 
                    LIMIT 1";
                $resultado = pg_query_params($conexion, $query, array($identifier));

                if ($resultado && pg_num_rows($resultado) > 0) {
                    $usuario = pg_fetch_assoc($resultado);

                    if (password_verify($password, $usuario['contrasena'])) {
                        $_SESSION['user_id'] = $usuario['id'];
                        $_SESSION['username'] = $usuario['username'];
                        header("Location: index.php");
                        exit();
                    } else {
                        $errors[] = "La contraseña es incorrecta.";
                    }
                } else {
                    password_verify($password, '$2y$10$1234567890123456789012uDq7gPybjP8shnKH0Gq5fT9mBtEJvHi');
                    $errors[] = "No se encontró una cuenta con este correo o usuario.";
                }

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
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="/Sastoque/src/styles.css?v=<?php echo time(); ?>">
</head>
<body>
    <div class="login-container">
        <h2 class="login-title">Iniciar Sesión</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label for="identifier">Correo o Usuario:</label>
                <input type="text" id="identifier" name="identifier" class="form-control" placeholder="Ingresa tu correo o usuario" required>
            </div>
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Ingresa tu contraseña" required>
            </div>
            <div class="form-group">
                <button type="submit" class="btn-login">Iniciar Sesión</button>
            </div>
            <div class="form-footer">
                <a href="recuperar_contrasena.php" class="link">¿Olvidaste tu contraseña?</a>
                <a href="registro.php" class="btn-register">Registrarse</a>
            </div>
        </form>
    </div>
</body>
</html>


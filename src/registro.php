<?php
// Iniciar sesión
session_start();
// archivo1.php
require_once __DIR__ . '/config/config.php';  // Busca config.php dentro de la carpeta config
require_once __DIR__ . '/../vendor/autoload.php';


// Aquí puedes usar la variable $pdo para interactuar con la base de datos



// Array para almacenar errores
$errors = [];
$success = false;

// Verificar si se ha enviado el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener datos del formulario
    $nombres = trim($_POST["nombres"] ?? '');
    $apellidos = trim($_POST["apellidos"] ?? '');
    $email = trim($_POST["email"] ?? '');
    $username = trim($_POST["username"] ?? '');
    $tipo_documento = $_POST["tipo_documento"] ?? '';
    $documento = trim($_POST["documento"] ?? '');
    $telefono = trim($_POST["telefono"] ?? '');
    $direccion = trim($_POST["direccion"] ?? '');
    $rol = $_POST["rol"] ?? '';
    $password = $_POST["password"] ?? '';
    $confirm_password = $_POST["confirm_password"] ?? '';
    
    // Validar datos
    if (empty($nombres)) {
        $errors[] = "Los nombres son obligatorios.";
    }
    
    if (empty($apellidos)) {
        $errors[] = "Los apellidos son obligatorios.";
    }
    
    if (empty($email)) {
        $errors[] = "El correo electrónico es obligatorio.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "El formato del correo electrónico no es válido.";
    }
    
    if (empty($username)) {
        $errors[] = "El nombre de usuario es obligatorio.";
    } elseif (strlen($username) < 3) {
        $errors[] = "El nombre de usuario debe tener al menos 3 caracteres.";
    }
    
    if (empty($tipo_documento)) {
        $errors[] = "Debe seleccionar un tipo de documento.";
    }
    
    if (empty($documento)) {
        $errors[] = "El número de documento es obligatorio.";
    }
    
    if (empty($telefono)) {
        $errors[] = "El teléfono es obligatorio.";
    }
    
    if (empty($direccion)) {
        $errors[] = "La dirección es obligatoria.";
    }
    
    if (empty($rol)) {
        $errors[] = "Debe seleccionar un rol.";
    }
    
    if (empty($password)) {
        $errors[] = "La contraseña es obligatoria.";
    } elseif (strlen($password) < 8) {
        $errors[] = "La contraseña debe tener al menos 8 caracteres.";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Las contraseñas no coinciden.";
    }
    
    // Si no hay errores, proceder con el registro
    if (empty($errors)) {
        try {
            // Conectar a la base de datos PostgreSQL
            $conexion = pg_connect("host=$db_host port=$db_port dbname=$db_name user=$db_user password=$db_pass");

            if (!$conexion) {
                $errors[] = "Error de conexión a la base de datos.";
            } else {
                // Verificar si el usuario ya existe
                $query = "SELECT id FROM usuarios WHERE username = $1 OR email = $2 OR (tipo_documento = $3 AND documento = $4)";
                $resultado = pg_query_params($conexion, $query, array($username, $email, $tipo_documento, $documento));
                
                if (pg_num_rows($resultado) > 0) {
                    $errors[] = "El nombre de usuario, correo electrónico o documento ya está registrado.";
                } else {
                    // Hashear la contraseña
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    
                    // Insertar nuevo usuario
                    $query = "INSERT INTO usuarios (nombres, apellidos, email, username, tipo_documento, documento, telefono, direccion, rol, contrasena) 
                              VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)";
                    $resultado = pg_query_params($conexion, $query, array(
                        $nombres, 
                        $apellidos, 
                        $email, 
                        $username, 
                        $tipo_documento, 
                        $documento, 
                        $telefono, 
                        $direccion, 
                        $rol, 
                        $password_hash
                    ));
                    
                    if ($resultado) {
                        // Registro exitoso
                        $success = true;
                    } else {
                        $errors[] = "Error al registrar el usuario: " . pg_last_error($conexion);
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
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="login-container registro-container">
        <h2 class="login-title">Crear Cuenta</h2>
        
        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <?php foreach ($errors as $error): ?>
                    <p><?php echo htmlspecialchars($error); ?></p>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success-message">
                <p>¡Registro exitoso! <a href="login.php">Iniciar sesión</a></p>
            </div>
        <?php else: ?>
            <form action="registro.php" method="POST">
                <div class="form-group">
                    <label for="nombres">Nombres:</label>
                    <input type="text" id="nombres" name="nombres" class="form-control" value="<?php echo htmlspecialchars($nombres ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="apellidos">Apellidos:</label>
                    <input type="text" id="apellidos" name="apellidos" class="form-control" value="<?php echo htmlspecialchars($apellidos ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="email">Correo electrónico:</label>
                    <input type="email" id="email" name="email" class="form-control" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="username">Nombre de usuario:</label>
                    <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
                    <small class="help-text">Este será su nombre de usuario para iniciar sesión</small>
                </div>
                <div class="form-group">
                    <label for="tipo_documento">Tipo de documento:</label>
                    <select id="tipo_documento" name="tipo_documento" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="TI" <?php echo ($tipo_documento ?? '') === 'TI' ? 'selected' : ''; ?>>Tarjeta de Identidad</option>
                        <option value="CC" <?php echo ($tipo_documento ?? '') === 'CC' ? 'selected' : ''; ?>>Cédula de Ciudadanía</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="documento">Número de documento:</label>
                    <input type="text" id="documento" name="documento" class="form-control" value="<?php echo htmlspecialchars($documento ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono:</label>
                    <input type="tel" id="telefono" name="telefono" class="form-control" value="<?php echo htmlspecialchars($telefono ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="direccion">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" class="form-control" value="<?php echo htmlspecialchars($direccion ?? ''); ?>" required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" class="form-control" required>
                        <option value="">Seleccione...</option>
                        <option value="super_usuario" <?php echo ($rol ?? '') === 'super_usuario' ? 'selected' : ''; ?>>Super Usuario</option>
                        <option value="digitador" <?php echo ($rol ?? '') === 'digitador' ? 'selected' : ''; ?>>Digitador</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="password">Contraseña:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirmar contraseña:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" required>
                </div>
                <div class="form-group">
                    <button type="submit" class="btn-login" style="background-color: #ff5733; color: white;">Registrarse</button> <!-- Cambio de color -->
                </div>
                <div class="form-footer">
                    <a href="login.php">¿Ya tienes una cuenta? Iniciar sesión</a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
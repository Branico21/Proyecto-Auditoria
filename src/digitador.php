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

// Procesar registro manual (protegido contra inyección SQL)
if (isset($_POST['registrar_manual'])) {
    $marca = $_POST['marca'] ?? '';
    $modelo = $_POST['modelo'] ?? '';
    $serial = $_POST['serial'] ?? '';
    $categoria = $_POST['categoria'] ?? '';
    $estado = $_POST['estado'] ?? '';
    $id_persona = $_POST['id_persona'] ?? '';

    try {
        $stmt = $pdo->prepare("INSERT INTO inventario (marca, modelo, serial, categoria, estado, id_persona, responsable) 
                               VALUES (:marca, :modelo, :serial, :categoria, :estado, :id_persona, :responsable)");
        $stmt->execute([
            ':marca' => $marca,
            ':modelo' => $modelo,
            ':serial' => $serial,
            ':categoria' => $categoria,
            ':estado' => $estado,
            ':id_persona' => $id_persona,
            ':responsable'=> $nombres . ' ' . $apellidos
        ]);
        echo "<p style='color:green;'>Registro manual exitoso.</p>";
    } catch (PDOException $e) {
        echo "<p style='color:red;'>Error al registrar: " . $e->getMessage() . "</p>";
    }
}

// Procesar carga desde CSV
if (isset($_POST['cargar_csv'])) {
    if (!empty($_FILES['archivo_csv']['tmp_name'])) {
        $archivo = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
        $primera_fila = true;

        while (($datos = fgetcsv($archivo, 1000, ",")) !== FALSE) {
            if ($primera_fila) {
                $primera_fila = false;
                continue; // Saltar encabezado
            }

            if (count($datos) != 6) {
                continue; // Saltar si la fila no tiene 6 columnas
            }

            list($marca, $modelo, $serial, $categoria, $estado, $id_persona) = $datos;

            // --------- Validaciones de Seguridad ---------
            // Limpiar espacios
            $marca = trim($marca);
            $modelo = trim($modelo);
            $serial = trim($serial);
            $categoria = trim($categoria);
            $estado = trim($estado);
            $id_persona = trim($id_persona);

            // Limitar longitud de cadenas (protección)
            $marca = substr($marca, 0, 100);
            $modelo = substr($modelo, 0, 100);
            $serial = substr($serial, 0, 100);
            $categoria = substr($categoria, 0, 50);
            $estado = substr($estado, 0, 50);

            // Validar que id_persona sea numérico (sin SQL injection)
            if (!ctype_digit($id_persona)) {
                continue; // Salta esta fila si no es número
            }

            // Limpieza básica para evitar scripts maliciosos en campos de texto
            $marca = htmlspecialchars(strip_tags($marca));
            $modelo = htmlspecialchars(strip_tags($modelo));
            $serial = htmlspecialchars(strip_tags($serial));
            $categoria = htmlspecialchars(strip_tags($categoria));
            $estado = htmlspecialchars(strip_tags($estado));

            // --------- Inserción segura con PDO ---------
            $sql = "INSERT INTO inventario (marca, modelo, serial, categoria, estado, id_persona)
                    VALUES (:marca, :modelo, :serial, :categoria, :estado, :id_persona)";
            $stmt = $pdo->prepare($sql);

            $stmt->execute([
                ':marca' => $marca,
                ':modelo' => $modelo,
                ':serial' => $serial,
                ':categoria' => $categoria,
                ':estado' => $estado,
                ':id_persona' => $id_persona
            ]);
        }

        fclose($archivo);
        echo "<p style='color:green;'>Carga desde CSV completada correctamente.</p>";
    } else {
        echo "<p style='color:red;'>No se seleccionó un archivo CSV.</p>";
    }
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

    <h3>Registro Manual</h3>
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
        <input type="submit" name="registrar_manual" value="Registrar"><br>
    </form>

    <h3>Cargar desde CSV</h3>
    <form method="post" enctype="multipart/form-data">
        Selecciona archivo CSV: <input type="file" name="archivo_csv" accept=".csv" required><br>
        <input type="submit" name="cargar_csv" value="Cargar CSV"><br>
        <br>
    </form>

    <form method="get" action="mostrarinventario.php" class="boton-separado">
        <button type="submit">Mostrar Inventario</button>
    </form>

    <form method="post" action="logout.php" class="boton-separado">
        <button type="submit">Cerrar Sesión</button>
    </form>
</div>
</body>
</html>
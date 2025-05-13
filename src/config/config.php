<?php
// ============================
// 1️⃣ Habilitar visualización de errores para depuración
// ============================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ============================
// 2️⃣ Incluir el autoload de Composer
// ============================
require_once __DIR__ . '/../../vendor/autoload.php';

// ============================
// 3️⃣ Cargar las variables del archivo .env
// ============================
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// ============================
// 4️⃣ Validación de variables de entorno
// ============================
$requiredVars = ['DB_HOST', 'DB_PORT', 'DB_NAME', 'DB_USER', 'DB_PASS'];
foreach ($requiredVars as $var) {
    if (empty($_ENV[$var])) {
        die("❌ Error: La variable de entorno {$var} no está definida en el archivo .env");
    }
}

// ============================
// 5️⃣ Asignación de variables
// ============================
$db_host = $_ENV['DB_HOST'];
$db_port = $_ENV['DB_PORT'];
$db_name = $_ENV['DB_NAME'];
$db_user = $_ENV['DB_USER'];
$db_pass = $_ENV['DB_PASS'];

// ============================
// 6️⃣ Establecer la conexión a la base de datos (PostgreSQL)
// ============================
$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";

try {
    $pdo = new PDO($dsn, $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Modo excepción para errores
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,   // Modo de fetch en array asociativo
        PDO::ATTR_PERSISTENT => true                        // Conexión persistente
    ]);
} catch (PDOException $e) {
    error_log("❌ Error de conexión: " . $e->getMessage());
    die("❌ Error de conexión a la base de datos, verifica los logs para más información.");
}

<?php
/**
 * Test de Conexión a Base de Datos
 * Ejecutar este archivo para diagnosticar problemas de conexión
 */

header('Content-Type: text/html; charset=utf-8');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Test de Conexión - CIA</title>
    <style>
        body { font-family: monospace; background: #111; color: #eee; padding: 20px; }
        .success { color: #4ade80; }
        .error { color: #f87171; }
        .info { color: #60a5fa; }
        pre { background: #222; padding: 15px; border-radius: 8px; overflow-x: auto; }
        h1 { color: #fbbf24; }
        .box { background: #1a1a1a; padding: 20px; border-radius: 10px; margin: 10px 0; border: 1px solid #333; }
    </style>
</head>
<body>
    <h1>🔧 Test de Conexión CIA</h1>
    <div class='box'>";

echo "<h2>1. Verificando config.php...</h2>";

if (!file_exists('config.php')) {
    echo "<p class='error'>❌ NO SE ENCUENTRA config.php</p>";
    exit;
}

echo "<p class='success'>✅ config.php encontrado</p>";

echo "<h2>2. Intentando conectar a MySQL...</h2>";

try {
    // Cargar configuración
    require_once 'config.php';
    
    echo "<p class='info'>Host: $host</p>";
    echo "<p class='info'>Database: $db</p>";
    echo "<p class='info'>User: $user</p>";
    echo "<p class='info'>Password: " . (empty($pass) ? '(vacío)' : '******') . "</p>";
    
    echo "<h2>3. Conexión PDO...</h2>";
    
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, $options);
    
    echo "<p class='success'>✅ ¡CONEXIÓN EXITOSA!</p>";
    
    echo "<h2>4. Verificando tablas...</h2>";
    
    // Verificar cada tabla
    $tables = ['publications', 'subscribers', 'contact_messages'];
    
    foreach ($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM $table");
            $count = $stmt->fetch()['total'];
            echo "<p class='success'>✅ Tabla '$table': $count registros</p>";
        } catch (Exception $e) {
            echo "<p class='error'>❌ Tabla '$table': " . $e->getMessage() . "</p>";
        }
    }
    
    echo "<h2>5. Información del servidor...</h2>";
    echo "<p class='info'>PHP Version: " . phpversion() . "</p>";
    echo "<p class='info'>MySQL Driver: " . $pdo->getAttribute(PDO::ATTR_DRIVER_NAME) . "</p>";
    echo "<p class='info'>Server Info: " . $pdo->getAttribute(PDO::ATTR_SERVER_INFO) . "</p>";
    
} catch (PDOException $e) {
    echo "<p class='error'>❌ ERROR DE CONEXIÓN:</p>";
    echo "<pre>" . $e->getMessage() . "</pre>";
    
    echo "<h2>Posibles soluciones:</h2>";
    echo "<ul>";
    echo "<li>Verifica que las credenciales en config.php sean correctas</li>";
    echo "<li>Para Infinity Free, usa las credenciales de MySQL que te proporcionaron</li>";
    echo "<li>El host en Infinity Free NO es 'localhost' - revisa en el panel de control</li>";
    echo "</ul>";
}

echo "</div>
</body>
</html>";
?>


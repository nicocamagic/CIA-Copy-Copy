<?php
/**
 * CIA - Compañía de Arte y Vida
 * Configuración de Base de Datos MySQL
 * 
 * Instrucciones de configuración:
 * 1. XAMPP: Modifica los valores abajo con:
// $host = 'localhost';
// $db   = 'cia_arte_vida_nueva';
// $user = 'root';
// $pass = '';
// $charset = 'utf8mb4';
// 
// 2. Hosting Web: Usa las credenciales proporcionadas por tu proveedor
//    (normalmente las encuentras en cPanel > MySQL Databases)
*/

// ============================================
// CONFIGURACIÓN - MODIFICA ESTOS VALORES
// ============================================

// Configuración para XAMPP (local)
$host = 'localhost';
$db   = 'cia_arte_vida_nueva';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

// Configuración alternativa para hosting web (descomenta y modifica si es necesario):
/*
$host = 'tu_host';        // Ej: localhost o tu dominio
$db   = 'tu_usuario_cia'; // Nombre de la base de datos
$user = 'tu_usuario';     // Usuario de MySQL
$pass = 'tu_contraseña';  // Contraseña de MySQL
$charset = 'utf8mb4';
*/

// ============================================
// CONEXIÓN PDO
// ============================================

$dsn = "mysql:host=$host;port=3306;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // En producción, oculta los detalles del error
    http_response_code(500);
    header('Content-Type: application/json');
    die(json_encode([
        'success' => false, 
        'error' => 'Error de conexión a la base de datos'
    ]));
}

// Configurar zona horaria (opcional - para España/Latam)
date_default_timezone_set('America/Bogota');

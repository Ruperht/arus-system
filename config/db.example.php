<?php
/**
 * Configuración de ejemplo de la base de datos.
 *
 * Copia este archivo como "db.php" y sustituye los valores
 * por los datos de tu servidor MySQL.
 */

$host    = 'localhost';
$db      = 'nombre_base_datos';
$user    = 'tu_usuario';
$pass    = 'tu_contraseña';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$opciones = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opciones);
} catch (PDOException $e) {
    error_log($e->getMessage());
    die('No se ha podido conectar con la base de datos.');
}
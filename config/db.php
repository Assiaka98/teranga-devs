<?php
// config/db.php (Connexion explicite au service distant Systalink)

$host = 'mysql-dbaas-mysql2-teranga-devs-teranga-devs.systalinkdatabases.com';
$dbname = 'teranga_devs';
$username = 'teranga_devs_u';
$password = 'rVJCvv86PdJDTVUAWRdyXFe3';

// On teste d'abord le port 33053 (vu dans tes configurations), puis le port standard 3306
$ports = ['33053', '3306'];
$pdo = null;
$last_error = '';

foreach ($ports as $port) {
    try {
        $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 5,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        if ($pdo) {
            break; // Connexion établie avec succès !
        }
    } catch (PDOException $e) {
        $last_error = "[Port $port] " . $e->getMessage();
    }
}

if (!$pdo) {
    die(json_encode([
        'success' => false, 
        'error' => 'Erreur de connexion distante Systalink : ' . $last_error
    ]));
}

/**
 * Fonction globale pour transformer un titre en slug propre
 */
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}
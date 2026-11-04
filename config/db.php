<?php
// config/db.php (Configuration Production ultra-robuste)

$host = getenv('DB_HOST') ?: 'mysql-dbaas-mysql2-pool-mysql-10-teranga-devs.systalinkdatabases.com';
$port = getenv('DB_PORT') ?: '33053';
$dbname = getenv('DB_NAME') ?: 'teranga_devs';
$username = getenv('DB_USER') ?: 'teranga_devs_u';
$password = getenv('DB_PASS') ?: 'Q5_IPld3TcYxXjpywsE8dWtE_Fcv3A-7';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4, wait_timeout=31536000, interactive_timeout=31536000"
    ]);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Erreur de connexion : ' . $e->getMessage()]));
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


// config/db.php (Mode Local)
/*
$host = 'localhost';
$dbname = 'teranga_devs'; // Le nom de ta base locale
$username = 'root';       // Ton utilisateur local (ex: root)
$password = '';           // Ton mot de passe local (souvent vide en local)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Erreur de connexion locale : ' . $e->getMessage()]));
}
*/


 
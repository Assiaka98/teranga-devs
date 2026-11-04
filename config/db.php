<?php
// config/db.php (Connexion finale Systalink)

$host = 'mysql-dbaas-mysql2-teranga-devs-teranga-devs.systalinkdatabases.com';
$dbname = 'teranga_devs';
$username = 'teranga_devs_u';
$password = 'rVJCvv86PdJDTVUAWRdyXFe3';

try {
    // Connexion avec l'hôte complet de Systalink (port par défaut 3306)
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_PERSISTENT => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
    ]);
} catch (PDOException $e) {
    // Tentative de secours avec le port explicite 33053 si le port par défaut échoue
    try {
        $port = '33053';
        $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    } catch (PDOException $e2) {
        die(json_encode(['success' => false, 'error' => 'Erreur de connexion : ' . $e->getMessage()]));
    }
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


 
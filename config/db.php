<?php
// config/db.php (Détection dynamique d'hôte interne pour Systalink)

$dbname = getenv('DB_NAME') ?: 'teranga_devs';
$username = getenv('DB_USER') ?: 'teranga_devs_u';
$password = getenv('DB_PASS') ?: 'rVJCvv86PdJDTVUAWRdyXFe3';

// Liste des hôtes potentiels par ordre de priorité sur le réseau interne
$candidate_hosts = array_filter([
    getenv('DB_HOST'),
    'teranga-devs-db',
    'mysql',
    '127.0.0.1'
]);

$pdo = null;
$last_error = '';

foreach ($candidate_hosts as $host) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 2, // Timeout court de 2 sec par tentative
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]);
        if ($pdo) {
            break; // Connexion réussie !
        }
    } catch (PDOException $e) {
        $last_error = "[$host] " . $e->getMessage();
    }
}

if (!$pdo) {
    die(json_encode([
        'success' => false, 
        'error' => 'Impossible de joindre la base sur le réseau interne. Dernière erreur : ' . $last_error
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
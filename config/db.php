<?php
// config/db.php (Stratégie hybride : MariaDB distant avec repli SQLite local)

$dbname = 'teranga_devs';
$username = 'teranga_devs_u';
$password = 'rVJCvv86PdJDTVUAWRdyXFe3';
$host = 'mysql-dbaas-mysql2-teranga-devs-teranga-devs.systalinkdatabases.com';

$pdo = null;

// Tentative de connexion MariaDB distante
try {
    $dsn = "mysql:host=$host;port=33053;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 3
    ]);
} catch (PDOException $e1) {
    try {
        $dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 3
        ]);
    } catch (PDOException $e2) {
        // Repli automatique sur SQLite pour garantir le fonctionnement du site sur le cloud
        try {
            $sqlite_path = __DIR__ . '/teranga_devs.sqlite';
            $pdo = new PDO("sqlite:$sqlite_path", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            
            // Création automatique des tables de base si elles n'existent pas
            $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                nom TEXT NOT NULL,
                prenom TEXT NOT NULL,
                email TEXT UNIQUE NOT NULL,
                mot_de_passe TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            )");
        } catch (PDOException $e3) {
            die(json_encode(['success' => false, 'error' => 'Erreur critique de base de données : ' . $e3->getMessage()]));
        }
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
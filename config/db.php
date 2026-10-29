<?php


// config/db.php (Mode Local)

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

/*config/db.php (Configuration Production Systalink)

$host = 'mysql-dbaas-mysql2-pool-mysql-10-teranga-devs.systalinkdatabases.com';
$port = '33053';
$dbname = 'teranga_devs';
$username = 'teranga_devs_u';
$password = 'Q5_IPld3TcYxXjpywsE8dWtE_Fcv3A-7';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);
} catch (PDOException $e) {
    die(json_encode(['success' => false, 'error' => 'Erreur de connexion : ' . $e->getMessage()]));
}*/
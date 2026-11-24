<?php
// config/db.php - Connexion MariaDB distante avec Auto-migration des tables

$dbname = 'teranga_devs';
$username = 'teranga_devs_u';
$password = 'rVJCvv86PdJDTVUAWRdyXFe3';
$host = 'mysql-dbaas-mysql2-teranga-devs-teranga-devs.systalinkdatabases.com';
$port = '33091';

$pdo = null;

try {
    $dsn = "mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5
    ]);

    // Vérification et création automatique des tables si elles n'existent pas sur MariaDB
    init_mysql_complete_schema($pdo);

} catch (PDOException $e1) {
    // Repli de secours sur SQLite local si le réseau distant échoue
    try {
        $sqlite_path = __DIR__ . '/teranga_devs.sqlite';
        $db_exists = file_exists($sqlite_path);
        
        $pdo = new PDO("sqlite:$sqlite_path", null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        if (!$db_exists || filesize($sqlite_path) == 0) {
            init_sqlite_complete_schema($pdo);
        }
    } catch (PDOException $e3) {
        die(json_encode(['success' => false, 'error' => 'Erreur critique BDD : ' . $e3->getMessage()]));
    }
}

/**
 * Migration automatique des tables pour MariaDB / MySQL distant
 */
function init_mysql_complete_schema($pdo) {
    // 1. Users
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(150) NOT NULL UNIQUE,
        mot_de_passe VARCHAR(255) NOT NULL,
        reputation INT DEFAULT 150,
        karma INT DEFAULT 150,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB;");

 // 2. Articles
    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        titre VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE,
        extrait TEXT,
        contenu TEXT NOT NULL,
        couverture TEXT,
        votes INT DEFAULT 0,
        date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_article_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 3. Article Votes
    $pdo->exec("CREATE TABLE IF NOT EXISTS article_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        article_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_article_vote (article_id, user_id),
        CONSTRAINT fk_vote_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        CONSTRAINT fk_vote_article_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 4. Questions
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        titre VARCHAR(255) NOT NULL,
        contenu TEXT NOT NULL,
        tags VARCHAR(255) NOT NULL,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_question_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 5. Responses
    $pdo->exec("CREATE TABLE IF NOT EXISTS responses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        question_id INT NOT NULL,
        user_id INT NOT NULL,
        contenu TEXT NOT NULL,
        votes INT DEFAULT 0,
        est_solution TINYINT(1) DEFAULT 0,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_response_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
        CONSTRAINT fk_response_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

    // 6. Response Votes
    $pdo->exec("CREATE TABLE IF NOT EXISTS response_votes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        response_id INT NOT NULL,
        user_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_response_vote (response_id, user_id),
        CONSTRAINT fk_vote_response FOREIGN KEY (response_id) REFERENCES responses(id) ON DELETE CASCADE,
        CONSTRAINT fk_vote_response_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;");

  // Ajout sécurisé des colonnes de profil si elles n'existent pas encore
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN titre_bio VARCHAR(255) DEFAULT 'Développeur / Passionné de Tech'");
    } catch (PDOException $e) {
        // La colonne existe déjà, on ignore
    }

    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN avatar VARCHAR(500) DEFAULT NULL");
    } catch (PDOException $e) {
        // La colonne existe déjà, on ignore
    }
}

/**
 * Migration de secours pour SQLite
 */
function init_sqlite_complete_schema($pdo) {
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        prenom TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        mot_de_passe TEXT NOT NULL,
        reputation INTEGER DEFAULT 150,
        karma INTEGER DEFAULT 150,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    // ... (les autres tables SQLite restent gérées si besoin)
}

/**
 * Helper global pour générer un slug URL propre
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


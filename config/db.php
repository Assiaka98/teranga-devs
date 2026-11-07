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
        couverture VARCHAR(255),
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

    // Insertion d'un utilisateur par défaut si la table users est vide
    $checkUser = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($checkUser == 0) {
        $pass = password_hash('Passer123', PASSWORD_BCRYPT);
        $pdo->exec("INSERT INTO users (id, nom, prenom, email, mot_de_passe, reputation, karma) 
            VALUES (1, 'Traore', 'Issiaka', 'isacadel98@gmail.com', '$pass', 150, 150)");
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

// config/db.php - Connexion hybride & Auto-initialisation des tables TerangaDevs
/*
$dbname = 'teranga_devs';
$username = 'teranga_devs_u';
$password = 'rVJCvv86PdJDTVUAWRdyXFe3';
$host = 'mysql-dbaas-mysql2-teranga-devs-teranga-devs.systalinkdatabases.com';

$pdo = null;

// 1. Tentative de connexion distant MariaDB / MySQL (Ports 33053 et 3306)
try {
    $dsn = "mysql:host=$host;port=33053;dbname=$dbname;charset=utf8mb4";
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 2
    ]);
} catch (PDOException $e1) {
    try {
        $dsn = "mysql:host=$host;port=3306;dbname=$dbname;charset=utf8mb4";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_TIMEOUT => 2
        ]);
    } catch (PDOException $e2) {
        // 2. Repli automatique sur SQLite local autonome si le réseau PaaS bloque
        try {
            $sqlite_path = __DIR__ . '/teranga_devs.sqlite';
            $db_exists = file_exists($sqlite_path);
            
            $pdo = new PDO("sqlite:$sqlite_path", null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Si le fichier SQLite est nouveau ou vide, création et remplissage automatique
            if (!$db_exists || filesize($sqlite_path) == 0) {
                init_sqlite_complete_schema($pdo);
            }
        } catch (PDOException $e3) {
            die(json_encode(['success' => false, 'error' => 'Erreur critique BDD : ' . $e3->getMessage()]));
        }
    }
}

/**
 * Fonction d'initialisation du schéma complet de TerangaDevs pour SQLite
 
function init_sqlite_complete_schema($pdo) {
    // 1. Users
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

    // 2. Articles
    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        titre TEXT NOT NULL,
        slug TEXT UNIQUE,
        extrait TEXT,
        contenu TEXT NOT NULL,
        couverture TEXT,
        votes INTEGER DEFAULT 0,
        date_publication DATETIME DEFAULT CURRENT_TIMESTAMP,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 3. Article Votes
    $pdo->exec("CREATE TABLE IF NOT EXISTS article_votes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        article_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(article_id, user_id),
        FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 4. Questions
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        titre TEXT NOT NULL,
        contenu TEXT NOT NULL,
        tags TEXT NOT NULL,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 5. Responses
    $pdo->exec("CREATE TABLE IF NOT EXISTS responses (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        question_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        contenu TEXT NOT NULL,
        votes INTEGER DEFAULT 0,
        est_solution INTEGER DEFAULT 0,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // 6. Response Votes
    $pdo->exec("CREATE TABLE IF NOT EXISTS response_votes (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        response_id INTEGER NOT NULL,
        user_id INTEGER NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        UNIQUE(response_id, user_id),
        FOREIGN KEY (response_id) REFERENCES responses(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )");

    // --- SEEDING DES DONNÉES INITIALES ---

    // Utilisateur principal
    $pass = password_hash('Passer123', PASSWORD_BCRYPT);
    $pdo->exec("INSERT OR IGNORE INTO users (id, nom, prenom, email, mot_de_passe, reputation, karma) 
        VALUES (1, 'Traore', 'Issiaka', 'isacadel98@gmail.com', '$pass', 150, 150)");
    $pdo->exec("INSERT OR IGNORE INTO users (id, nom, prenom, email, mot_de_passe, reputation, karma) 
        VALUES (2, 'Tamba', 'Balla', 'balla@terangadevs.sn', '$pass', 150, 150)");    
        $pdo->exec("INSERT OR IGNORE INTO users (id, nom, prenom, email, mot_de_passe, reputation, karma) 
        VALUES (3, 'Camara', 'Khalifa', 'khalifa@terangadevs.sn', '$pass', 150, 150)");
    
    // Auteurs complémentaires pour dynamiser le forum
    $pdo->exec("INSERT OR IGNORE INTO users (id, nom, prenom, email, mot_de_passe, reputation, karma) 
        VALUES (4, 'Ndiaye', 'Awa', 'awa@terangadevs.sn', '$pass', 220, 220)");

    // Questions initiales
    $pdo->exec("INSERT INTO questions (user_id, titre, contenu, tags) VALUES 
        (1, 'Comment optimiser une API PHP pour du faible réseau ?', 'Des astuces pour réduire la taille des payloads JSON sous connexion 3G/4G instable ?', 'PHP,Performance,API'),
        (2, 'Comment résoudre une erreur de timeout SQL sur un hébergeur cloud PaaS ?', 'Bonjour la communauté ! Lors du déploiement de mon application PHP sur une instance cloud distant, j’obtiens l’erreur SQLSTATE[HY000] [2002] Connection timed out. Quelle est la meilleure approche pour gérer un fallback propre ?', 'PHP,Systalink,MariaDB'),
        (2, 'Quelle librairie utiliser pour connecter un ESP32 à un tableau de bord Node.js en WebSocket ?', 'Je travaille sur un système de capteurs environnementaux pour une serre connectée. Je cherche une méthode légère pour transmettre les métriques (température, humidité du sol) en temps réel.', 'IoT,NodeJS,ESP32')
    ");

    // Articles initiaux avec couvertures d'images
    $pdo->exec("INSERT INTO articles (user_id, titre, slug, extrait, contenu, couverture, votes) VALUES 
        (1, 'Modélisation du jeu 3 Pions en PHP', 'modelisation-du-jeu-3-pions-en-php', 'Les jeux de stratégie traditionnels de nos quartiers possèdent des mécaniques mathématiques et géométriques fascinantes adaptées au web.', 'Les jeux de stratégie traditionnels de nos quartiers possèdent des mécaniques mathématiques et géométriques fascinantes adaptées au web. Nous détaillons la matrice du jeu 3 pions et son algorithme d opportunités.', 'https://images.unsplash.com/photo-1611996575749-79a3a250f948?auto=format&fit=crop&w=800&q=80', 1),
        (2, 'Déployer avec succès une application PHP sur l’infrastructure cloud Systalink', 'deployer-application-php-systalink-cloud', 'Guide pratique pour configurer son environnement, gérer les connexions réseau et optimiser ses performances sur Systalink.', 'Le déploiement sur une plateforme PaaS moderne comme Systalink demande de bien comprendre l architecture des conteneurs isolés. Dans cet article, nous explorons la configuration des variables d environnement.', 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=800&q=80', 5),
        (2, 'Alerte Sécurité PHP : Décryptage de la dernière faille critique et correctifs', 'alerte-securite-php-derniere-faille-critique-correctifs', 'Analyse approfondie de la vulnérabilité d exécution de code à distance (RCE) découverte récemment dans PHP et mesures immédiates.', 'La sécurité des applications web back-end est un enjeu de premier plan. Une faille critique affectant l interprétation des paramètres sous certaines configurations CGI/serveur a été corrigée dans PHP 8.2 et 8.3.', 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80', 3)
    ");
}

/**
 * Helper global pour générer un slug URL propre
 
function slugify($text) {
    $text = preg_replace('~[^\pL\d]+~u', '-', $text);
    $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
    $text = preg_replace('~[^-\w]+~', '', $text);
    $text = trim($text, '-');
    $text = preg_replace('~-+~', '-', $text);
    $text = strtolower($text);
    return empty($text) ? 'n-a' : $text;
}*/
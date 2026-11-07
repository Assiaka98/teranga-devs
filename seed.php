<?php
// seed.php - Générateur complet de données (Questions + Articles avec images de couverture)
require_once 'config/db.php';

try {
    // 1. Création de la table users si absente
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        nom TEXT NOT NULL,
        prenom TEXT NOT NULL,
        email TEXT UNIQUE NOT NULL,
        mot_de_passe TEXT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 2. Création de la table questions si absente
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        titre TEXT NOT NULL,
        contenu TEXT NOT NULL,
        tags TEXT,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 3. Création de la table articles avec support d'image de couverture
    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER NOT NULL,
        titre TEXT NOT NULL,
        slug TEXT UNIQUE NOT NULL,
        extrait TEXT NOT NULL,
        contenu TEXT NOT NULL,
        couverture TEXT,
        date_publication DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    // 4. Insertion d'utilisateurs de test
    $users = [
        ['Diallo', 'Moussa', 'moussa@terangadevs.sn', password_hash('Passer123', PASSWORD_BCRYPT)],
        ['Ndiaye', 'Awa', 'awa@terangadevs.sn', password_hash('Passer123', PASSWORD_BCRYPT)],
        ['Sow', 'Fatou', 'fatou@terangadevs.sn', password_hash('Passer123', PASSWORD_BCRYPT)]
    ];

    $stmtUser = $pdo->prepare("INSERT OR IGNORE INTO users (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
    foreach ($users as $u) {
        $stmtUser->execute($u);
    }

    $userId = $pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn() ?: 1;

    // 5. Injection de questions techniques
    $questions = [
        [
            'titre' => 'Comment résoudre une erreur de timeout SQL sur un hébergeur cloud PaaS ?',
            'contenu' => 'Bonjour la communauté ! Lors du déploiement de mon application PHP sur une instance cloud distant, j’obtiens l’erreur SQLSTATE[HY000] [2002] Connection timed out. Quelle est la meilleure approche pour gérer un fallback propre ?',
            'tags' => 'PHP,Systalink,MariaDB'
        ],
        [
            'titre' => 'Quelle librairie utiliser pour connecter un ESP32 à un tableau de bord Node.js en WebSocket ?',
            'contenu' => 'Je travaille sur un système de capteurs environnementaux pour une serre connectée. Je cherche une méthode légère pour transmettre les métriques (température, humidité du sol) en temps réel.',
            'tags' => 'IoT,NodeJS,ESP32'
        ]
    ];

    $stmtQ = $pdo->prepare("INSERT INTO questions (user_id, titre, contenu, tags) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmtQ->execute([$userId, $q['titre'], $q['contenu'], $q['tags']]);
    }

    // 6. Injection d'articles phares avec images de couverture (Unsplash)
    $articles = [
        [
            'titre' => 'Déployer avec succès une application PHP sur l’infrastructure cloud Systalink',
            'slug' => 'deployer-application-php-systalink-cloud',
            'extrait' => 'Guide pratique pour configurer son environnement, gérer les connexions réseau et optimiser ses performances sur les grappes de serveurs Systalink.',
            'contenu' => 'Le déploiement sur une plateforme PaaS moderne comme Systalink demande de bien comprendre l’architecture des conteneurs isolés. Dans cet article, nous explorons comment configurer les variables d’environnement, mettre en place une stratégie de base de données hybride (MariaDB / SQLite de secours) et s’assurer que les performances web répondent aux exigences des utilisateurs en Afrique de l’Ouest.',
            'couverture' => 'https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=800&q=80'
        ],
        [
            'titre' => 'Alerte Sécurité PHP : Décryptage de la dernière faille critique et correctifs',
            'slug' => 'alerte-securite-php-derniere-faille-critique-correctifs',
            'extrait' => 'Analyse approfondie de la vulnérabilité d’exécution de code à distance (RCE) découverte récemment dans PHP et mesures correctives immédiates à appliquer.',
            'contenu' => 'La sécurité des applications web back-end est un enjeu de premier plan. Une faille critique affectant l’interprétation des paramètres de ligne de commande sous certaines configurations CGI/serveur a récemment ébranlé l’écosystème PHP. Dans cet article, nous détaillons les vecteurs d’attaque potentiels, comment identifier si votre serveur est vulnérable, et l’importance de mettre à jour vers les versions patchées (PHP 8.2.x / 8.3.x) pour sécuriser vos applications de production.',
            'couverture' => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?auto=format&fit=crop&w=800&q=80'
        ]
    ];

    $stmtArt = $pdo->prepare("INSERT OR IGNORE INTO articles (user_id, titre, slug, extrait, contenu, couverture) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($articles as $a) {
        $stmtArt->execute([$userId, $a['titre'], $a['slug'], $a['extrait'], $a['contenu'], $a['couverture']]);
    }

    echo "<div style='font-family: Arial; padding: 20px; background: #0f172a; color: #f8fafc; border-radius: 8px; max-width: 600px; margin: 40px auto;'>";
    echo "<h2 style='color: #10b981;'>✅ Base de données initialisée et alimentée avec succès !</h2>";
    echo "<p>Les tables <strong>users</strong>, <strong>questions</strong> et <strong>articles</strong> ont été créées et remplies avec des données réalistes (Systalink, Sécurité PHP, IoT).</p>";
    echo "<br><a href='index.php' style='background: #f59e0b; color: #0f172a; padding: 10px 20px; text-decoration: none; font-weight: bold; border-radius: 6px;'>Accéder à TerangaDevs</a>";
    echo "</div>";

} catch (Exception $e) {
    die("Erreur critique lors du seeding : " . $e->getMessage());
}
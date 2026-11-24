<?php
// seed.php - Script de peuplement de la base locale (SQLite / MySQL) pour la démo TerangaDevs
require_once __DIR__ . '/config/db.php';

echo "<h2>🚀 Initialisation des données de démonstration TerangaDevs...</h2>";

try {
    // 1. S'assurer que les colonnes nécessaires existent (compatibilité SQLite / MySQL)
    $columns = [
        "ALTER TABLE users ADD COLUMN karma INT DEFAULT 150",
        "ALTER TABLE users ADD COLUMN titre_bio VARCHAR(255) DEFAULT 'Fullstack Developer | TerangaDevs Member'",
        "ALTER TABLE users ADD COLUMN avatar VARCHAR(500) DEFAULT NULL"
    ];
    foreach ($columns as $sql) {
        try { $pdo->exec($sql); } catch (Exception $e) {}
    }

    // 2. Création des développeurs de la communauté
    $passwordHash = password_hash('teranga2026', PASSWORD_BCRYPT);
    
    $users = [
        [
            'nom' => 'Diouf',
            'prenom' => 'Cheikh',
            'email' => 'cheikh.diouf@terangadevs.sn',
            'karma' => 340,
            'titre_bio' => 'Ingénieur Automatisme & IoT Industriel | CFPT Sénégal-Japon'
        ],
        [
            'nom' => 'Ndiaye',
            'prenom' => 'Aminata',
            'email' => 'aminata.ndiaye@terangadevs.sn',
            'karma' => 285,
            'titre_bio' => 'Lead Backend PHP / Laravel & Architecture API FinTech'
        ],
        [
            'nom' => 'Fall',
            'prenom' => 'Moussa',
            'email' => 'moussa.fall@terangadevs.sn',
            'karma' => 195,
            'titre_bio' => 'Développeur Fullstack JS / Node.js & Systèmes Embarqués'
        ],
        [
            'nom' => 'Sow',
            'prenom' => 'Fatou',
            'email' => 'fatou.sow@terangadevs.sn',
            'karma' => 410,
            'titre_bio' => 'Cloud Architect & DevOps | Optimisation Réseaux Afrique de l\'Ouest'
        ]
    ];

    $userIds = [];
    foreach ($users as $u) {
        $stmtCheck = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmtCheck->execute([$u['email']]);
        $existing = $stmtCheck->fetch();

        if ($existing) {
            $userIds[] = $existing['id'];
        } else {
            $stmtInsert = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, karma, titre_bio) VALUES (?, ?, ?, ?, ?, ?)");
            $stmtInsert->execute([
                $u['nom'],
                $u['prenom'],
                $u['email'],
                $passwordHash,
                $u['karma'],
                $u['titre_bio']
            ]);
            $userIds[] = $pdo->lastInsertId();
        }
    }

    // Mettre à jour tous les utilisateurs à 0 de karma vers 150 par défaut
    $pdo->exec("UPDATE users SET karma = 150 WHERE karma = 0 OR karma IS NULL");

    echo "<p>✅ Utilisateurs de la communauté créés / vérifiés (" . count($userIds) . " profils actifs).</p>";

    // 3. Création des Questions Techniques
    $questions = [
        [
            'user_id' => $userIds[0],
            'titre' => 'Comment stabiliser une connexion WebSocket avec un ESP32 sur réseau 4G instable ?',
            'contenu' => "Bonjour la communauté ! Sur notre projet de serre connectée à Dakar, l'ESP32 perd parfois la connexion au serveur Node.js lors des micro-coupures du réseau mobile. Avez-vous des bonnes pratiques pour gérer le heartbeat (ping/pong) et la reconnexion automatique sans bloquer la boucle principale ?",
            'tags' => 'IoT, ESP32, WebSockets, NodeJS'
        ],
        [
            'user_id' => $userIds[1],
            'titre' => 'Intégration des webhooks Wave API en PHP natif : gestion des signatures HMAC',
            'contenu' => "Je mets en place un système de paiement mobile avec l'API Wave sur une application PHP 8. Comment vérifier proprement l'en-tête Wave-Signature pour sécuriser le callback et éviter les doubles validations de transactions ?",
            'tags' => 'PHP, WaveAPI, Sécurité, FinTech'
        ],
        [
            'user_id' => $userIds[2],
            'titre' => 'Stratégie de repli automatique MariaDB vers SQLite en PDO',
            'contenu' => "Pour garantir la haute disponibilité d'une application web locale ou hébergée sur PaaS, quelle est la meilleure architecture pour basculer automatiquement sur une base SQLite locale lorsque le serveur MariaDB distant renvoie une erreur 2006 (MySQL server has gone away) ?",
            'tags' => 'PHP, SQL, SQLite, Architecture'
        ],
        [
            'user_id' => $userIds[3],
            'titre' => 'Optimisation du chargement Tailwind CSS et des assets sur mobile 3G',
            'contenu' => "Quels sont vos retours d'expérience pour réduire au maximum le temps de premier affichage (FCP) d'une plateforme communautaire destinée aux développeurs en Afrique de l'Ouest ?",
            'tags' => 'HTML, CSS, Performance, Web'
        ]
    ];

    $questionIds = [];
    foreach ($questions as $q) {
        $stmtCheckQ = $pdo->prepare("SELECT id FROM questions WHERE titre = ?");
        $stmtCheckQ->execute([$q['titre']]);
        $existingQ = $stmtCheckQ->fetch();

        if ($existingQ) {
            $questionIds[] = $existingQ['id'];
        } else {
            $stmtQ = $pdo->prepare("INSERT INTO questions (user_id, titre, contenu, tags) VALUES (?, ?, ?, ?)");
            $stmtQ->execute([$q['user_id'], $q['titre'], $q['contenu'], $q['tags']]);
            $questionIds[] = $pdo->lastInsertId();
        }
    }

    echo "<p>✅ Questions techniques ajoutées (" . count($questionIds) . " questions).</p>";

    // 4. Ajout de Réponses aux Questions (si la table responses existe)
    try {
        $responses = [
            [
                'question_id' => $questionIds[0],
                'user_id' => $userIds[2],
                'contenu' => "Sur ESP32, utilise la bibliothèque WebSocketsClient avec `webSocket.setReconnectInterval(5000)` et active le heartbeat via `webSocket.enableHeartbeat(15000, 3000, 2)`. Cela détecte une coupure silencieuse en moins de 20 secondes sans figer tes capteurs !"
            ],
            [
                'question_id' => $questionIds[1],
                'user_id' => $userIds[3],
                'contenu' => "Récupère le payload brut avec `file_get_contents('php://input')`, extrais le timestamp `t` et la signature `v1` depuis `\$_SERVER['HTTP_WAVE_SIGNATURE']`, puis compare avec `hash_equals(\$v1, hash_hmac('sha256', \$t . \$payload, \$webhookSecret))`."
            ],
            [
                'question_id' => $questionIds[2],
                'user_id' => $userIds[1],
                'contenu' => "Encapsule ta connexion PDO dans un bloc `try/catch (PDOException \$e)` avec un `PDO::ATTR_TIMEOUT => 3`. Si la connexion distante échoue, instancie immédiatement `new PDO('sqlite:' . __DIR__ . '/fallback.sqlite')` et exécute tes `CREATE TABLE IF NOT EXISTS`."
            ]
        ];

        $respCount = 0;
        foreach ($responses as $r) {
            $stmtCheckR = $pdo->prepare("SELECT id FROM responses WHERE question_id = ? AND user_id = ?");
            $stmtCheckR->execute([$r['question_id'], $r['user_id']]);
            if (!$stmtCheckR->fetch()) {
                $stmtR = $pdo->prepare("INSERT INTO responses (question_id, user_id, contenu) VALUES (?, ?, ?)");
                $stmtR->execute([$r['question_id'], $r['user_id'], $r['contenu']]);
                $respCount++;
            }
        }
        echo "<p>✅ Réponses d'entraide insérées ($respCount nouvelles réponses).</p>";
    } catch (Exception $e) {
        // Ignorer si la structure de la table responses diffère légèrement
    }

    // 5. Création d'Articles Techniques (si la table articles existe)
    try {
        $articles = [
            [
                'user_id' => $userIds[0],
                'titre' => 'Concevoir une architecture IoT résiliente pour l\'agriculture intelligente au Sénégal',
                'slug' => 'architecture-iot-resiliente-agriculture-senegal',
                'extrait' => 'Retour d\'expérience sur le couplage ESP32, écrans tactiles Nextion et serveurs Node.js/WebSockets pour automatiser les serres et couveuses connectées.',
                'contenu' => "L'automatisation agricole en Afrique de l'Ouest impose des contraintes fortes : variations électriques, connectivité intermittente et températures élevées.

### 1. Découplage Local / Cloud
Le microcontrôleur ESP32 doit conserver une autonomie totale de régulation (température, hygrométrie, ventilation) même en cas de perte totale d'accès Internet. L'interface locale sur écran tactile Nextion assure le pilotage direct sur le terrain.

### 2. Synchronisation Temps Réel
Dès que le réseau est disponible, les télémétries sont poussées via WebSockets vers le tableau de bord web pour l'analyse historique et l'assistance agronomique."
            ],
            [
                'user_id' => $userIds[1],
                'titre' => 'Déployer des applications PHP 8 modernes avec bascule SQLite haute disponibilité',
                'slug' => 'deployer-php8-bascule-sqlite-haute-disponibilite',
                'extrait' => 'Comment éviter les interruptions de service lors des maintenances de bases de données distantes grâce à une architecture PDO hybride.',
                'contenu' => "Lors d'une démonstration ou en production sur des infrastructures cloud distribuées, une coupure de la base de données principale ne doit jamais bloquer l'expérience utilisateur.

En configurant un timeout court sur PDO MySQL/MariaDB et en basculant dynamiquement sur un fichier SQLite local auto-initialisé, l'application reste 100% fonctionnelle en lecture et écriture."
            ],
            [
                'user_id' => $userIds[2],
                'titre' => 'Digitaliser le patrimoine ludique dakarois : du jeu de rue 3 Pions au JavaScript',
                'slug' => 'digitaliser-jeu-3-pions-javascript',
                'extrait' => 'Modélisation mathématique d\'une grille 3x3 avec déplacements orthogonaux et diagonaux pour adapter un classique des quartiers de Dakar.',
                'contenu' => "Le jeu des 3 pions est un incontournable des rues de Dakar. Son adaptation numérique demande une validation stricte des graphes d'adjacence (notamment les diagonales centrales) et une intelligence artificielle capable d'anticiper les alignements en deux coups."
            ]
        ];

        $artCount = 0;
        foreach ($articles as $art) {
            $stmtCheckArt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
            $stmtCheckArt->execute([$art['slug']]);
            if (!$stmtCheckArt->fetch()) {
                $stmtArt = $pdo->prepare("INSERT INTO articles (user_id, titre, slug, extrait, contenu) VALUES (?, ?, ?, ?, ?)");
                $stmtArt->execute([
                    $art['user_id'],
                    $art['titre'],
                    $art['slug'],
                    $art['extrait'],
                    $art['contenu']
                ]);
                $artCount++;
            }
        }
        echo "<p>✅ Articles techniques publiés ($artCount nouveaux articles).</p>";
    } catch (Exception $e) {
        // Ignorer si les colonnes d'articles ont des noms légèrement différents
    }

    echo "<hr><p><strong>🎉 Base de données prête pour la démo !</strong> <a href='index.php'>Retourner à l'accueil de TerangaDevs</a></p>";

} catch (Exception $e) {
    echo "<p style='color:red;'>❌ Erreur lors du peuplement : " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

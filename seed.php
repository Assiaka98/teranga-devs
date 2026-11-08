<?php
// seed.php - Script de peuplement (seeding) de la base de données Teranga Devs
require_once __DIR__ . '/config/db.php';

// Sécurité basique : s'assurer qu'on est en ligne de commande ou protégé, ou exécutable directement
echo "🌱 Début du peuplement de la base de données...\n<br>";

try {
    // 1. Insertion d'utilisateurs fictifs supplémentaires
    $users = [
        ['Traore', 'Issiaka', 'isacadel98@gmail.com', password_hash('Passer123', PASSWORD_BCRYPT), 150, 150],
        ['Diallo', 'Balla', 'balla.diallo@terangadevs.sn', password_hash('Passer123', PASSWORD_BCRYPT), 120, 110],
        ['Ndiaye', 'Aissatou', 'aissatou.ndiaye@terangadevs.sn', password_hash('Passer123', PASSWORD_BCRYPT), 200, 185],
        ['Sow', 'Mamadou', 'mamadou.sow@terangadevs.sn', password_hash('Passer123', PASSWORD_BCRYPT), 95, 80]
    ];

    $stmtUser = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe, reputation, karma) VALUES (?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE reputation=VALUES(reputation)");
    
    foreach ($users as $u) {
        $stmtUser->execute($u);
    }
    echo "✅ Utilisateurs insérés avec succès.<br>\n";

    // Récupérer l'ID d'un utilisateur pour lui associer les articles et questions
    $userId = $pdo->query("SELECT id FROM users LIMIT 1")->fetchColumn();

    // 2. Insertion d'articles techniques
    $articles = [
        [
            'user_id' => $userId,
            'titre' => "Modélisation du jeu 3 Pions en PHP",
            'slug' => "modelisation-du-jeu-3-pions-en-php",
            'extrait' => "Les jeux de stratégie traditionnels de nos quartiers possèdent des mécanismes mathématiques fascinants adaptés au web.",
            'contenu' => "Les jeux de stratégie traditionnels de nos quartiers possèdent des mécanismes mathématiques et géométriques fascinants adaptés au web. Nous détaillons la matrice du jeu 3 pions et son algorithme d'opportunités en pur JavaScript et PHP.",
            'couverture' => "https://images.unsplash.com/photo-1611996575749-79a3a250f948?auto=format&fit=crop&w=800&q=80",
            'votes' => 3
        ],
        [
            'user_id' => $userId,
            'titre' => "Comment optimiser l'autonomie d'un ESP32 en mode Deep Sleep",
            'slug' => "optimiser-autonomie-esp32-deep-sleep",
            'extrait' => "Guide pratique pour réduire la consommation électrique de vos capteurs IoT sous Node.js et microcontrôleurs.",
            'contenu' => "Dans le cadre de projets IoT agricoles au Sénégal, l'autonomie énergétique est cruciale. L'utilisation judieuse du mode Deep Sleep de l'ESP32 couplée à un réveil par interruption externe permet d'atteindre plusieurs mois d'autonomie sur une simple batterie 18650.",
            'couverture' => "https://images.unsplash.com/photo-1518770660439-4636190af475?auto=format&fit=crop&w=800&q=80",
            'votes' => 7
        ],
        [
            'user_id' => $userId,
            'titre' => "Déployer une application PHP sur Systalink Cloud",
            'slug' => "deployer-application-php-systalink-cloud",
            'extrait' => "Tutoriel pas à pas pour configurer sa base de données MariaDB distante et optimiser son PaaS.",
            'deployer' => "Le déploiement sur le cloud souverain Systalink offre d'excellentes performances. Voyons ensemble comment configurer les variables d'environnement PDO et gérer les migrations automatiques de schémas.",
            'contenu' => "Le déploiement sur le cloud souverain Systalink offre d'excellentes performances. Voyons ensemble comment configurer les variables d'environnement PDO et gérer les migrations automatiques de schémas sans accroc.",
            'couverture' => "https://images.unsplash.com/photo-1451187580459-43490279c0fa?auto=format&fit=crop&w=800&q=80",
            'votes' => 5
        ]
    ];

    $stmtArt = $pdo->prepare("INSERT INTO articles (user_id, titre, slug, extrait, contenu, couverture, votes) VALUES (?, ?, ?, ?, ?, ?, ?) ON DUPLICATE KEY UPDATE votes=VALUES(votes)");
    
    foreach ($articles as $a) {
        $stmtArt->execute([$a['user_id'], $a['titre'], $a['slug'], $a['extrait'], $a['contenu'], $a['couverture'], $a['votes']]);
    }
    echo "✅ Articles et tutoriels insérés avec succès.<br>\n";

    // 3. Insertion de questions communautaires
    $questions = [
        [
            'user_id' => $userId,
            'titre' => "Comment optimiser une API PHP pour du faible réseau ?",
            'contenu' => "Des astuces pour réduire la taille des payloads JSON sous connexion 3G/4G instable ?",
            'tags' => "PHP,Performance,API"
        ],
        [
            'user_id' => $userId,
            'titre' => "Erreur CORS avec Wave API en JavaScript natif",
            'contenu' => "Je tente d'intégrer le widget de paiement Wave mais je bloque sur les en-têtes d'accès autorisés.",
            'tags' => "JavaScript,WaveAPI,Security"
        ]
    ];

    $stmtQ = $pdo->prepare("INSERT INTO questions (user_id, titre, contenu, tags) VALUES (?, ?, ?, ?)");
    foreach ($questions as $q) {
        $stmtQ->execute([$q['user_id'], $q['titre'], $q['contenu'], $q['tags']]);
    }
    echo "✅ Questions communautaires insérées avec succès.<br>\n";

    echo "<h3 style='color: #00853F;'>🎉 Seeding terminé avec brio ! Votre application est prête pour la démo.</h3>";
    echo "<br><a href='index.php'>Aller à l'accueil</a> | <a href='articles.php'>Voir les articles</a>";

} catch (Exception $e) {
    echo "<p style='color: red;'>Erreur lors du seeding : " . $e->getMessage() . "</p>";
}
?>
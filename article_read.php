<?php
// Appel du système de session, de sécurité et de la base de données
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/config/db.php';

// Récupération de l'ID de l'article
$article_id = $_GET['id'] ?? null;

if (!$article_id) {
    header('Location: articles.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

// 1. Traitement du clic sur le bouton Upvote EN PREMIER
if (isset($_POST['upvote']) && $user_id) {
    // Récupérer l'article pour vérifier l'auteur
    $stmtArt = $pdo->prepare("SELECT user_id FROM articles WHERE id = ?");
    $stmtArt->execute([$article_id]);
    $artData = $stmtArt->fetch();

    if ($artData) {
        // Vérifier si l'utilisateur n'a pas déjà voté et n'est pas l'auteur
        $stmtCheck = $pdo->prepare("SELECT * FROM article_votes WHERE article_id = ? AND user_id = ?");
        $stmtCheck->execute([$article_id, $user_id]);
        
        if ($stmtCheck->rowCount() == 0 && $artData['user_id'] != $user_id) {
            // Enregistrer le vote
            $insertVote = $pdo->prepare("INSERT INTO article_votes (article_id, user_id) VALUES (?, ?)");
            $insertVote->execute([$article_id, $user_id]);

            // Incrémenter les votes de l'article
            $updateStmt = $pdo->prepare("UPDATE articles SET votes = votes + 1 WHERE id = ?");
            $updateStmt->execute([$article_id]);
            
            // Incrémenter le karma de l'auteur (+5 points)
            $karmaStmt = $pdo->prepare("UPDATE users SET reputation = reputation + 5 WHERE id = ?");
            $karmaStmt->execute([$artData['user_id']]);
        }
    }
    
    // Redirection pour éviter la resoumission du formulaire
    header("Location: article_read.php?id=" . $article_id);
    exit;
}

// 2. Récupération des informations de l'article et de l'auteur
try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.nom, u.prenom, u.reputation, u.id as author_id 
        FROM articles a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.id = ?
    ");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();

    if (!$article) {
        header('Location: articles.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: articles.php');
    exit;
}

// 3. Vérification de l'état du vote APRÈS traitement
$has_voted = false;
if ($user_id) {
    $stmtVoteCheck = $pdo->prepare("SELECT * FROM article_votes WHERE article_id = ? AND user_id = ?");
    $stmtVoteCheck->execute([$article_id, $user_id]);
    $has_voted = $stmtVoteCheck->rowCount() > 0;
}

$pageTitle = $article['titre'] . " - Teranga Devs";
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        teranga: { green: '#00853F', gold: '#FCD116', red: '#E31B23' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex flex-col justify-between">

    <!-- Navbar -->
    <nav class="border-b border-slate-800 bg-slate-900/50 backdrop-blur sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            
            <div class="flex items-center space-x-6">
                <a href="index.php" class="font-bold text-xl text-amber-400">Teranga<span class="text-white">Devs</span></a>
                
                <a href="articles.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                    <span>📚</span> Articles
                </a>
            </div>
            
            <div class="flex items-center space-x-4">
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                        <span>👤</span> Mon Profil
                    </a>
                    <span class="text-slate-400 text-sm hidden md:inline">|</span>
                    <a href="logout.php" class="text-sm bg-red-600/20 text-red-400 hover:bg-red-600/30 px-3 py-1.5 rounded-lg transition">Déconnexion</a>
                <?php else: ?>
                    <a href="login.php" class="text-sm text-slate-300 hover:text-white transition">Connexion</a>
                    <a href="register.php" class="text-sm bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold px-3 py-1.5 rounded-lg transition">Inscription</a>
                <?php endif; ?>
            </div>

        </div>
    </nav>

    <!-- Contenu Principal -->
    <main class="max-w-3xl mx-auto px-4 py-10 flex-grow w-full space-y-8">
        
        <!-- Bouton Retour -->
        <div>
            <a href="articles.php" class="text-xs text-slate-400 hover:text-slate-200 transition flex items-center gap-1">
                &larr; Retour à la liste des articles
            </a>
        </div>

        <!-- En-tête de l'article -->
        <header class="space-y-4 border-b border-slate-800 pb-6">
            <h1 class="text-3xl font-extrabold text-slate-100 leading-tight">
                <?= htmlspecialchars($article['titre']) ?>
            </h1>

            <div class="flex items-center justify-between text-xs text-slate-400 pt-2">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 text-amber-400 font-bold flex items-center justify-center border border-amber-500/30">
                        <?= strtoupper(substr($article['prenom'], 0, 1) . substr($article['nom'], 0, 1)) ?>
                    </div>
                    <div>
                        <strong class="text-slate-200 block"><?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?></strong>
                        <span>Karma : <?= $article['reputation'] ?? 0 ?> points</span>
                    </div>
                </div>
                <span>Publié le <?= date('d/m/Y à H:i', strtotime($article['date_creation'])) ?></span>
            </div>
        </header>

        <!-- Corps de l'article -->
        <article class="prose prose-invert max-w-none text-slate-300 leading-relaxed space-y-4 text-base">
            <?= nl2br(htmlspecialchars($article['contenu'])) ?>
        </article>

        <!-- Bas de page article (Actions & Upvote) -->
        <div class="border-t border-slate-800 pt-6 flex justify-between items-center">
            <a href="articles.php" class="text-xs bg-slate-900 hover:bg-slate-800 text-slate-300 px-4 py-2 rounded-lg border border-slate-800 transition">
                &larr; Tous les articles
            </a>

            <!-- Gestion intelligente de l'affichage du vote -->
            <div>
                <?php if ($user_id && $user_id == $article['author_id']): ?>
                    <span class="text-xs text-slate-500 italic">C'est ton article (vote non autorisé)</span>
                <?php elseif ($has_voted): ?>
                    <div class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-2">
                        <span>✅ Article déjà voté (Utile)</span>
                        <span class="bg-emerald-500 text-slate-950 px-2 py-0.5 rounded-full font-bold"><?= $article['votes'] ?? 0 ?></span>
                    </div>
                <?php else: ?>
                    <form method="POST" class="flex items-center gap-3">
                        <button type="submit" name="upvote" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-2">
                            <span>👍 Utile / Upvote</span> 
                            <span class="bg-amber-500 text-slate-950 px-2 py-0.5 rounded-full font-bold"><?= $article['votes'] ?? 0 ?></span>
                        </button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <!-- Footer -->
    <?php
require_once 'includes/footer.php';
?>
</body>
</html>
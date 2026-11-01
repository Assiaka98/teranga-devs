<?php
session_start();
require_once 'config/db.php';

// Récupération de la liste des articles avec les infos de l'auteur
try {
    $stmt = $pdo->query("
        SELECT a.*, u.nom, u.prenom, u.reputation 
        FROM articles a 
        JOIN users u ON a.user_id = u.id 
        ORDER BY a.date_creation DESC
    ");
    $articles = $stmt->fetchAll();
} catch (Exception $e) {
    $articles = [];
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Articles & Tutoriels - Teranga Devs</title>
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
            
            <!-- Section Gauche : Logo + Lien Articles -->
            <div class="flex items-center space-x-6">
                <a href="index.php" class="font-bold text-xl text-amber-400">Teranga<span class="text-white">Devs</span></a>
                
                <a href="articles.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-amber-400 font-semibold px-3 py-1.5 rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                    <span>📚</span> Articles
                </a>
            </div>
            
            <!-- Section Droite : Actions Utilisateur -->
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
    <main class="max-w-4xl mx-auto px-4 py-8 flex-grow w-full space-y-6">
        
        <!-- En-tête de la section -->
        <div class="flex items-center justify-between border-b border-slate-800 pb-5">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">Articles & Tutoriels</h1>
                <p class="text-sm text-slate-400 mt-1">Partagez vos retours d'expérience et vos guides techniques.</p>
            </div>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="article_create.php" class="bg-gradient-to-r from-teranga-green to-emerald-600 hover:opacity-90 text-slate-950 font-bold px-4 py-2 rounded-lg text-sm transition shadow-md flex items-center gap-1.5">
                    <span>✏️</span> Rédiger un article
                </a>
            <?php endif; ?>
        </div>

        <!-- Liste des articles -->
        <div class="space-y-4">
            <?php if (empty($articles)): ?>
                <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 text-center space-y-3">
                    <p class="text-slate-400 text-sm">Aucun article n'a encore été publié.</p>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <a href="article_create.php" class="inline-block text-xs bg-amber-500 hover:bg-amber-400 text-slate-950 font-semibold px-4 py-2 rounded-lg transition">
                            Soyez le premier à rédiger un article !
                        </a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <?php foreach ($articles as $art): ?>
                    <article class="bg-slate-900/60 border border-slate-800/80 hover:border-slate-700/80 rounded-xl p-6 transition shadow-sm space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>Par <strong class="text-slate-200"><?= htmlspecialchars($art['prenom'] . ' ' . $art['nom']) ?></strong> (Karma: <?= $art['reputation'] ?? 0 ?>)</span>
                            <span><?= date('d/m/Y à H:i', strtotime($art['date_creation'])) ?></span>
                        </div>
                        
                        <h2 class="text-lg font-bold text-slate-100 hover:text-amber-400 transition">
                            <a href="article_read.php?id=<?= $art['id'] ?>">
                                <?= htmlspecialchars($art['titre']) ?>
                            </a>
                        </h2>
                        
                        <p class="text-sm text-slate-400 line-clamp-3 leading-relaxed">
                            <?= htmlspecialchars(mb_strimwidth(strip_tags($art['contenu']), 0, 180, "...")) ?>
                        </p>
                        
                        <div class="pt-2 flex justify-end">
                            <a href="article_read.php?id=<?= $art['id'] ?>" class="text-xs text-amber-400 hover:underline flex items-center gap-1 font-semibold">
                                Lire l'article complet &rarr;
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

    </main>

    <!-- Footer -->
   <?php
require_once 'includes/footer.php';
?>
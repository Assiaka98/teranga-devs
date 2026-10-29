<?php
require_once 'config/db.php';
session_start();

$tagFilter = $_GET['tag'] ?? null;
$searchQuery = $_GET['search'] ?? null;

try {
    $sql = "
        SELECT q.*, u.nom, u.prenom, u.reputation 
        FROM questions q 
        JOIN users u ON q.user_id = u.id 
        WHERE 1=1
    ";
    $params = [];

    if ($tagFilter) {
        $sql .= " AND q.tags LIKE ?";
        $params[] = "%$tagFilter%";
    }

    if ($searchQuery) {
        $sql .= " AND (q.titre LIKE ? OR q.contenu LIKE ?)";
        $params[] = "%$searchQuery%";
        $params[] = "%$searchQuery%";
    }

    $sql .= " ORDER BY q.date_creation DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $questions = $stmt->fetchAll();
} catch (Exception $e) {
    $questions = [];
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teranga Devs - La communauté des développeurs africains</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        teranga: { green: '#00853F', gold: '#FCD116', red: '#E31B23', dark: '#0f172a', card: '#1e293b' }
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex flex-col justify-between">

    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <?php session_start(); ?>
<!-- Dans la section du header de index.php -->
<div class="flex items-center space-x-4">
    <button onclick="toggleLowBandwidth()" id="bandwidth-btn" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-full border border-slate-700 transition">
        ⚡ Mode Basse conso
    </button>
    
    <?php if (isset($_SESSION['user_id'])): ?>
        <span class="text-xs text-slate-300">Salut, <strong class="text-teranga-gold"><?= htmlspecialchars($_SESSION['user_prenom']) ?></strong></span>
        <a href="logout.php" class="text-xs bg-red-500/20 text-red-400 px-3 py-1 rounded-full border border-red-500/30 hover:bg-red-500/30 transition">Déconnexion</a>
    <?php else: ?>
        <a href="login.php" class="text-xs bg-slate-800 text-slate-300 px-3 py-1 rounded-full border border-slate-700 hover:bg-slate-700 transition">Connexion</a>
        <a href="register.php" class="text-xs bg-teranga-green text-slate-950 font-bold px-3 py-1 rounded-full transition">Inscription</a>
    <?php endif; ?>
</div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-tr from-teranga-green via-teranga-gold to-teranga-red flex items-center justify-center font-bold text-slate-950 text-xl shadow-lg">
                    TD
                </div>
                <div>
                    <h1 class="font-bold text-lg tracking-tight bg-gradient-to-r from-teranga-green via-teranga-gold to-teranga-red bg-clip-text text-transparent">
                        Teranga Devs
                    </h1>
                    <p class="text-xs text-slate-400">Par les devs africains, pour les devs africains</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <button onclick="toggleLowBandwidth()" id="bandwidth-btn" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-3 py-1.5 rounded-full border border-slate-700 transition">
                    ⚡ Mode Basse conso
                </button>
                <span class="text-xs bg-teranga-green/20 text-teranga-green px-3 py-1 rounded-full font-medium border border-teranga-green/30">
                    Sénégal 🇸🇳
                </span>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full grid grid-cols-1 lg:grid-cols-4 gap-8">
        
      <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm">
    <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Tags Populaires</h3>
    <div class="flex flex-wrap gap-2">
        <a href="index.php?tag=PHP" class="text-xs bg-slate-800 hover:bg-slate-700 text-teranga-gold px-2.5 py-1 rounded-md transition">#PHP</a>
        <a href="index.php?tag=IoT" class="text-xs bg-slate-800 hover:bg-slate-700 text-teranga-green px-2.5 py-1 rounded-md transition">#IoT</a>
        <a href="index.php?tag=WaveAPI" class="text-xs bg-slate-800 hover:bg-slate-700 text-teranga-red px-2.5 py-1 rounded-md transition">#WaveAPI</a>
        <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#Systalink</a>
        <?php if ($tagFilter || $searchQuery): ?>
            <div class="w-full mt-3 pt-3 border-t border-slate-800">
                <a href="index.php" class="text-xs text-red-400 hover:underline">Réinitialiser les filtres</a>
            </div>
        <?php endif; ?>
    </div>
</div>

        <section class="lg:col-span-3 space-y-6">
            
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-200 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-teranga-gold"></span> Poser une question à la communauté
                </h3>
                <form id="question-form" class="space-y-4">
                    <input type="hidden" id="user_id" value="1">
                    <div>
                        <input type="text" id="titre" placeholder="Titre de votre question..." required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-teranga-green">
                    </div>
                    <div>
                        <textarea id="contenu" rows="3" placeholder="Décrivez votre problématique technique..." required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-teranga-green"></textarea>
                    </div>
                    <div class="flex justify-between items-center">
                        <input type="text" id="tags" placeholder="Tags (ex: PHP,IoT)" required
                            class="bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 w-1/2 focus:outline-none focus:border-teranga-green">
                        <button type="submit" class="bg-gradient-to-r from-teranga-green to-emerald-600 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition shadow-md">
                            Publier
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-4" id="questions-container">
                <form method="GET" action="index.php" class="flex gap-2 mb-4">
    <input type="text" name="search" value="<?= htmlspecialchars($searchQuery ?? '') ?>" placeholder="Rechercher une question..." 
        class="flex-grow bg-slate-900 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-teranga-green">
    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-lg text-sm transition border border-slate-700">
        Rechercher
    </button>
</form>
                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Questions Récentes</h3>
                
                <?php if (empty($questions)): ?>
                    <p class="text-slate-500 text-sm">Aucune question pour le moment.</p>
                <?php else: ?>
                    <?php foreach ($questions as $q): ?>
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm hover:border-slate-700 transition">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-400">Par <strong class="text-slate-200"><?= htmlspecialchars($q['prenom'] . ' ' . $q['nom']) ?></strong> (Karma: <?= $q['reputation'] ?>)</span>
                                <span class="text-xs text-slate-500"><?= $q['date_creation'] ?></span>
                            </div>
                            <h4 class="text-base font-bold text-slate-100 mb-2">
                                <a href="question.php?id=<?= $q['id'] ?>" class="hover:text-teranga-gold transition">
                                    <?= htmlspecialchars($q['titre']) ?>
                                </a>
                            </h4>
                            <p class="text-sm text-slate-300 mb-4"><?= nl2br(htmlspecialchars($q['contenu'])) ?></p>
                            
                            <div class="flex flex-wrap gap-2">
                                <?php foreach (explode(',', $q['tags']) as $tag): ?>
                                    <span class="text-xs bg-slate-950 text-teranga-gold px-2.5 py-0.5 rounded border border-slate-800">#<?= trim($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

        </section>
    </main>

    <footer class="border-t border-slate-800 bg-slate-900/50 py-6 text-center text-xs text-slate-500">
        <p>Teranga Devs &copy; 2026 - Conçu avec fierté pour le concours Cadev 🇸🇳</p>
    </footer>

    <!-- Inclusion du fichier JS externe -->
    <script src="assets/js/main.js"></script>
</body>
</html>
<?php
session_start();
require_once 'config/db.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';
$success = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!empty($titre) && !empty($contenu)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO articles (user_id, titre, contenu, date_creation) VALUES (?, ?, ?, NOW())");
            $stmt->execute([$user_id, $titre, $contenu]);
            
            $article_id = $pdo->lastInsertId();
            
            // Redirection vers l'article créé ou vers la liste des articles
            header("Location: article_read.php?id=" . $article_id);
            exit;
        } catch (Exception $e) {
            $error = "Une erreur est survenue lors de la publication de l'article.";
        }
    } else {
        $error = "Veuillez remplir tous les champs obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rédiger un Article - Teranga Devs</title>
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
                
                <a href="articles.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                    <span>📚</span> Articles
                </a>
            </div>
            
            <!-- Section Droite : Actions Utilisateur -->
            <div class="flex items-center space-x-4">
                <a href="profile.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <span>👤</span> Mon Profil
                </a>
                <span class="text-slate-400 text-sm hidden md:inline">|</span>
                <a href="logout.php" class="text-sm bg-red-600/20 text-red-400 hover:bg-red-600/30 px-3 py-1.5 rounded-lg transition">Déconnexion</a>
            </div>

        </div>
    </nav>

    <!-- Contenu Principal -->
    <main class="max-w-3xl mx-auto px-4 py-8 flex-grow w-full">
        
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">Rédiger un article</h1>
                <p class="text-sm text-slate-400 mt-1">Partage tes connaissances, tutoriels et retours d'expérience avec la communauté.</p>
            </div>
            <a href="articles.php" class="text-xs text-slate-400 hover:text-slate-200 transition">
                &larr; Retour aux articles
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire -->
        <form method="POST" class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-5">
            
            <!-- Titre -->
            <div>
                <label for="titre" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Titre de l'article</label>
                <input type="text" id="titre" name="titre" placeholder="Ex: Comment optimiser l'autonomie d'un ESP32 en mode Deep Sleep" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
            </div>

            <!-- Contenu -->
            <div>
                <label for="contenu" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Contenu de l'article</label>
                <textarea id="contenu" name="contenu" rows="12" placeholder="Écris ton article ici..." required
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg p-4 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition leading-relaxed"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800/80">
                <a href="articles.php" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg transition">
                    Annuler
                </a>
                <button type="submit" class="bg-gradient-to-r from-teranga-green to-emerald-600 hover:opacity-90 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition shadow-md flex items-center gap-2">
                    <span>🚀</span> Publier l'article
                </button>
            </div>

        </form>

    </main>

    <!-- Footer -->
 <?php
require_once 'includes/footer.php';
?>
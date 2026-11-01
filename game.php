<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pause Détente : 3 Pions - Teranga Devs</title>
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
                
                <a href="articles.php" class="text-sm bg-slate-800/60 hover:bg-slate-800 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg border border-slate-700/50 transition flex items-center gap-1.5">
                    <span>📚</span> Articles
                </a>

                <a href="game.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-amber-400 font-semibold px-3 py-1.5 rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                    <span>🎮</span> 3 Pions
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

    <!-- Contenu du Jeu (C'est ici que ton extrait se place) -->
    <main class="max-w-4xl mx-auto px-4 py-8 flex-grow w-full space-y-6 flex flex-col items-center">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-bold text-slate-100">Pause Détente : Jeu des 3 Pions 🇸🇳</h1>
            <p class="text-sm text-slate-400">Défie l'adversaire ou l'IA directement depuis Teranga Devs entre deux lignes de code !</p>
        </div>

        <!-- Conteneur iFrame pour afficher ton jeu Netlify -->
        <div class="w-full bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-lg flex justify-center items-center overflow-hidden">
            <iframe src="https://3-pions.netlify.app/" 
                class="w-full h-[600px] rounded-xl border border-slate-800 bg-slate-950" 
                title="Jeu 3 Pions">
            </iframe>
        </div>
    </main>

    <!-- Footer -->
<?php
require_once 'includes/footer.php';
?>
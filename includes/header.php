<?php
// Appel du système de protection et de session
require_once __DIR__ . '/auth_guard.php';
require_once __DIR__ . '/../config/db.php';

$pageTitle = $pageTitle ?? 'Teranga Devs';

// Récupération du prénom de l'utilisateur si connecté
$userPrenom = '';
if (isset($_SESSION['user_id'])) {
    try {
        $stmtUser = $pdo->prepare("SELECT prenom FROM users WHERE id = ?");
        $stmtUser->execute([$_SESSION['user_id']]);
        $userData = $stmtUser->fetch();
        if ($userData) {
            $userPrenom = $userData['prenom'];
        }
    } catch (Exception $e) {
        $userPrenom = '';
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle) ?></title>
    <!-- Chargement indispensable de Tailwind CSS -->
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
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between">

<header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-2">
        
        <!-- Menu Burger Mobile + Logo -->
        <div class="flex items-center gap-3">
            <button onclick="toggleMobileMenu()" class="text-slate-300 hover:text-white p-1.5 rounded-lg bg-slate-800 focus:outline-none lg:hidden" aria-label="Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <a href="index.php" class="font-bold text-xl text-amber-400 shrink-0">
                Teranga<span class="text-white">Devs</span>
            </a>
        </div>

        <!-- Navigation Desktop -->
        <nav class="hidden lg:flex items-center gap-2">
            <a href="articles.php" class="bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 px-3 py-2 rounded-lg flex items-center gap-1.5 transition border border-slate-700">
                📚 <span>Articles</span>
            </a>
            <a href="game.php" class="bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 px-3 py-2 rounded-lg flex items-center gap-1.5 transition border border-slate-700">
                🎮 <span>3 Pions</span>
            </a>
        </nav>

        <!-- Boutons d'action (Connexion / Inscription / Déconnexion) -->
        <div class="flex items-center gap-3 shrink-0">
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if (!empty($userPrenom)): ?>
                    <!-- Prénom cliquable menant vers profile.php -->
                    <a href="profile.php" class="text-xs text-slate-300 hover:text-amber-400 transition flex items-center gap-1.5 font-medium">
                        <span>👤</span> Salut, <span class="underline decoration-amber-400/50 underline-offset-4"><?= htmlspecialchars($userPrenom) ?></span>
                    </a>
                    <span class="text-slate-700">|</span>
                <?php endif; ?>
                <a href="logout.php" class="text-xs text-red-400 hover:underline px-2 py-1">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="text-xs text-slate-300 hover:text-white px-2.5 py-1.5">Connexion</a>
                <a href="register.php" class="bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold text-xs px-3 py-1.5 rounded-lg transition">
                    Inscription
                </a>
            <?php endif; ?>
        </div>

    </div>

    <!-- Tiroir Mobile (Menu déroulant type Stack Overflow) -->
    <div id="mobile-menu" class="hidden lg:hidden bg-slate-900 border-t border-slate-800 px-4 py-4 space-y-2.5 shadow-xl">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Navigation</div>
        
        <a href="articles.php" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-sm font-semibold text-amber-400 bg-slate-800/80 hover:bg-slate-800 border border-slate-700">
            <span>📚</span> Articles de la communauté
        </a>
        
        <a href="game.php" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-sm font-semibold text-emerald-400 bg-slate-800/80 hover:bg-slate-800 border border-slate-700">
            <span>🎮</span> 3 Pions (Dakar Game)
        </a>

        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="profile.php" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-sm font-semibold text-slate-200 bg-slate-800/80 hover:bg-slate-800 border border-slate-700">
                <span>👤</span> Mon Profil
            </a>
        <?php endif; ?>
    </div>
</header>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        menu.classList.toggle('hidden');
    }
</script>
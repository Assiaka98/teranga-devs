<?php
$pageTitle = $pageTitle ?? 'Teranga Devs';
$urls = $urls ?? [];
$baseUrl = (string) ($urls['base'] ?? '/');
$gameUrl = (string) ($urls['game'] ?? '/game');
$isLoggedIn = isset($_SESSION['user_id']);
$userPrenom = (string) ($_SESSION['prenom'] ?? 'Mon Profil');
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></title>
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
<body class="bg-slate-950 text-slate-100 min-h-screen flex flex-col justify-between selection:bg-amber-400 selection:text-slate-950">

<header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <button type="button" onclick="toggleMobileMenu()" class="text-slate-300 hover:text-white p-2 rounded-lg bg-slate-800 focus:outline-none lg:hidden" aria-label="Ouvrir le menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            <a href="<?= htmlspecialchars($baseUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="font-bold text-lg sm:text-xl text-amber-400 shrink-0 tracking-tight">
                Teranga<span class="text-white">Devs</span>
            </a>
        </div>

        <nav class="hidden lg:flex items-center gap-2">
            <a href="<?= htmlspecialchars($urls['articles'] ?? '/articles', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 px-3 py-2 rounded-lg transition border border-slate-700/60">Articles</a>
            <a href="<?= htmlspecialchars($urls['questions'] ?? '/questions', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 px-3 py-2 rounded-lg transition border border-slate-700/60">Questions</a>
            <a href="<?= htmlspecialchars($gameUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 px-3 py-2 rounded-lg transition border border-slate-700/60">3 Pions</a>
        </nav>

        <div class="flex items-center gap-2 sm:gap-3 shrink-0">
            <?php if ($isLoggedIn): ?>
                <a href="<?= htmlspecialchars($urls['profile'] ?? '/profile', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs text-slate-300 hover:text-amber-400 font-semibold transition"><?= htmlspecialchars($userPrenom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                <span class="text-slate-700">|</span>
                <a href="<?= htmlspecialchars($urls['logout'] ?? '/logout', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs text-red-400 hover:text-red-300 hover:underline px-2 py-1 transition font-medium">Déconnexion</a>
            <?php else: ?>
                <a href="<?= htmlspecialchars($urls['login'] ?? '/login', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs text-slate-300 hover:text-white px-2.5 py-1.5 transition font-medium">Connexion</a>
                <a href="<?= htmlspecialchars($urls['register'] ?? '/register', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="bg-amber-400 hover:bg-amber-300 text-slate-950 font-bold text-xs px-3 py-1.5 rounded-lg transition shadow-sm">Inscription</a>
            <?php endif; ?>
        </div>
    </div>

    <div id="mobile-menu" class="hidden lg:hidden bg-slate-900 border-t border-slate-800 px-4 py-4 space-y-2.5 shadow-xl">
        <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-1">Navigation</div>
        <a href="<?= htmlspecialchars($urls['articles'] ?? '/articles', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-sm font-semibold text-amber-400 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition">Articles de la communauté</a>
        <a href="<?= htmlspecialchars($urls['questions'] ?? '/questions', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-sm font-semibold text-slate-200 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition">Questions / Réponses</a>
        <a href="<?= htmlspecialchars($gameUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-sm font-semibold text-emerald-400 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition">3 Pions</a>
        <?php if ($isLoggedIn): ?>
            <a href="<?= htmlspecialchars($urls['profile'] ?? '/profile', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="flex items-center gap-3 py-2.5 px-3 rounded-lg text-sm font-semibold text-slate-200 bg-slate-800/80 hover:bg-slate-800 border border-slate-700/80 transition">Mon profil</a>
        <?php endif; ?>
    </div>
</header>

<script>
    function toggleMobileMenu() {
        const menu = document.getElementById('mobile-menu');
        if (menu) {
            menu.classList.toggle('hidden');
        }
    }
</script>

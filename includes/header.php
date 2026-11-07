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
<header class="bg-slate-900 border-b border-slate-800 sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4 py-3 flex items-center justify-between gap-2">
        
        <!-- Logo -->
        <a href="index.php" class="font-bold text-xl text-amber-400 shrink-0">
            Teranga<span class="text-white">Devs</span>
        </a>

        <!-- Liens centraux (scrollable proprement sur mobile si l'écran est petit) -->
        <nav class="flex items-center gap-2 overflow-x-auto no-scrollbar py-1">
            <a href="articles.php" class="bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shrink-0 transition">
                📚 <span>Articles</span>
            </a>
            <a href="game.php" class="bg-slate-800 hover:bg-slate-700 text-xs text-slate-200 px-3 py-1.5 rounded-lg flex items-center gap-1.5 shrink-0 transition">
                🎮 <span>3 Pions</span>
            </a>
        </nav>

        <!-- Boutons d'action (Connexion / Inscription) -->
        <div class="flex items-center gap-2 shrink-0">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout.php" class="text-xs text-red-400 hover:underline">Déconnexion</a>
            <?php else: ?>
                <a href="login.php" class="text-xs text-slate-300 hover:text-white px-2 py-1">Connexion</a>
                <a href="register.php" class="bg-amber-400 hover:bg-amber-500 text-slate-950 font-bold text-xs px-3 py-1.5 rounded-lg transition">
                    Inscription
                </a>
            <?php endif; ?>
        </div>

    </div>
</header>
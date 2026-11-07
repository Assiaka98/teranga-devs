<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Vérification de la session et de la sécurité
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/config/db.php';

$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    header('Location: login.php');
    exit;
}

$user = null;
$questions = [];
$articles = [];

try {
    // 1. Récupération des informations de l'utilisateur
    $stmtUser = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmtUser->execute([$user_id]);
    $user = $stmtUser->fetch();

    if (!$user) {
        session_destroy();
        header('Location: login.php');
        exit;
    }

    // 2. Récupération des questions posées par l'utilisateur
    $stmtQ = $pdo->prepare("SELECT * FROM questions WHERE user_id = ? ORDER BY date_creation DESC");
    $stmtQ->execute([$user_id]);
    $questions = $stmtQ->fetchAll();

    // 3. Récupération des articles rédigés par l'utilisateur
    $stmtA = $pdo->prepare("SELECT * FROM articles WHERE user_id = ? ORDER BY date_creation DESC");
    $stmtA->execute([$user_id]);
    $articles = $stmtA->fetchAll();

} catch (Exception $e) {
    die("Erreur de chargement du profil : " . $e->getMessage());
}

$pageTitle = "Mon Profil - Teranga Devs";
require_once __DIR__ . '/includes/header.php';
?>

<main class="max-w-4xl mx-auto px-4 py-8 space-y-8">
    
    <!-- En-tête du Profil -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl">
        <div class="flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl bg-amber-500/20 text-amber-400 font-bold text-2xl flex items-center justify-center border border-amber-500/30">
                <?= strtoupper(substr($user['prenom'] ?? 'U', 0, 1) . substr($user['nom'] ?? 'D', 0, 1)) ?>
            </div>
            <div class="space-y-1 text-center sm:text-left">
                <h1 class="text-xl sm:text-2xl font-bold text-slate-100">
                    <?= htmlspecialchars(($user['prenom'] ?? '') . ' ' . ($user['nom'] ?? '')) ?>
                </h1>
                <p class="text-xs text-slate-400"><?= htmlspecialchars($user['email'] ?? '') ?></p>
            </div>
        </div>

        <!-- Badges Karma / Réputation -->
        <div class="flex items-center gap-3 bg-slate-950 px-4 py-3 rounded-xl border border-slate-800">
            <div class="text-center px-3 border-r border-slate-800">
                <span class="block text-xs text-slate-400 uppercase tracking-wider">Karma</span>
                <span class="text-lg font-bold text-amber-400"><?= $user['karma'] ?? $user['reputation'] ?? 0 ?></span>
            </div>
            <div class="text-center px-3">
                <span class="block text-xs text-slate-400 uppercase tracking-wider">Articles</span>
                <span class="text-lg font-bold text-emerald-400"><?= count($articles) ?></span>
            </div>
        </div>
    </div>

    <!-- Section : Mes Articles -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-2 flex items-center justify-between">
            <span>📚 Mes Articles Rédigés</span>
            <a href="article_create.php" class="text-xs bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold px-3 py-1.5 rounded-lg transition">
                + Rédiger un article
            </a>
        </h2>

        <?php if (!empty($articles)): ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <?php foreach ($articles as $art): ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-2 flex flex-col justify-between">
                        <div>
                            <span class="text-[10px] text-emerald-400 font-bold uppercase"><?= htmlspecialchars($art['date_creation'] ?? '') ?></span>
                            <h3 class="font-semibold text-slate-100 text-sm hover:text-amber-400 transition">
                                <a href="article_read.php?slug=<?= htmlspecialchars($art['slug'] ?? '') ?>">
                                    <?= htmlspecialchars($art['titre']) ?>
                                </a>
                            </h3>
                            <p class="text-xs text-slate-400 line-clamp-2 mt-1"><?= htmlspecialchars($art['extrait'] ?? $art['contenu']) ?></p>
                        </div>
                        <div class="pt-2 border-t border-slate-800 flex justify-between items-center text-xs">
                            <span class="text-amber-400">👍 <?= $art['votes'] ?? 0 ?> votes</span>
                            <a href="article_read.php?slug=<?= htmlspecialchars($art['slug'] ?? '') ?>" class="text-slate-300 hover:underline">Voir &rarr;</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-slate-400 text-xs">
                Tu n'as pas encore rédigé d'articles.
            </div>
        <?php endif; ?>
    </div>

    <!-- Section : Mes Questions -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-2">❓ Mes Questions Posées</h2>

        <?php if (!empty($questions)): ?>
            <div class="space-y-3">
                <?php foreach ($questions as $q): ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                        <div class="space-y-1">
                            <h3 class="font-semibold text-slate-100 text-sm">
                                <?= htmlspecialchars($q['titre']) ?>
                            </h3>
                            <p class="text-xs text-slate-400 line-clamp-1"><?= htmlspecialchars($q['contenu']) ?></p>
                        </div>
                        <span class="text-[10px] bg-slate-950 text-slate-300 px-2.5 py-1 rounded-full border border-slate-800 whitespace-nowrap">
                            <?= htmlspecialchars($q['date_creation']) ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-slate-400 text-xs">
                Tu n'as posé aucune question pour le moment.
            </div>
        <?php endif; ?>
    </div>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
<?php
$pageTitle = "Articles & Tutoriels - Teranga Devs";
require_once 'config/db.php';
session_start();

try {
    $stmt = $pdo->query("SELECT a.*, u.nom, u.prenom FROM articles a JOIN users u ON a.user_id = u.id ORDER BY a.date_publication DESC");
    $articles = $stmt->fetchAll();
} catch (Exception $e) {
    $articles = [];
}

require_once 'includes/header.php';
?>

<main class="max-w-7xl mx-auto px-4 py-8 space-y-6">
    
    <!-- En-tête de section -->
    <div class="border-b border-slate-800 pb-4">
        <h1 class="text-2xl font-bold text-amber-400">Articles & Tutoriels</h1>
        <p class="text-xs text-slate-400 mt-1">Partagez vos retours d'expérience, analyses de sécurité et guides techniques.</p>
    </div>

    <!-- Grille des Articles -->
    <?php if (!empty($articles)): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($articles as $art): ?>
                <article class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden flex flex-col justify-between hover:border-slate-700 transition">
                    
                    <?php if (!empty($art['couverture'])): ?>
                        <div class="h-48 overflow-hidden bg-slate-950">
                            <img src="<?= htmlspecialchars($art['couverture']) ?>" alt="Couverture" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    <?php endif; ?>

                    <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
                        <div class="space-y-2">
                            <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-400 bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-800/50">Tutoriel</span>
                            <h2 class="text-lg font-semibold text-slate-100 hover:text-amber-400 transition">
                                <a href="article_read.php?slug=<?= htmlspecialchars($art['slug']) ?>">
                                    <?= htmlspecialchars($art['titre']) ?>
                                </a>
                            </h2>
                            <p class="text-xs text-slate-400 line-clamp-3">
                                <?= htmlspecialchars($art['extrait']) ?>
                            </p>
                        </div>

                        <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Par <strong class="text-slate-200"><?= htmlspecialchars($art['prenom'] . ' ' . $art['nom']) ?></strong></span>
                            <a href="article_read.php?slug=<?= htmlspecialchars($art['slug']) ?>" class="text-amber-400 font-semibold hover:underline">Lire l'article &rarr;</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 text-center text-slate-500 text-sm">
            Aucun article n'a encore été publié.
        </div>
    <?php endif; ?>

</main>

<?php
require_once 'includes/footer.php';
?>
<?php
$pageTitle = "Teranga Devs - La communauté des développeurs africains";
require_once 'config/db.php';
session_start();

// Récupération des filtres de recherche ou de tags si présents
$tagFilter = $_GET['tag'] ?? null;
$searchQuery = $_GET['q'] ?? null;

try {
    if ($tagFilter) {
        $stmt = $pdo->prepare("SELECT q.*, u.nom, u.prenom FROM questions q JOIN users u ON q.user_id = u.id WHERE q.tags LIKE ? ORDER BY q.date_creation DESC");
        $stmt->execute(["%$tagFilter%"]);
    } elseif ($searchQuery) {
        $stmt = $pdo->prepare("SELECT q.*, u.nom, u.prenom FROM questions q JOIN users u ON q.user_id = u.id WHERE q.titre LIKE ? OR q.contenu LIKE ? ORDER BY q.date_creation DESC");
        $stmt->execute(["%$searchQuery%", "%$searchQuery%"]);
    } else {
        $stmt = $pdo->query("SELECT q.*, u.nom, u.prenom FROM questions q JOIN users u ON q.user_id = u.id ORDER BY q.date_creation DESC");
    }
    $questions = $stmt->fetchAll();
} catch (Exception $e) {
    $questions = [];
}

require_once 'includes/header.php';
?>

<!-- Contenu Principal de la page d'accueil -->
<main class="max-w-7xl mx-auto px-4 py-6 space-y-6">

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        
        <!-- Sidebar Tags -->
        <aside class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-xl p-4 h-fit">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">Tags populaires</h2>
            <div class="flex flex-wrap gap-2">
             <a href="index.php?tag=PHP" class="text-xs bg-slate-800 hover:bg-slate-700 text-amber-400 px-2.5 py-1 rounded-md transition">#PHP</a>
            <a href="index.php?tag=IoT" class="text-xs bg-slate-800 hover:bg-slate-700 text-emerald-400 px-2.5 py-1 rounded-md transition">#IoT</a>
            <a href="index.php?tag=WaveAPI" class="text-xs bg-slate-800 hover:bg-slate-700 text-red-400 px-2.5 py-1 rounded-md transition">#WaveAPI</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#Systalink</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#C</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#Python</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#JavaScript</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#HTML</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#CSS</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#Go</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#SQL</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#NoSQL</a>    
                <?php if ($tagFilter || $searchQuery): ?>
                    <div class="w-full mt-2 pt-2 border-t border-slate-800">
                        <a href="index.php" class="text-xs text-amber-400 hover:underline">Réinitialiser les filtres</a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <!-- Zone Principale : Formulaire + Questions -->
        <section class="lg:col-span-3 space-y-6">

            <!-- Formulaire de question -->
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 sm:p-6 space-y-4">
                <h2 class="text-sm font-semibold text-amber-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> Poser une question à la communauté
                </h2>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <form action="post_question.php" method="POST" class="space-y-3">
                        <input type="text" name="titre" placeholder="Titre de votre question..." required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none">
                        
                        <textarea name="contenu" rows="3" placeholder="Décrivez votre problématique technique..." required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none resize-none"></textarea>
                        
                        <!-- Bouton & Tags responsives -->
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <input type="text" name="tags" placeholder="Tags (ex: PHP, IoT)"
                                class="w-full sm:w-2/3 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none">
                            
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-sm px-6 py-2 rounded-lg transition w-full sm:w-auto">
                                Publier
                            </button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-xs text-slate-400">Tu dois être <a href="login.php" class="text-amber-400 underline font-semibold">connecté</a> pour poser une question à la communauté.</p>
                <?php endif; ?>
            </div>

            <!-- Barre de Recherche Responsive -->
            <form method="GET" action="index.php" class="flex flex-col sm:flex-row gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($searchQuery ?? '') ?>" placeholder="Rechercher une question par mot-clé..."
                    class="w-full bg-slate-900 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none">
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold px-5 py-2.5 rounded-lg transition shrink-0">
                    Rechercher
                </button>
            </form>

            <!-- Liste des questions -->
            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">Questions récentes</h3>
                
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $q): ?>
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 sm:p-5 space-y-2 hover:border-slate-700 transition">
                            <a href="question.php?id=<?= $q['id'] ?>" class="text-base font-semibold text-amber-400 hover:underline block">
                                <?= htmlspecialchars($q['titre']) ?>
                            </a>
                            <p class="text-xs text-slate-300 line-clamp-2">
                                <?= htmlspecialchars($q['contenu']) ?>
                            </p>
                            <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-800/60 gap-2">
                                <span>Par <strong class="text-slate-200"><?= htmlspecialchars($q['prenom'] . ' ' . $q['nom']) ?></strong> le <?= htmlspecialchars($q['date_creation'] ?? '') ?></span>
                                <?php if (!empty($q['tags'])): ?>
                                    <span class="bg-slate-800 text-emerald-400 px-2 py-0.5 rounded"><?= htmlspecialchars($q['tags']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 text-center text-slate-500 text-sm">
                        Aucune question ne correspond à votre recherche.
                    </div>
                <?php endif; ?>
            </div>

        </section>

    </div>

</main>

<?php
require_once 'includes/footer.php';
?>
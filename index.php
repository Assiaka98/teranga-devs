<?php
$pageTitle = "Teranga Devs - La communauté des développeurs africains";
require_once 'includes/header.php';

// Récupération des filtres de recherche ou de tags si présents
$tagFilter = $_GET['tag'] ?? null;
$searchQuery = $_GET['search'] ?? null;

try {
    if ($tagFilter) {
        $stmt = $pdo->prepare("SELECT q.*, u.nom, u.prenom, u.reputation FROM questions q JOIN users u ON q.user_id = u.id WHERE q.tags LIKE ? ORDER BY q.date_creation DESC");
        $stmt->execute(["%$tagFilter%"]);
    } elseif ($searchQuery) {
        $stmt = $pdo->prepare("SELECT q.*, u.nom, u.prenom, u.reputation FROM questions q JOIN users u ON q.user_id = u.id WHERE q.titre LIKE ? OR q.contenu LIKE ? ORDER BY q.date_creation DESC");
        $stmt->execute(["%$searchQuery%", "%$searchQuery%"]);
    } else {
        $stmt = $pdo->query("SELECT q.*, u.nom, u.prenom, u.reputation FROM questions q JOIN users u ON q.user_id = u.id ORDER BY q.date_creation DESC");
    }
    $questions = $stmt->fetchAll();
} catch (Exception $e) {
    $questions = [];
}
?>

<!-- Contenu Principal de la page d'accueil -->
<main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex-grow w-full grid grid-cols-1 lg:grid-cols-4 gap-8">
        
      <!-- Sidebar Tags -->
      <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 shadow-sm h-fit">
        <h3 class="text-xs font-semibold uppercase tracking-wider text-slate-400 mb-3">Tags Populaires</h3>
        <div class="flex flex-wrap gap-2">
            <a href="index.php?tag=PHP" class="text-xs bg-slate-800 hover:bg-slate-700 text-amber-400 px-2.5 py-1 rounded-md transition">#PHP</a>
            <a href="index.php?tag=IoT" class="text-xs bg-slate-800 hover:bg-slate-700 text-emerald-400 px-2.5 py-1 rounded-md transition">#IoT</a>
            <a href="index.php?tag=WaveAPI" class="text-xs bg-slate-800 hover:bg-slate-700 text-red-400 px-2.5 py-1 rounded-md transition">#WaveAPI</a>
            <a href="index.php?tag=Systalink" class="text-xs bg-slate-800 hover:bg-slate-700 text-blue-400 px-2.5 py-1 rounded-md transition">#Systalink</a>
            <?php if ($tagFilter || $searchQuery): ?>
                <div class="w-full mt-3 pt-3 border-t border-slate-800">
                    <a href="index.php" class="text-xs text-red-400 hover:underline">Réinitialiser les filtres</a>
                </div>
            <?php endif; ?>
        </div>
      </div>

      <!-- Section Centrale : Formulaire + Liste des questions -->
      <section class="lg:col-span-3 space-y-6">
            
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
                <h3 class="text-sm font-semibold text-slate-200 mb-4 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> Poser une question à la communauté
                </h3>
                <form id="question-form" class="space-y-4">
                    <input type="hidden" id="user_id" value="<?= $_SESSION['user_id'] ?? 1 ?>">
                    <div>
                        <input type="text" id="titre" placeholder="Titre de votre question..." required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500">
                    </div>
                    <div>
                        <textarea id="contenu" rows="3" placeholder="Décrivez votre problématique technique..." required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-100 focus:outline-none focus:border-emerald-500"></textarea>
                    </div>
                    <div class="flex justify-between items-center">
                        <input type="text" id="tags" placeholder="Tags (ex: PHP,IoT)" required
                            class="bg-slate-950 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 w-1/2 focus:outline-none focus:border-emerald-500">
                        <button type="submit" class="bg-gradient-to-r from-emerald-600 to-teal-600 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition shadow-md">
                            Publier
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-4" id="questions-container">
                <!-- Barre de recherche -->
                <form method="GET" action="index.php" class="flex gap-2">
                    <input type="text" name="search" value="<?= htmlspecialchars($searchQuery ?? '') ?>" placeholder="Rechercher une question par mot-clé..." 
                        class="flex-grow bg-slate-900 border border-slate-800 rounded-lg px-4 py-2 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-emerald-500">
                    <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 px-4 py-2 rounded-lg text-sm transition border border-slate-700">
                        Rechercher
                    </button>
                    <?php if (!empty($searchQuery)): ?>
                        <a href="index.php" class="bg-slate-900 hover:bg-slate-800 text-red-400 px-4 py-2 rounded-lg text-sm transition border border-slate-800 flex items-center">
                            Effacer
                        </a>
                    <?php endif; ?>
                </form>

                <?php if (!empty($searchQuery)): ?>
                    <div class="text-xs text-slate-400">
                        Résultats pour : <span class="text-amber-400 font-semibold">"<?= htmlspecialchars($searchQuery) ?>"</span> (<?= count($questions) ?> trouvé(s))
                    </div>
                <?php endif; ?>

                <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400 pt-2">Questions Récentes</h3>
                
                <?php if (empty($questions)): ?>
                    <p class="text-slate-500 text-sm bg-slate-900/40 p-4 rounded-xl border border-slate-800/80">Aucune question ne correspond à votre recherche.</p>
                <?php else: ?>
                    <?php foreach ($questions as $q): ?>
                        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm hover:border-slate-700 transition space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-slate-400">Par <strong class="text-slate-200"><?= htmlspecialchars($q['prenom'] . ' ' . $q['nom']) ?></strong> (Karma: <?= $q['reputation'] ?>)</span>
                                <span class="text-xs text-slate-500"><?= $q['date_creation'] ?></span>
                            </div>
                           <h4 class="text-base font-bold text-slate-100">
                               <a href="question.php?id=<?= $q['id'] ?>" class="hover:text-amber-400 transition">
                                    <?= htmlspecialchars($q['titre']) ?>
                                </a>
                            </h4>
                            <p class="text-sm text-slate-300"><?= nl2br(htmlspecialchars($q['contenu'])) ?></p>
                            
                            <div class="flex flex-wrap gap-2 pt-2">
                                <?php foreach (explode(',', $q['tags']) as $tag): ?>
                                    <span class="text-xs bg-slate-950 text-amber-400 px-2.5 py-0.5 rounded border border-slate-800"><?= trim($tag) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

      </section>
</main>

<?php
require_once 'includes/footer.php';
?>
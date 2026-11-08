<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Appel du système de session, de sécurité et de la base de données
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/config/db.php';

// Récupération du slug OU de l'ID depuis la requête GET
$slug = $_GET['slug'] ?? null;
$article_id = $_GET['id'] ?? null;

if (!$slug && !$article_id) {
    header('Location: articles.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;
$error_msg = '';

// Récupération des informations de l'article (par slug ou par id)
try {
    if ($slug) {
        $stmt = $pdo->prepare("
            SELECT a.*, u.nom, u.prenom, u.reputation, u.id as author_id 
            FROM articles a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.slug = ?
        ");
        $stmt->execute([$slug]);
    } else {
        $stmt = $pdo->prepare("
            SELECT a.*, u.nom, u.prenom, u.reputation, u.id as author_id 
            FROM articles a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.id = ?
        ");
        $stmt->execute([$article_id]);
    }

    $article = $stmt->fetch();
    if (!$article) {
        header('Location: articles.php');
        exit;
    }
    
    $article_id = $article['id'];

} catch (Exception $e) {
    die("Erreur SQL : " . $e->getMessage());
}

// Gestion des messages d'erreur passés dans l'URL
if (isset($_GET['error']) && $_GET['error'] === 'already_voted') {
    $error_msg = "Vous avez déjà voté pour cet article ! Un seul vote par utilisateur est autorisé.";
}

// Traitement sécurisé du vote de l'article
if (isset($_POST['upvote'])) {
    if (!$user_id) {
        header('Location: login.php?error=unauthorized');
        exit;
    }

    try {
        // Vérifier si l'utilisateur a déjà voté
        $stmtCheck = $pdo->prepare("SELECT * FROM article_votes WHERE article_id = ? AND user_id = ?");
        $stmtCheck->execute([$article_id, $user_id]);
        
        if ($stmtCheck->rowCount() > 0) {
            $redirectUrl = (!empty($article['slug']) ? "article_read.php?slug=" . $article['slug'] : "article_read.php?id=" . $article_id) . "&error=already_voted";
            header("Location: " . $redirectUrl);
            exit;
        }

        if ($article['author_id'] == $user_id) {
            $redirectUrl = (!empty($article['slug']) ? "article_read.php?slug=" . $article['slug'] : "article_read.php?id=" . $article_id) . "&error=self_vote";
            header("Location: " . $redirectUrl);
            exit;
        }

        // Enregistrer le vote
        $insertVote = $pdo->prepare("INSERT INTO article_votes (article_id, user_id) VALUES (?, ?)");
        $insertVote->execute([$article_id, $user_id]);

        // Incrémenter les votes de l'article
        $updateStmt = $pdo->prepare("UPDATE articles SET votes = votes + 1 WHERE id = ?");
        $updateStmt->execute([$article_id]);
        
        // Incrémenter la réputation de l'auteur (+5 points)
        $karmaStmt = $pdo->prepare("UPDATE users SET reputation = reputation + 5 WHERE id = ?");
        $karmaStmt->execute([$article['author_id']]);

    } catch (Exception $e) {
        // En cas de violation de contrainte unique (double clic simultané)
        $redirectUrl = (!empty($article['slug']) ? "article_read.php?slug=" . $article['slug'] : "article_read.php?id=" . $article_id) . "&error=already_voted";
        header("Location: " . $redirectUrl);
        exit;
    }
    
    // Redirection propre après succès
    $redirectUrl = !empty($article['slug']) ? "article_read.php?slug=" . $article['slug'] : "article_read.php?id=" . $article_id;
    header("Location: " . $redirectUrl);
    exit;
}

// Vérification si déjà voté pour adapter l'affichage du bouton
$has_voted = false;
if ($user_id) {
    $stmtVoteCheck = $pdo->prepare("SELECT * FROM article_votes WHERE article_id = ? AND user_id = ?");
    $stmtVoteCheck->execute([$article_id, $user_id]);
    $has_voted = $stmtVoteCheck->rowCount() > 0;
}

$pageTitle = $article['titre'] . " - Teranga Devs";
require_once __DIR__ . '/includes/header.php';
?>

<main class="max-w-4xl mx-auto px-4 py-8 space-y-6">
    
    <!-- Bouton Retour -->
    <div>
        <a href="articles.php" class="text-xs text-amber-400 hover:underline inline-flex items-center gap-1">
            &larr; Retour aux articles
        </a>
    </div>
    <!-- Actions Auteur (Modifier / Supprimer) -->
<?php if ($user_id && $user_id == $article['author_id']): ?>
    <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
        <a href="article_edit.php?id=<?= $article['id'] ?>" class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
            <span>✏️</span> Modifier
        </a>
        <a href="article_delete.php?id=<?= $article['id'] ?>" onclick="return confirm('Es-tu sûr de vouloir supprimer définitivement cet article ?');" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 px-3 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5">
            <span>🗑️</span> Supprimer
        </a>
    </div>
<?php endif; ?>
    <!-- Message d'erreur / Alerte -->
    <?php if (!empty($error_msg)): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs sm:text-sm p-4 rounded-xl flex items-center gap-3 shadow-lg">
            <span>⚠️</span>
            <span><?= htmlspecialchars($error_msg) ?></span>
        </div>
    <?php endif; ?>

    <!-- Article complet -->
    <article class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden space-y-6 shadow-xl">
        
        <?php if (!empty($article['couverture'])): ?>
            <div class="w-full h-72 sm:h-96 bg-slate-950 overflow-hidden">
                <img src="<?= htmlspecialchars($article['couverture']) ?>" alt="Couverture" class="w-full h-full object-cover">
            </div>
        <?php endif; ?>

        <div class="p-6 sm:p-8 space-y-6">
            
            <div class="space-y-3">
                <span class="text-[11px] uppercase tracking-wider font-bold text-emerald-400 bg-emerald-950/50 px-2.5 py-1 rounded border border-emerald-800/50">Tutoriel Technique</span>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-100 leading-tight">
                    <?= htmlspecialchars($article['titre']) ?>
                </h1>
                
                <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-800">
                    <span>Publié par <strong class="text-slate-200"><?= htmlspecialchars($article['prenom'] . ' ' . $article['nom']) ?></strong> (Karma : <?= $article['reputation'] ?? 0 ?>)</span>
                    <span><?= htmlspecialchars($article['date_publication'] ?? $article['date_creation'] ?? '') ?></span>
                </div>
            </div>

            <!-- Contenu -->
            <div class="text-slate-300 text-sm sm:text-base leading-relaxed space-y-4 pt-2">
                <p><?= nl2br(htmlspecialchars($article['contenu'])) ?></p>
            </div>

            <!-- Actions de Vote -->
            <div class="border-t border-slate-800 pt-4 flex justify-between items-center">
                <a href="articles.php" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg transition">
                    &larr; Retour
                </a>

                <div>
                    <?php if (!$user_id): ?>
                        <a href="login.php" class="bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 px-4 py-2 rounded-lg text-xs font-semibold transition inline-block">
                            🔑 Connecte-toi pour voter
                        </a>
                    <?php elseif ($user_id == $article['author_id']): ?>
                        <span class="text-xs text-slate-500 italic">C'est ton propre article</span>
                    <?php elseif ($has_voted): ?>
                        <div class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-4 py-2 rounded-lg text-xs font-semibold flex items-center gap-2">
                            <span>✅ Article marqué comme utile</span>
                            <span class="bg-emerald-500 text-slate-950 px-2 py-0.5 rounded-full font-bold"><?= $article['votes'] ?? 0 ?></span>
                        </div>
                    <?php else: ?>
                        <form method="POST">
                            <button type="submit" name="upvote" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 px-4 py-2 rounded-lg text-xs font-semibold transition flex items-center gap-2">
                                <span>👍 Voter (Utile)</span> 
                                <span class="bg-amber-500 text-slate-950 px-2 py-0.5 rounded-full font-bold"><?= $article['votes'] ?? 0 ?></span>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </article>

</main>

<?php
require_once __DIR__ . '/includes/footer.php';
?>
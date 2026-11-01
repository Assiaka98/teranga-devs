<?php
session_start();
require_once 'config/db.php';

// 1. Récupération sécurisée de l'ID et du slug depuis l'URL
$question_id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
$current_slug = $_GET['slug'] ?? '';

if (!$question_id) {
    header('Location: index.php');
    exit;
}

// Traitement du formulaire de réponse (envoi en POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }

    $contenu = trim($_POST['contenu'] ?? '');
    $user_id = $_SESSION['user_id'];

    if (!empty($contenu)) {
        try {
            $stmtInsert = $pdo->prepare("INSERT INTO responses (question_id, user_id, contenu, date_creation) VALUES (?, ?, ?, NOW())");
            $stmtInsert->execute([$question_id, $user_id, $contenu]);
            
            // On conserve l'ID et le slug dans la redirection pour garder une URL propre
            $stmtSlugCheck = $pdo->prepare("SELECT titre FROM questions WHERE id = ?");
            $stmtSlugCheck->execute([$question_id]);
            $qData = $stmtSlugCheck->fetch();
            $redirectSlug = $qData ? slugify($qData['titre']) : '';

            header("Location: question.php?id=" . $question_id . "&slug=" . $redirectSlug);
            exit;
        } catch (Exception $e) {
            $error = "Une erreur est survenue lors de l'envoi de la réponse.";
        }
    } else {
        $error = "Le contenu de la réponse ne peut pas être vide.";
    }
}

// 2. Récupération de la question depuis la base de données
$stmt = $pdo->prepare("
    SELECT q.*, u.nom, u.prenom, u.reputation 
    FROM questions q 
    JOIN users u ON q.user_id = u.id 
    WHERE q.id = ?
");
$stmt->execute([$question_id]);
$question = $stmt->fetch();

if (!$question) {
    die("Question introuvable.");
}

// 3. Redirection automatique vers le bon slug si l'URL est modifiée ou incomplète
$expected_slug = slugify($question['titre']);
if ($current_slug !== $expected_slug) {
    header("Location: question.php?id={$question['id']}&slug={$expected_slug}");
    exit;
}

// 4. Récupérer les réponses associées
$stmtResponses = $pdo->prepare("
    SELECT r.*, u.nom, u.prenom, u.reputation 
    FROM responses r 
    JOIN users u ON r.user_id = u.id 
    WHERE r.question_id = ? 
    ORDER BY r.date_creation ASC
");
$stmtResponses->execute([$question_id]);
$responses = $stmtResponses->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($question['titre']) ?> - Teranga Devs</title>
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
<!-- Navbar -->
<nav class="border-b border-slate-800 bg-slate-900/50 backdrop-blur sticky top-0 z-50">
    <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
        
        <!-- Section Gauche : Logo + Lien Articles -->
        <div class="flex items-center space-x-6">
            <a href="index.php" class="font-bold text-xl text-amber-400">Teranga<span class="text-white">Devs</span></a>
            
            <!-- Lien Articles bien positionné au centre/gauche -->
            <a href="articles.php" class="text-sm bg-slate-800/60 hover:bg-slate-800 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg border border-slate-700/50 transition flex items-center gap-1.5">
                <span>📚</span> Articles
            </a>
        </div>
        
        <!-- Section Droite : Actions Utilisateur (Profil / Déconnexion) -->
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

    <!-- Contenu -->
    <main class="max-w-4xl mx-auto px-4 py-8 flex-grow w-full space-y-6">
        
        <!-- Question principale -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-slate-400">Posée par <strong class="text-slate-200"><?= htmlspecialchars($question['prenom'] . ' ' . $question['nom']) ?></strong> (Karma: <?= $question['reputation'] ?>)</span>
                <span class="text-xs text-slate-500"><?= $question['date_creation'] ?></span>
            </div>
            <h1 class="text-xl font-bold text-slate-100 mb-3"><?= htmlspecialchars($question['titre']) ?></h1>
            <p class="text-sm text-slate-300 mb-4 leading-relaxed"><?= nl2br(htmlspecialchars($question['contenu'])) ?></p>
            
            <div class="flex flex-wrap gap-2">
                <?php foreach (explode(',', $question['tags']) as $tag): ?>
                    <span class="text-xs bg-slate-950 text-teranga-gold px-2.5 py-0.5 rounded border border-slate-800">#<?= trim($tag) ?></span>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Section Réponses -->
        <div class="space-y-4">
            <h3 class="text-sm font-semibold uppercase tracking-wider text-slate-400">Réponses (<?= count($responses) ?>)</h3>

            <?php if (empty($responses)): ?>
                <p class="text-slate-500 text-sm bg-slate-900/40 p-4 rounded-xl border border-slate-800/80">Aucune réponse pour le moment. Partage ton expertise !</p>
            <?php else: ?>
                <?php foreach ($responses as $resp): ?>
                    <div class="bg-slate-900/60 border border-slate-800/80 rounded-xl p-5 shadow-sm space-y-3">
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>Par <strong class="text-slate-200"><?= htmlspecialchars($resp['prenom'] . ' ' . $resp['nom']) ?></strong> (Karma: <?= $resp['reputation'] ?? 0 ?>)</span>
                            <span><?= $resp['date_creation'] ?></span>
                        </div>
                        <p class="text-sm text-slate-300"><?= nl2br(htmlspecialchars($resp['contenu'])) ?></p>
                        
                        <div class="pt-2 flex items-center justify-between">
                            <button type="button" class="vote-btn bg-slate-800 hover:bg-amber-500/20 hover:text-amber-400 text-slate-300 text-xs px-2.5 py-1 rounded-lg border border-slate-700 transition flex items-center gap-1" data-id="<?= $resp['id'] ?>">
                                ▲ Upvote (<span class="vote-count"><?= $resp['votes'] ?? 0 ?></span>)
                            </button>
                        </div>

                        <!-- C'est ici qu'il doit être, à l'intérieur de la boucle pour chaque réponse -->
                        <?php if (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $question['user_id'] && !$resp['is_solution']): ?>
                            <form action="api/post_solution.php" method="POST" class="mt-2">
                                <input type="hidden" name="response_id" value="<?= $resp['id'] ?>">
                                <input type="hidden" name="question_id" value="<?= $question['id'] ?>">
                                <button type="submit" class="text-xs bg-emerald-500/10 hover:bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-lg border border-emerald-500/30 transition">
                                    ✓ Marquer comme solution
                                </button>
                            </form>
                        <?php elseif ($resp['is_solution']): ?>
                            <div class="mt-2 inline-flex items-center gap-1.5 text-xs bg-emerald-500/20 text-emerald-300 px-3 py-1 rounded-lg border border-emerald-500/40 font-semibold">
                                <span>✓ Solution validée</span>
                            </div>
                        <?php endif; ?>

                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Formulaire de réponse -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm mt-8">
            <h3 class="text-sm font-semibold text-slate-200 mb-4">Ajouter une réponse</h3>

            <?php if (!empty($error)): ?>
                <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg mb-4 text-center">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['user_id'])): ?>
                <form method="POST" class="space-y-4">
                    <div>
                        <textarea name="contenu" rows="4" placeholder="Écris ta solution ou ton conseil technique..." required
                            class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-teranga-green transition"></textarea>
                    </div>
                    <div class="flex justify-end">
                        <button type="submit" class="bg-gradient-to-r from-teranga-green to-emerald-600 hover:opacity-90 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition shadow-md">
                            Publier ma réponse
                        </button>
                    </div>
                </form>
            <?php else: ?>
                <div class="bg-slate-950 border border-slate-800 rounded-xl p-4 text-center text-sm text-slate-400">
                    Tu dois être <a href="login.php" class="text-amber-400 font-semibold hover:underline">connecté</a> pour pouvoir partager une réponse.
                </div>
            <?php endif; ?>
        </div>

    </main>

<?php
require_once 'includes/footer.php';
?>
<?php
session_start();
require_once 'includes/auth_guard.php'; // Bloque l'accès si non connecté
require_once 'config/db.php';

$user_id = $_SESSION['user_id'];

// 1. Récupérer toutes les informations personnelles de l'utilisateur (nom, prénom, email, karma)
$stmt_u = $pdo->prepare("SELECT nom, prenom, email, karma FROM users WHERE id = ?");
$stmt_u->execute([$user_id]);
$user_info = $stmt_u->fetch();

$nom = $user_info['nom'] ?? '';
$prenom = $user_info['prenom'] ?? '';
$email = $user_info['email'] ?? '';
$karma = $user_info['karma'] ?? 0;

// 2. Récupérer les questions de l'utilisateur (Statistiques + Liste)
$stmt_q = $pdo->prepare("SELECT * FROM questions WHERE user_id = ? ORDER BY date_creation DESC");
$stmt_q->execute([$user_id]);
$questions = $stmt_q->fetchAll();

// 3. Récupérer les réponses de l'utilisateur (Statistiques + Liste)
$stmt_r = $pdo->prepare("SELECT r.*, q.titre as question_titre FROM responses r JOIN questions q ON r.question_id = q.id WHERE r.user_id = ? ORDER BY r.date_creation DESC");
$stmt_r->execute([$user_id]);
$responses = $stmt_r->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mon Profil - Teranga Devs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/CSS/style.css">
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen">
    <!-- Navbar -->
    <nav class="border-b border-slate-800 bg-slate-900/50 backdrop-blur sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="font-bold text-xl text-amber-400">Teranga<span class="text-white">Devs</span></a>
            <div class="flex items-center space-x-4">
                <span class="text-slate-300 text-sm">👤 <?= htmlspecialchars($prenom . ' ' . $nom) ?></span>
                <a href="logout.php" class="text-sm bg-red-600/20 text-red-400 hover:bg-red-600/30 px-3 py-1.5 rounded-lg transition">Déconnexion</a>
            </div>
        </div>
    </nav>

    <main class="max-w-5xl mx-auto px-4 py-8 space-y-8">
        <!-- En-tête profil (Infos personnelles & Karma) -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-2xl font-bold text-white mb-1"><?= htmlspecialchars($prenom . ' ' . $nom) ?></h1>
                <p class="text-slate-400 text-sm"><?= htmlspecialchars($email) ?></p>
            </div>
            <div class="bg-amber-500/10 border border-amber-500/30 px-5 py-3 rounded-xl text-center">
                <span class="block text-2xl font-bold text-amber-400">⚡ <?= $karma ?></span>
                <span class="text-xs text-slate-400 uppercase tracking-wider font-semibold">Karma / Réputation</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Mes Questions -->
            <div>
                <h2 class="text-xl font-semibold mb-4 text-amber-400 flex items-center gap-2">
                    <span>💬</span> Mes Questions (<?= count($questions) ?>)
                </h2>
                <div class="space-y-4">
                    <?php if (empty($questions)): ?>
                        <p class="text-slate-500 italic bg-slate-900/50 p-4 rounded-xl border border-slate-800/50">Tu n'as posé aucune question pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($questions as $q): ?>
                            <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl hover:border-slate-700 transition space-y-1">
                                <a href="question.php?id=<?= $q['id'] ?>" class="font-semibold text-white hover:text-amber-400 block"><?= htmlspecialchars($q['titre']) ?></a>
                                <span class="text-xs text-slate-500 block"><?= $q['date_creation'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mes Réponses -->
            <div>
                <h2 class="text-xl font-semibold mb-4 text-amber-400 flex items-center gap-2">
                    <span>⚡</span> Mes Réponses (<?= count($responses) ?>)
                </h2>
                <div class="space-y-4">
                    <?php if (empty($responses)): ?>
                        <p class="text-slate-500 italic bg-slate-900/50 p-4 rounded-xl border border-slate-800/50">Tu n'as apporté aucune réponse pour le moment.</p>
                    <?php else: ?>
                        <?php foreach ($responses as $r): ?>
                            <div class="bg-slate-900 border border-slate-800 p-4 rounded-xl space-y-2">
                                <p class="text-xs text-amber-400/80">Sur la question : <a href="question.php?id=<?= $r['question_id'] ?>" class="underline hover:text-white"><?= htmlspecialchars($r['question_titre']) ?></a></p>
                                <p class="text-slate-300 text-sm"><?= nl2br(htmlspecialchars($r['contenu'])) ?></p>
                                <span class="text-xs text-slate-500 block"><?= $r['date_creation'] ?></span>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
<?php
require_once 'includes/footer.php';
?>
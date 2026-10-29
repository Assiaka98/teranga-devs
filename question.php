<?php
require_once 'config/db.php';

$question_id = $_GET['id'] ?? null;

if (!$question_id) {
    header('Location: index.php');
    exit;
}

// Récupérer la question
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

// Récupérer les réponses associées
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
    <title><= htmlspecialchars($question['titre']) ?> - Teranga Devs</title>
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

    <!-- Header -->
    <header class="border-b border-slate-800 bg-slate-900/80 backdrop-blur sticky top-0 z-50">
        <div class="max-w-4xl mx-auto px-4 h-16 flex items-center justify-between">
            <a href="index.php" class="text-sm text-teranga-gold hover:underline flex items-center gap-2">
                &larr; Retour à l'accueil
            </a>
            <span class="text-xs bg-teranga-green/20 text-teranga-green px-3 py-1 rounded-full font-medium border border-teranga-green/30">
                Teranga Devs 🇸🇳
            </span>
        </div>
    </header>

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
                <p class="text-slate-500 text-sm">Aucune réponse pour le moment. Partage ton expertise !</p>
            <?php else: ?>
                <?php foreach ($responses as $resp): ?>
                    <div class="bg-slate-900/60 border border-slate-800/80 rounded-xl p-5 shadow-sm space-y-2">
                        <div class="flex items-center justify-between text-xs text-slate-400">
                            <span>Par <strong class="text-slate-200"><?= htmlspecialchars($resp['prenom'] . ' ' . $resp['nom']) ?></strong> (Karma: <?= $resp['reputation'] ?>)</span>
                            <span><?= $resp['date_creation'] ?></span>
                        </div>
                        <p class="text-sm text-slate-300"><?= nl2br(htmlspecialchars($resp['contenu'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Formulaire de réponse -->
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm mt-8">
            <h3 class="text-sm font-semibold text-slate-200 mb-4">Ajouter une réponse</h3>
            <form id="response-form" class="space-y-4">
                <input type="hidden" id="question_id" value="<?= $question['id'] ?>">
                <input type="hidden" id="user_id" value="1"> <!-- ID de test -->
                <div>
                    <textarea id="contenu-reponse" rows="3" placeholder="Écris ta solution ou ton conseil technique..." required
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-teranga-green transition"></textarea>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="bg-gradient-to-r from-teranga-green to-emerald-600 hover:opacity-90 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition shadow-md">
                        Répondre
                    </button>
                </div>
            </form>
        </div>

    </main>

    <footer class="border-t border-slate-800 bg-slate-900/50 py-6 text-center text-xs text-slate-500 mt-12">
        <p>Teranga Devs &copy; 2026 - Conçu avec fierté pour le concours Cadev 🇸🇳</p>
    </footer>

    <!-- Script AJAX pour poster une réponse -->
    <script>
        document.getElementById('response-form').addEventListener('submit', async function(e) {
            e.preventDefault();

            const data = {
                question_id: document.getElementById('question_id').value,
                user_id: document.getElementById('user_id').value,
                contenu: document.getElementById('contenu-reponse').value
            };

            try {
                const response = await fetch('api/post_response.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                const result = await response.json();

                if (result.success) {
                    location.reload();
                } else {
                    alert('Erreur : ' + result.error);
                }
            } catch (err) {
                console.error('Erreur réseau', err);
            }
        });
    </script>
</body>
</html>
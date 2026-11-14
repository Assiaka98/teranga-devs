<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Déterminer quel profil afficher
$profile_id = $_GET['id'] ?? $_SESSION['user_id'] ?? null;

if (!$profile_id) {
    header('Location: login.php');
    exit;
}

$isMyProfile = (isset($_SESSION['user_id']) && $_SESSION['user_id'] == $profile_id);
$success_msg = '';
$error_msg = '';

// Traitement des mises à jour (uniquement si c'est mon propre profil)
if ($isMyProfile && $_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Mise à jour de la Bio / Titre
    if (isset($_POST['update_bio'])) {
        $titre_bio = trim($_POST['titre_bio'] ?? '');
        try {
            $stmt = $pdo->prepare("UPDATE users SET titre_bio = ? WHERE id = ?");
            $stmt->execute([$titre_bio, $_SESSION['user_id']]);
            $success_msg = "Profil mis à jour avec succès !";
        } catch (Exception $e) {
            $error_msg = "Erreur lors de la mise à jour du profil.";
        }
    }

    // 2. Modification du Mot de Passe
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_msg = "Tous les champs de mot de passe sont obligatoires.";
        } elseif ($new_password !== $confirm_password) {
            $error_msg = "Le nouveau mot de passe et la confirmation ne correspondent pas.";
        } elseif (strlen($new_password) < 6) {
            $error_msg = "Le nouveau mot de passe doit faire au moins 6 caractères.";
        } else {
            try {
                // Vérifier l'ancien mot de passe
                $stmtPass = $pdo->prepare("SELECT mot_de_passe FROM users WHERE id = ?");
                $stmtPass->execute([$_SESSION['user_id']]);
                $userPass = $stmtPass->fetch();

                if ($userPass && password_verify($current_password, $userPass['mot_de_passe'])) {
                    $new_hash = password_hash($new_password, PASSWORD_BCRYPT);
                    $updatePass = $pdo->prepare("UPDATE users SET mot_de_passe = ? WHERE id = ?");
                    $updatePass->execute([$new_hash, $_SESSION['user_id']]);
                    $success_msg = "Mot de passe modifié avec succès !";
                } else {
                    $error_msg = "Le mot de passe actuel est incorrect.";
                }
            } catch (Exception $e) {
                $error_msg = "Erreur lors de la modification du mot de passe.";
            }
        }
    }
}

// Charger les données de l'utilisateur
try {
    $stmtUser = $pdo->prepare("SELECT id, nom, prenom, email, karma, titre_bio, avatar, date_creation FROM users WHERE id = ?");
    $stmtUser->execute([$profile_id]);
    $user = $stmtUser->fetch();

    if (!$user) {
        header('Location: index.php');
        exit;
    }

    // Récupérer les articles
    $stmtArt = $pdo->prepare("SELECT * FROM articles WHERE user_id = ? ORDER BY date_publication DESC");
    $stmtArt->execute([$profile_id]);
    $articles = $stmtArt->fetchAll();

    // Récupérer les questions
    $stmtQ = $pdo->prepare("SELECT * FROM questions WHERE user_id = ? ORDER BY date_creation DESC");
    $stmtQ->execute([$profile_id]);
    $questions = $stmtQ->fetchAll();

} catch (Exception $e) {
    header('Location: index.php');
    exit;
}

// Avatar par défaut (style GitHub / UI Avatars)
$avatar_url = !empty($user['avatar']) 
    ? $user['avatar'] 
    : "https://ui-avatars.com/api/?name=" . urlencode($user['prenom'] . '+' . $user['nom']) . "&background=d97706&color=fff&size=128&bold=true";

$pageTitle = "Profil de " . htmlspecialchars($user['prenom'] . ' ' . $user['nom']) . " - Teranga Devs";
require_once __DIR__ . '/includes/header.php';
?>

<main class="max-w-4xl mx-auto px-4 py-8 space-y-6">

    <!-- Messages de succès / d'erreur -->
    <?php if ($success_msg): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg">
            <?= htmlspecialchars($success_msg) ?>
        </div>
    <?php endif; ?>
    <?php if ($error_msg): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg">
            <?= htmlspecialchars($error_msg) ?>
        </div>
    <?php endif; ?>

    <!-- Carte Profil (Header Style LinkedIn) -->
    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
            <!-- Avatar par défaut généré -->
            <img src="<?= htmlspecialchars($avatar_url) ?>" alt="Avatar" class="w-20 h-20 rounded-full border-2 border-amber-400 shadow-md shrink-0">
            
            <div class="flex-grow text-center sm:text-left space-y-1">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                    <h1 class="text-2xl font-bold text-slate-100">
                        <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
                    </h1>
                    <?php if ($isMyProfile): ?>
                        <button onclick="document.getElementById('edit-modal').classList.toggle('hidden')" class="text-xs bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 px-3 py-1.5 rounded-lg transition">
                            ✏️ Éditer mon profil / Sécurité
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Bio / Titre professionnel (Style LinkedIn) -->
                <p class="text-sm text-amber-400/90 font-medium">
                    <?= htmlspecialchars($user['titre_bio'] ?? 'Fullstack Developer | TerangaDevs Member') ?>
                </p>

                <p class="text-xs text-slate-400">
                    ✉️ <?= htmlspecialchars($user['email']) ?>
                </p>
            </div>
        </div>

        <!-- Statistiques (Karma & Articles) -->
        <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-slate-800 text-center">
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-800/80">
                <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Karma</span>
                <span class="text-xl font-extrabold text-amber-400"><?= number_format($user['karma']) ?></span>
            </div>
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-800/80">
                <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Articles publiés</span>
                <span class="text-xl font-extrabold text-emerald-400"><?= count($articles) ?></span>
            </div>
        </div>
    </div>

    <!-- Formulaire d'édition (Bio & Mot de Passe) -->
    <?php if ($isMyProfile): ?>
        <div id="edit-modal" class="hidden bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-xl">
            <h2 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-3">Paramètres du compte</h2>

            <!-- Formulaire Bio -->
            <form method="POST" class="space-y-3">
                <label class="block text-xs font-semibold text-slate-300 uppercase">Description / Titre professionnel (style LinkedIn)</label>
                <input type="text" name="titre_bio" value="<?= htmlspecialchars($user['titre_bio'] ?? '') ?>" placeholder="ex: Fullstack Developer | PHP, Node.js, IoT" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                <button type="submit" name="update_bio" class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold px-4 py-2 rounded-lg transition">
                    Mettre à jour la bio
                </button>
            </form>

            <hr class="border-slate-800">

            <!-- Formulaire Changer de mot de passe -->
            <form method="POST" class="space-y-3">
                <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Changer de mot de passe</h3>
                
                <div>
                    <label class="block text-[11px] text-slate-400 mb-1">Mot de passe actuel</label>
                    <input type="password" name="current_password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Nouveau mot de passe</label>
                        <input type="password" name="new_password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                    <div>
                        <label class="block text-[11px] text-slate-400 mb-1">Confirmer le nouveau mot de passe</label>
                        <input type="password" name="confirm_password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                </div>

                <button type="submit" name="change_password" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold px-4 py-2 rounded-lg transition">
                    Modifier le mot de passe
                </button>
            </form>
        </div>
    <?php endif; ?>

    <!-- Liste des articles publiés -->
    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-100">📚 Articles rédigés</h2>
        <?php if (empty($articles)): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-xs text-slate-400">
                Aucun article publié pour le moment.
            </div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($articles as $art): ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-2">
                        <h3 class="font-bold text-sm text-slate-100 line-clamp-1">
                            <a href="article_read.php?slug=<?= $art['slug'] ?>" class="hover:text-amber-400 transition">
                                <?= htmlspecialchars($art['titre']) ?>
                            </a>
                        </h3>
                        <p class="text-xs text-slate-400 line-clamp-2"><?= htmlspecialchars($art['extrait']) ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$article_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$article_id || !$user_id) {
    header('Location: articles.php');
    exit;
}

// Récupérer l'article et vérifier que l'utilisateur connecté est bien l'auteur
try {
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();

    if (!$article || $article['user_id'] != $user_id) {
        header('Location: articles.php');
        exit;
    }
} catch (Exception $e) {
    header('Location: articles.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $extrait = trim($_POST['extrait'] ?? '');
    $couverture = $article['couverture']; // Conserver l'ancienne par défaut

    if (!empty($titre) && !empty($contenu)) {
        try {
            // Gestion d'une nouvelle image de couverture éventuelle
            if (isset($_FILES['couverture_file']) && $_FILES['couverture_file']['error'] === UPLOAD_ERR_OK) {
                $fileTmpPath = $_FILES['couverture_file']['tmp_name'];
                $fileName = $_FILES['couverture_file']['name'];
                $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

                if (in_array($fileExtension, $allowedExtensions)) {
                    $uploadDir = __DIR__ . '/uploads/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $newFileName = 'cover_' . uniqid() . '.' . $fileExtension;
                    if (move_uploaded_file($fileTmpPath, $uploadDir . $newFileName)) {
                        $couverture = 'uploads/' . $newFileName;
                    }
                }
            }

            $slug = slugify($titre);
            if (empty($extrait)) {
                $extrait = mb_substr(strip_tags($contenu), 0, 150) . '...';
            }

            $updateStmt = $pdo->prepare("UPDATE articles SET titre = ?, slug = ?, extrait = ?, contenu = ?, couverture = ? WHERE id = ? AND user_id = ?");
            $updateStmt->execute([$titre, $slug, $extrait, $contenu, $couverture, $article_id, $user_id]);

            header("Location: article_read.php?slug=" . $slug);
            exit;
        } catch (Exception $e) {
            $error = "Erreur lors de la mise à jour : " . $e->getMessage();
        }
    } else {
        $error = "Le titre et le contenu sont obligatoires.";
    }
}

$pageTitle = "Modifier l'article - Teranga Devs";
require_once __DIR__ . '/includes/header.php';
?>

<main class="max-w-3xl mx-auto px-4 py-8 space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-slate-100">Modifier l'article</h1>
        <a href="article_read.php?id=<?= $article['id'] ?>" class="text-xs text-amber-400 hover:underline">&larr; Retour à l'article</a>
    </div>

    <?php if (!empty($error)): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="bg-slate-900 border border-slate-800 rounded-xl p-6 space-y-5 shadow-xl">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Titre de l'article *</label>
            <input type="text" name="titre" value="<?= htmlspecialchars($article['titre']) ?>" required
                class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 focus:outline-none focus:border-amber-400">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Extrait court</label>
            <input type="text" name="extrait" value="<?= htmlspecialchars($article['extrait']) ?>"
                class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 focus:outline-none focus:border-amber-400">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Nouvelle image de couverture (optionnel)</label>
            <?php if (!empty($article['couverture'])): ?>
                <div class="mb-2">
                    <img src="<?= htmlspecialchars($article['couverture']) ?>" alt="Actuelle" class="w-32 h-20 object-cover rounded-lg border border-slate-800">
                </div>
            <?php endif; ?>
            <input type="file" name="couverture_file" accept="image/*"
                class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2 text-xs text-slate-400">
        </div>

        <div>
            <label class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Contenu *</label>
            <textarea name="contenu" rows="10" required
                class="w-full bg-slate-950 border border-slate-800 rounded-lg p-4 text-sm text-slate-100 focus:outline-none focus:border-amber-400 leading-relaxed"><?= htmlspecialchars($article['contenu']) ?></textarea>
        </div>

        <div class="flex justify-end gap-3 pt-3 border-t border-slate-800">
            <a href="article_read.php?id=<?= $article['id'] ?>" class="bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs px-4 py-2 rounded-lg transition">Annuler</a>
            <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-xs px-6 py-2 rounded-lg transition">Enregistrer les modifications</button>
        </div>
    </form>
</main>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
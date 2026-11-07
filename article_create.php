<?php
session_start();
require_once 'config/db.php';

// Redirection si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$error = '';

// Traitement du formulaire
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre = trim($_POST['titre'] ?? '');
    $contenu = trim($_POST['contenu'] ?? '');
    $extrait = trim($_POST['extrait'] ?? '');
    $user_id = $_SESSION['user_id'];
    $couverture = '';

    if (!empty($titre) && !empty($contenu)) {
        try {
            // Traitement du téléversement de l'image de couverture
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

                    // Nom unique pour l'image
                    $newFileName = 'cover_' . uniqid() . '.' . $fileExtension;
                    $destPath = $uploadDir . $newFileName;

                    if (move_uploaded_file($fileTmpPath, $destPath)) {
                        $couverture = 'uploads/' . $newFileName;
                    } else {
                        $error = "Erreur lors de l'enregistrement de l'image de couverture.";
                    }
                } else {
                    $error = "Format d'image non supporté (seuls JPG, PNG, WEBP et GIF sont autorisés).";
                }
            }

            // Si aucune image uploadée ou si échec, image par défaut
            if (empty($couverture)) {
                $couverture = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80';
            }

            if (empty($error)) {
                $slug = slugify($titre);
                if (empty($extrait)) {
                    $extrait = mb_substr(strip_tags($contenu), 0, 150) . '...';
                }

                $stmt = $pdo->prepare("INSERT INTO articles (user_id, titre, slug, extrait, contenu, couverture, date_publication) VALUES (?, ?, ?, ?, ?, ?, datetime('now'))");
                $stmt->execute([$user_id, $titre, $slug, $extrait, $contenu, $couverture]);
                
                header("Location: article_read.php?slug=" . $slug);
                exit;
            }
        } catch (Exception $e) {
            $error = "Une erreur est survenue lors de la publication : " . $e->getMessage();
        }
    } else {
        $error = "Veuillez remplir les champs obligatoires (titre et contenu).";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rédiger un Article - Teranga Devs</title>
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
    <nav class="border-b border-slate-800 bg-slate-900/50 backdrop-blur sticky top-0 z-50">
        <div class="max-w-5xl mx-auto px-4 h-16 flex items-center justify-between">
            <div class="flex items-center space-x-6">
                <a href="index.php" class="font-bold text-xl text-amber-400">Teranga<span class="text-white">Devs</span></a>
                <a href="articles.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white px-3 py-1.5 rounded-lg border border-slate-700 transition flex items-center gap-1.5">
                    <span>📚</span> Articles
                </a>
            </div>
            
            <div class="flex items-center space-x-4">
                <a href="profile.php" class="text-sm bg-slate-800 hover:bg-slate-700 text-slate-200 px-3 py-1.5 rounded-lg transition flex items-center gap-1.5">
                    <span>👤</span> Mon Profil
                </a>
                <a href="logout.php" class="text-sm bg-red-600/20 text-red-400 hover:bg-red-600/30 px-3 py-1.5 rounded-lg transition">Déconnexion</a>
            </div>
        </div>
    </nav>

    <!-- Contenu Principal -->
    <main class="max-w-3xl mx-auto px-4 py-8 flex-grow w-full">
        
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-slate-100">Rédiger un article</h1>
                <p class="text-sm text-slate-400 mt-1">Partage tes connaissances, tutoriels et retours d'expérience avec la communauté.</p>
            </div>
            <a href="articles.php" class="text-xs text-slate-400 hover:text-slate-200 transition">
                &larr; Retour aux articles
            </a>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg mb-6 text-center">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Formulaire avec enctype -->
        <form method="POST" enctype="multipart/form-data" class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-5">
            
            <!-- Titre -->
            <div>
                <label for="titre" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Titre de l'article *</label>
                <input type="text" id="titre" name="titre" placeholder="Ex: Comment connecter un capteur IoT à Node.js" required
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
            </div>

            <!-- Extrait -->
            <div>
                <label for="extrait" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Extrait court (résumé)</label>
                <input type="text" id="extrait" name="extrait" placeholder="Bref résumé affiché sur la liste des articles..."
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
            </div>

            <!-- Image de couverture (File Upload) -->
            <div>
                <label for="couverture_file" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Image de couverture (Téléverser)</label>
                <input type="file" id="couverture_file" name="couverture_file" accept="image/png, image/jpeg, image/webp, image/gif"
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-amber-400 hover:file:bg-slate-700 cursor-pointer">
                <span class="text-[10px] text-slate-500 mt-1 block">Formats acceptés : PNG, JPG, WEBP, GIF (Taille recommandée : 1200x630px).</span>
            </div>

            <!-- Contenu -->
            <div>
                <label for="contenu" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Contenu de l'article *</label>
                <textarea id="contenu" name="contenu" rows="10" placeholder="Écris ton article en détail ici..." required
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg p-4 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition leading-relaxed"></textarea>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800/80">
                <a href="articles.php" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg transition">
                    Annuler
                </a>
                <button type="submit" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition shadow-md flex items-center gap-2">
                    <span>🚀</span> Publier l'article
                </button>
            </div>

        </form>

    </main>

    <!-- Footer -->
    <?php require_once 'includes/footer.php'; ?>
</body>
</html>
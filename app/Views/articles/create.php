<?php
$article = $article ?? [];
$action = $action ?? ($urls['articles'] ?? '/articles');
$isEdit = $isEdit ?? false;
$articlesUrl = (string) ($urls['articles'] ?? '/articles');
$titre = (string) ($article['titre'] ?? '');
$extrait = (string) ($article['extrait'] ?? '');
$contenu = (string) ($article['contenu'] ?? '');
$cover = (string) ($article['couverture'] ?? '');
$coverSrc = $cover;
if ($coverSrc !== '' && !str_starts_with($coverSrc, 'http') && !str_starts_with($coverSrc, '/')) {
    $coverSrc = '/' . $coverSrc;
}
?>
<main class="max-w-3xl mx-auto px-4 py-8 flex-grow w-full">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-100"><?= $isEdit ? 'Modifier l\'article' : 'Rédiger un article' ?></h1>
            <p class="text-sm text-slate-400 mt-1">Partage tes connaissances, tutoriels et retours d'expérience avec la communauté.</p>
        </div>
        <a href="<?= htmlspecialchars($articlesUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs text-slate-400 hover:text-slate-200 transition">&larr; Retour aux articles</a>
    </div>

    <form method="post" action="<?= htmlspecialchars($action, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" enctype="multipart/form-data" class="bg-slate-900 border border-slate-800 rounded-xl p-6 shadow-sm space-y-5">
        <div>
            <label for="titre" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Titre de l'article *</label>
            <input type="text" id="titre" name="titre" value="<?= htmlspecialchars($titre, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Ex: Comment connecter un capteur IoT à Node.js" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
        </div>

        <div>
            <label for="extrait" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Extrait court (résumé)</label>
            <input type="text" id="extrait" name="extrait" value="<?= htmlspecialchars($extrait, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Bref résumé affiché sur la liste des articles..." class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition">
        </div>

        <div>
            <label for="couverture_file" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Image de couverture</label>
            <?php if ($isEdit && $cover !== ''): ?>
                <div class="mb-2">
                    <img src="<?= htmlspecialchars($coverSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Couverture actuelle" class="w-32 h-20 object-cover rounded-lg border border-slate-800">
                </div>
            <?php endif; ?>
            <input type="file" id="couverture_file" name="couverture_file" accept="image/png, image/jpeg, image/webp, image/gif" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-800 file:text-amber-400 hover:file:bg-slate-700 cursor-pointer">
            <span class="text-[10px] text-slate-500 mt-1 block">Formats acceptés : PNG, JPG, WEBP, GIF.</span>
        </div>

        <div>
            <label for="contenu" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 mb-2">Contenu de l'article *</label>
            <textarea id="contenu" name="contenu" rows="10" placeholder="Écris ton article en détail ici..." required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-4 text-sm text-slate-100 placeholder-slate-500 focus:outline-none focus:border-amber-400 transition leading-relaxed"><?= htmlspecialchars($contenu, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></textarea>
        </div>

        <div class="flex items-center justify-end space-x-3 pt-3 border-t border-slate-800/80">
            <a href="<?= htmlspecialchars($articlesUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg transition">Annuler</a>
            <button type="submit" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-slate-950 font-bold px-6 py-2 rounded-lg text-sm transition shadow-md"><?= $isEdit ? 'Enregistrer les modifications' : 'Publier l\'article' ?></button>
        </div>
    </form>
</main>

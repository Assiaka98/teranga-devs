<?php
$user = $user ?? [];
$articles = $articles ?? [];
$questions = $questions ?? [];
$isMyProfile = $isMyProfile ?? false;
$profileUrl = (string) ($profileUrl ?? ($urls['profile'] ?? '/profile'));
$editUrl = (string) ($editUrl ?? ($urls['profile'] ?? '/profile') . '/edit');
$editOpen = (bool) ($editOpen ?? false);
$error = $error ?? null;
$notice = $notice ?? null;
$prenom = (string) ($user['prenom'] ?? '');
$nom = (string) ($user['nom'] ?? '');
$avatar = (string) ($user['avatar'] ?? '');
$avatarUrl = 'https://ui-avatars.com/api/?name=' . rawurlencode($prenom) . '+' . rawurlencode($nom) . '&background=d97706&color=fff&size=128&bold=true';

if ($avatar !== '' && preg_match('~^https?://~i', $avatar) === 1) {
    $avatarUrl = $avatar;
} elseif ($avatar !== '') {
    $projectRoot = dirname(__DIR__, 3);
    $name = basename(str_replace('\\', '/', $avatar));
    foreach ([$projectRoot . '/uploads/' . $name, $projectRoot . '/public/uploads/' . $name] as $file) {
        if (is_file($file) && @getimagesize($file) !== false) {
            $avatarUrl = '/uploads/' . rawurlencode($name);
            break;
        }
    }
}
$articlesUrl = (string) ($urls['articles'] ?? '/articles');
$questionsUrl = (string) ($urls['questions'] ?? '/questions');
?>
<main class="max-w-4xl mx-auto px-4 py-8 space-y-6 w-full">
    <?php if ($error !== null && $error !== ''): ?>
        <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>
    <?php if ($notice !== null && $notice !== ''): ?>
        <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg" role="status"><?= htmlspecialchars($notice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
    <?php endif; ?>

    <div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl relative overflow-hidden">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-5">
            <img src="<?= htmlspecialchars($avatarUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Avatar de <?= htmlspecialchars($prenom . ' ' . $nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="w-20 h-20 rounded-full border-2 border-amber-400 shadow-md shrink-0">
            <div class="flex-grow text-center sm:text-left space-y-1">
                <h1 class="text-2xl font-bold text-slate-100"><?= htmlspecialchars($prenom . ' ' . $nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                <?php if ($isMyProfile): ?>
                    <button type="button" onclick="document.getElementById('edit-profile').classList.toggle('hidden')" class="text-xs bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 px-3 py-1.5 rounded-lg transition">Éditer mon profil / Sécurité</button>
                <?php endif; ?>
                <p class="text-sm text-amber-400/90 font-medium"><?= htmlspecialchars((string) ($user['titre_bio'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <p class="text-xs text-slate-400"><?= htmlspecialchars((string) ($user['email'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            </div>
        </div>

        <div class="grid grid-cols-3 gap-4 mt-6 pt-4 border-t border-slate-800 text-center">
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-800/80">
                <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Karma</span>
                <span class="text-xl font-extrabold text-amber-400"><?= number_format((int) ($user['karma'] ?? 0)) ?></span>
            </div>
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-800/80">
                <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Réputation</span>
                <span class="text-xl font-extrabold text-sky-400"><?= number_format((int) ($user['reputation'] ?? 0)) ?></span>
            </div>
            <div class="bg-slate-950/50 p-3 rounded-xl border border-slate-800/80">
                <span class="block text-xs uppercase tracking-wider text-slate-400 font-semibold">Articles</span>
                <span class="text-xl font-extrabold text-emerald-400"><?= count($articles) ?></span>
            </div>
        </div>
    </div>

    <?php if ($isMyProfile): ?>
        <div id="edit-profile" class="<?= $editOpen ? '' : 'hidden ' ?>bg-slate-900 border border-slate-800 rounded-2xl p-6 space-y-6 shadow-xl">
            <h2 class="text-lg font-bold text-slate-100 border-b border-slate-800 pb-3">Paramètres du compte</h2>

            <form method="post" action="<?= htmlspecialchars($editUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" enctype="multipart/form-data" class="space-y-3">
                <label for="nom" class="block text-xs font-semibold text-slate-300 uppercase">Nom affiché</label>
                <input type="text" id="nom" name="nom" maxlength="100" value="<?= htmlspecialchars($nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                <label for="titre_bio" class="block text-xs font-semibold text-slate-300 uppercase">Description / Titre professionnel</label>
                <input type="text" id="titre_bio" name="titre_bio" maxlength="255" value="<?= htmlspecialchars((string) ($user['titre_bio'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="ex: Fullstack Developer | PHP, Node.js, IoT" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                <label for="avatar_file" class="block text-xs font-semibold text-slate-300 uppercase">Avatar (optionnel)</label>
                <input type="file" id="avatar_file" name="avatar_file" accept="image/png, image/jpeg, image/webp, image/gif" class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-400">
                <button type="submit" class="bg-amber-500 hover:bg-amber-400 text-slate-950 text-xs font-bold px-4 py-2 rounded-lg transition">Mettre à jour le profil</button>
            </form>

            <hr class="border-slate-800">

            <form method="post" action="<?= htmlspecialchars($profileUrl . '/password', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="space-y-3">
                <h3 class="text-xs font-bold text-slate-200 uppercase tracking-wider">Changer de mot de passe</h3>
                <div>
                    <label for="current_password" class="block text-[11px] text-slate-400 mb-1">Mot de passe actuel</label>
                    <input type="password" id="current_password" name="current_password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label for="new_password" class="block text-[11px] text-slate-400 mb-1">Nouveau mot de passe</label>
                        <input type="password" id="new_password" name="new_password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                    <div>
                        <label for="confirm_password" class="block text-[11px] text-slate-400 mb-1">Confirmer le mot de passe</label>
                        <input type="password" id="confirm_password" name="confirm_password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-2.5 text-xs text-slate-100 focus:outline-none focus:border-amber-400">
                    </div>
                </div>
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 border border-slate-700 text-xs font-bold px-4 py-2 rounded-lg transition">Modifier le mot de passe</button>
            </form>
        </div>
    <?php endif; ?>

    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-100">Articles rédigés</h2>
        <?php if ($articles === []): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-xs text-slate-400">Aucun article publié pour le moment.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($articles as $article): ?>
                    <?php
                    $slug = (string) ($article['slug'] ?? '');
                    $identifier = $slug !== '' ? $slug : (string) ($article['id'] ?? '');
                    ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-2">
                        <h3 class="font-bold text-sm text-slate-100 line-clamp-1">
                            <a href="<?= htmlspecialchars($articlesUrl . '/' . rawurlencode($identifier), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="hover:text-amber-400 transition"><?= htmlspecialchars((string) ($article['titre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                        </h3>
                        <p class="text-xs text-slate-400 line-clamp-2"><?= htmlspecialchars((string) ($article['extrait'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="space-y-4">
        <h2 class="text-lg font-bold text-slate-100">Questions posées</h2>
        <?php if ($questions === []): ?>
            <div class="bg-slate-900 border border-slate-800 rounded-xl p-6 text-center text-xs text-slate-400">Aucune question posée pour le moment.</div>
        <?php else: ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($questions as $question): ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 space-y-2">
                        <h3 class="font-bold text-sm text-slate-100 line-clamp-1">
                            <a href="<?= htmlspecialchars($questionsUrl . '/' . rawurlencode((string) ($question['id'] ?? '')), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="hover:text-amber-400 transition"><?= htmlspecialchars((string) ($question['titre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                        </h3>
                        <p class="text-xs text-slate-400 line-clamp-2"><?= htmlspecialchars((string) ($question['tags'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

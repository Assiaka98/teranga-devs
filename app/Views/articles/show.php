<?php
$article = $article ?? [];
$isLoggedIn = $isLoggedIn ?? false;
$canManage = $canManage ?? false;
$hasVoted = $hasVoted ?? false;
$articlesUrl = (string) ($urls['articles'] ?? '/articles');
$articleId = (int) ($article['id'] ?? 0);
$actionUrl = $articlesUrl . '/' . $articleId;
$author = trim((string) ($article['prenom'] ?? '') . ' ' . (string) ($article['nom'] ?? ''));
$date = (string) ($article['date_publication'] ?? $article['date_creation'] ?? '');
$cover = (string) ($article['couverture'] ?? '');
if ($cover !== '' && !preg_match('~^(?:https?:)?//~i', $cover) && !str_starts_with($cover, '/')) {
    $cover = '/' . ltrim(str_replace('\\', '/', $cover), '/');
}
$extrait = (string) ($article['extrait'] ?? '');
$votes = (int) ($article['votes'] ?? 0);
?>
<main class="max-w-4xl mx-auto px-4 py-8 space-y-6 w-full">
    <div>
        <a href="<?= htmlspecialchars($articlesUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs text-amber-400 hover:underline inline-flex items-center gap-1">&larr; Retour aux articles</a>
    </div>

    <?php if ($canManage): ?>
        <div class="flex items-center gap-2 pt-2 border-t border-slate-800">
            <a href="<?= htmlspecialchars($actionUrl . '/edit', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="bg-blue-500/10 hover:bg-blue-500/20 text-blue-400 border border-blue-500/30 px-3 py-1.5 rounded-lg text-xs font-semibold transition">Modifier</a>
            <form method="post" action="<?= htmlspecialchars($actionUrl . '/delete', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                <button type="submit" class="bg-red-500/10 hover:bg-red-500/20 text-red-400 border border-red-500/30 px-3 py-1.5 rounded-lg text-xs font-semibold transition">Supprimer</button>
            </form>
        </div>
    <?php endif; ?>

    <article class="bg-slate-900 border border-slate-800 rounded-2xl overflow-hidden space-y-6 shadow-xl">
        <?php if ($cover !== ''): ?>
            <div class="w-full h-72 sm:h-96 bg-slate-950 overflow-hidden">
                <img src="<?= htmlspecialchars($cover, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Couverture de l'article" class="w-full h-full object-cover">
            </div>
        <?php endif; ?>

        <div class="p-6 sm:p-8 space-y-6">
            <div class="space-y-3">
                <span class="text-[11px] uppercase tracking-wider font-bold text-emerald-400 bg-emerald-950/50 px-2.5 py-1 rounded border border-emerald-800/50">Tutoriel Technique</span>
                <h1 class="text-2xl sm:text-3xl font-bold text-slate-100 leading-tight"><?= htmlspecialchars((string) ($article['titre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
                <div class="flex items-center justify-between text-xs text-slate-400 pt-2 border-t border-slate-800">
                    <span>Publié par <strong class="text-slate-200"><?= htmlspecialchars($author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong> (Réputation : <?= htmlspecialchars((string) ($article['reputation'] ?? 0), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>)</span>
                    <span><?= htmlspecialchars($date, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                </div>
            </div>

            <?php if ($extrait !== ''): ?>
                <p class="text-sm text-slate-400"><?= htmlspecialchars($extrait, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
            <?php endif; ?>

            <div class="text-slate-300 text-sm sm:text-base leading-relaxed space-y-4 pt-2">
                <p><?= nl2br(htmlspecialchars((string) ($article['contenu'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8')) ?></p>
            </div>

            <div class="border-t border-slate-800 pt-4 flex justify-between items-center">
                <a href="<?= htmlspecialchars($articlesUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg transition">&larr; Retour</a>

                <?php if (!$isLoggedIn): ?>
                    <a href="<?= htmlspecialchars($urls['login'] ?? '/login', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="bg-slate-800 hover:bg-slate-700 text-amber-400 border border-slate-700 px-4 py-2 rounded-lg text-xs font-semibold transition">Connectez-vous pour voter</a>
                <?php elseif ($canManage): ?>
                    <span class="text-xs text-slate-500 italic">C'est votre propre article</span>
                <?php elseif ($hasVoted): ?>
                    <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 px-4 py-2 rounded-lg text-xs font-semibold">Marqué comme utile (<?= $votes ?>)</span>
                <?php else: ?>
                    <form method="post" action="<?= htmlspecialchars($actionUrl . '/vote', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                        <button type="submit" class="bg-amber-500/10 hover:bg-amber-500/20 text-amber-400 border border-amber-500/30 px-4 py-2 rounded-lg text-xs font-semibold transition">Voter (Utile) — <?= $votes ?></button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </article>
</main>

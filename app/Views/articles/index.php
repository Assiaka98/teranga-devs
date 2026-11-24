<?php
$articles = $articles ?? [];
$error = $error ?? null;
$notice = $notice ?? null;
$canCreate = $canCreate ?? false;
$articlesUrl = (string) ($urls['articles'] ?? '/articles');

$projectRoot = dirname(__DIR__, 3);
$uploadUrlBase = '/uploads/';

$coverUrl = static function (string $cover) use ($projectRoot, $uploadUrlBase): ?string {
    $cover = trim($cover);
    if ($cover === '' || preg_match('/[\x00-\x1F\x7F]/', $cover) === 1) {
        return null;
    }

    if (str_starts_with($cover, '//')) {
        $cover = 'https:' . $cover;
    }
    if (preg_match('~^https?://~i', $cover) === 1) {
        $parts = parse_url($cover);

        return is_array($parts) && ($parts['host'] ?? '') !== '' ? $cover : null;
    }

    $path = ltrim(str_replace('\\', '/', $cover), '/');
    if (str_starts_with($path, 'public/')) {
        $path = substr($path, 7);
    }
    if (!str_starts_with($path, 'uploads/')) {
        return null;
    }

    $name = substr($path, strlen('uploads/'));
    if ($name === '' || str_contains($name, '/') || str_contains($name, '..')) {
        return null;
    }

    $mimes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    foreach ([$projectRoot . '/uploads/' . $name, $projectRoot . '/public/uploads/' . $name] as $file) {
        if (!is_file($file)) {
            continue;
        }
        $info = @getimagesize($file);
        if ($info === false || !in_array((string) ($info['mime'] ?? ''), $mimes, true)) {
            continue;
        }

        return $uploadUrlBase . rawurlencode($name);
    }

    return null;
};
?>
<main class="max-w-7xl mx-auto px-4 py-8 space-y-6 w-full">
    <div class="border-b border-slate-800 pb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-amber-400">Articles &amp; Tutoriels</h1>
            <p class="text-xs text-slate-400 mt-1">Partagez vos retours d'expérience, analyses de sécurité et guides techniques.</p>
        </div>
        <?php if ($canCreate): ?>
            <a href="<?= htmlspecialchars($articlesUrl . '/create', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-slate-950 font-bold px-4 py-2 rounded-lg text-xs transition shadow-md">Rédiger un article</a>
        <?php endif; ?>
    </div>

    <?php if ($error !== null && $error !== ''): ?>
        <p role="alert" class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>
    <?php if ($notice !== null && $notice !== ''): ?>
        <p role="status" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg"><?= htmlspecialchars($notice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    <?php endif; ?>

    <?php if ($articles !== []): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <?php foreach ($articles as $article): ?>
                <?php
                $slug = (string) ($article['slug'] ?? '');
                $identifier = $slug !== '' ? $slug : (string) ($article['id'] ?? '');
                $link = $articlesUrl . '/' . rawurlencode($identifier);
                $coverSrc = $coverUrl((string) ($article['couverture'] ?? ''));
                $author = trim((string) ($article['prenom'] ?? '') . ' ' . (string) ($article['nom'] ?? ''));
                $date = (string) ($article['date_publication'] ?? $article['date_creation'] ?? '');
                $timestamp = $date !== '' ? strtotime($date) : false;
                $dateLabel = $timestamp !== false ? date('d/m/Y', $timestamp) : $date;
                ?>
                <article class="bg-slate-900 border border-slate-800 rounded-xl overflow-hidden flex flex-col justify-between hover:border-slate-700 transition">
                    <?php if ($coverSrc !== null): ?>
                        <div class="h-48 overflow-hidden bg-slate-950">
                            <img src="<?= htmlspecialchars($coverSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" alt="Couverture de l'article" loading="lazy" class="w-full h-full object-cover hover:scale-105 transition duration-300">
                        </div>
                    <?php endif; ?>
                    <div class="p-5 space-y-3 flex-1 flex flex-col justify-between">
                        <div class="space-y-2">
                            <span class="text-[10px] uppercase tracking-wider font-bold text-emerald-400 bg-emerald-950/50 px-2 py-0.5 rounded border border-emerald-800/50">Tutoriel</span>
                            <h2 class="text-lg font-semibold text-slate-100 hover:text-amber-400 transition">
                                <a href="<?= htmlspecialchars($link, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars((string) ($article['titre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                            </h2>
                            <p class="text-xs text-slate-400 line-clamp-3"><?= htmlspecialchars((string) ($article['extrait'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                        </div>
                        <div class="pt-4 border-t border-slate-800 flex items-center justify-between text-[11px] text-slate-400">
                            <span>Par <span class="text-slate-200 font-semibold"><?= htmlspecialchars($author, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span></span>
                            <span><?= htmlspecialchars($dateLabel, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 text-center text-slate-500 text-sm">Aucun article n'a encore été publié.</div>
    <?php endif; ?>
</main>

<?php
$questions = $questions ?? [];
$popularTags = $popularTags ?? [];
$search = (string) ($search ?? '');
$tag = (string) ($tag ?? '');
$error = $error ?? null;
$notice = $notice ?? null;
$isLoggedIn = $isLoggedIn ?? false;
$questionsUrl = (string) ($urls['questions'] ?? '/questions');
$questionBase = (string) ($urls['questionBase'] ?? rtrim($questionsUrl, '/') . '/');
$loginUrl = (string) ($urls['login'] ?? '/login');

if ($popularTags === []) {
    $popularTags = array_map(
        static fn (string $name): array => ['tag' => $name, 'total' => 0],
        ['PHP', 'IoT', 'WaveAPI', 'Systalink', 'C', 'Python', 'JavaScript', 'HTML', 'CSS', 'Go', 'SQL', 'NoSQL']
    );
}

$tagColors = [
    'bg-amber-500/10 text-amber-400 border-amber-500/30',
    'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
    'bg-blue-500/10 text-blue-400 border-blue-500/30',
    'bg-red-500/10 text-red-400 border-red-500/30',
    'bg-purple-500/10 text-purple-400 border-purple-500/30',
    'bg-cyan-500/10 text-cyan-400 border-cyan-500/30',
];
?>
<main class="max-w-7xl mx-auto px-4 py-6 space-y-6 w-full">
    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        <aside class="lg:col-span-1 bg-slate-900 border border-slate-800 rounded-xl p-4 h-fit">
            <h2 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">TAGS POPULAIRES</h2>
            <div class="flex flex-wrap gap-2">
                <?php foreach ($popularTags as $item): ?>
                    <?php $tagName = trim((string) ($item['tag'] ?? '')); ?>
                    <?php if ($tagName === ''): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <a href="<?= htmlspecialchars($questionsUrl . '?tag=' . rawurlencode($tagName), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs bg-slate-800 hover:bg-slate-700 text-amber-400 px-2.5 py-1 rounded-md transition">#<?= htmlspecialchars($tagName, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                <?php endforeach; ?>
                <?php if ($search !== '' || $tag !== ''): ?>
                    <div class="w-full mt-2 pt-2 border-t border-slate-800">
                        <a href="<?= htmlspecialchars($questionsUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-xs text-amber-400 hover:underline">Réinitialiser les filtres</a>
                    </div>
                <?php endif; ?>
            </div>
        </aside>

        <section class="lg:col-span-3 space-y-6">

            <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 sm:p-6 space-y-4">
                <h2 class="text-sm font-semibold text-amber-400 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-amber-400"></span> Poser une question à la communauté
                </h2>

                <?php if ($error !== null && $error !== ''): ?>
                    <p role="alert" class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <?php endif; ?>
                <?php if ($notice !== null && $notice !== ''): ?>
                    <p role="status" class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg"><?= htmlspecialchars($notice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                <?php endif; ?>

                <?php if ($isLoggedIn): ?>
                    <form method="post" action="<?= htmlspecialchars($questionsUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="space-y-3">
                        <input type="text" name="titre" placeholder="Titre de votre question..." required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none">
                        <textarea name="contenu" rows="3" placeholder="Décrivez votre problématique technique..." required class="w-full bg-slate-950 border border-slate-800 rounded-lg p-3 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none resize-none"></textarea>
                        <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-3">
                            <input type="text" name="tags" placeholder="Tags (ex: PHP, IoT)" class="w-full sm:w-2/3 bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none">
                            <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-slate-950 font-bold text-sm px-6 py-2 rounded-lg transition w-full sm:w-auto">Publier</button>
                        </div>
                    </form>
                <?php else: ?>
                    <p class="text-xs text-slate-400">Tu dois être <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-amber-400 underline font-semibold">connecté</a> pour poser une question à la communauté.</p>
                <?php endif; ?>
            </div>

            <form method="get" action="<?= htmlspecialchars($questionsUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="flex flex-col sm:flex-row gap-2">
                <input type="text" name="q" value="<?= htmlspecialchars($search, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" placeholder="Rechercher une question par mot-clé..." class="w-full bg-slate-900 border border-slate-800 rounded-lg px-4 py-2.5 text-sm text-slate-100 focus:border-emerald-500 focus:outline-none">
                <button type="submit" class="bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-semibold px-5 py-2.5 rounded-lg transition shrink-0">Rechercher</button>
            </form>

            <div class="space-y-3">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">QUESTIONS RÉCENTES</h3>

                <?php if ($questions !== []): ?>
                    <?php foreach ($questions as $question): ?>
                        <?php
                        $questionLink = (string) ($question['url'] ?? $questionBase . rawurlencode((string) ($question['id'] ?? '')));
                        $auteur = trim((string) ($question['prenom'] ?? '') . ' ' . (string) ($question['nom'] ?? ''));
                        ?>
                        <article class="bg-slate-900 border border-slate-800 rounded-xl p-4 sm:p-5 space-y-2 hover:border-slate-700 transition">
                            <a href="<?= htmlspecialchars($questionLink, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-base font-semibold text-amber-400 hover:underline block"><?= htmlspecialchars((string) ($question['titre'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                            <p class="text-xs text-slate-300 line-clamp-2"><?= htmlspecialchars((string) ($question['contenu'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
                            <div class="flex flex-wrap items-center justify-between text-[11px] text-slate-400 pt-2 border-t border-slate-800/60 gap-2">
                                <span>Par <strong class="text-slate-200"><?= htmlspecialchars($auteur, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></strong> le <?= htmlspecialchars((string) ($question['date_creation'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></span>
                                <span class="flex flex-wrap justify-end gap-1.5">
                                    <?php foreach (explode(',', (string) ($question['tags'] ?? '')) as $questionTag): ?>
                                        <?php
                                        $questionTag = trim($questionTag);
                                        if ($questionTag === '') {
                                            continue;
                                        }
                                        $color = $tagColors[abs(crc32(mb_strtolower($questionTag))) % count($tagColors)];
                                        ?>
                                        <a href="<?= htmlspecialchars($questionsUrl . '?tag=' . rawurlencode($questionTag), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="border px-2 py-0.5 rounded <?= htmlspecialchars($color, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"><?= htmlspecialchars('#' . $questionTag, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></a>
                                    <?php endforeach; ?>
                                </span>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="bg-slate-900 border border-slate-800 rounded-xl p-4 text-center text-slate-500 text-sm">Aucune question ne correspond à votre recherche.</div>
                <?php endif; ?>
            </div>

        </section>

    </div>
</main>

<?php
$title = (string) ($title ?? 'Pause Détente : Jeu des 3 Pions');
$description = (string) ($description ?? 'Défie l\'adversaire ou l\'IA directement depuis Teranga Devs entre deux lignes de code !');
$iframeSrc = (string) ($iframeSrc ?? 'https://3-pions.netlify.app/');
$iframeTitle = (string) ($iframeTitle ?? 'Jeu 3 Pions');
?>
<main class="max-w-4xl mx-auto px-4 py-8 flex-grow w-full space-y-6 flex flex-col items-center">
    <div class="text-center space-y-2">
        <h1 class="text-2xl font-bold text-slate-100"><?= htmlspecialchars($title, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></h1>
        <p class="text-sm text-slate-400"><?= htmlspecialchars($description, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></p>
    </div>

    <div class="w-full bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-lg flex justify-center items-center overflow-hidden">
        <iframe src="<?= htmlspecialchars($iframeSrc, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"
            class="w-full h-[600px] rounded-xl border border-slate-800 bg-slate-950"
            title="<?= htmlspecialchars($iframeTitle, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></iframe>
    </div>
</main>

<?php
$urls = $urls ?? [];
?>
<footer class="bg-slate-900 border-t border-slate-800 text-slate-400 text-xs py-4 text-center mt-auto">
    <p>Teranga Devs &copy; <?= date('Y') ?> - Conçu avec fierté pour le concours Cadev</p>
</footer>

<script src="<?= htmlspecialchars($urls['mainJs'] ?? '/assets/js/main.js', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>"></script>
</body>
</html>

<?php
$error = $error ?? null;
$notice = $notice ?? null;
$email = (string) ($email ?? '');
$loginUrl = (string) ($urls['login'] ?? '/login');
$registerUrl = (string) ($urls['register'] ?? '/register');
$homeUrl = (string) ($urls['base'] ?? '/');
?>
<main class="flex-1 flex items-center justify-center p-4 w-full">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 max-w-md w-full shadow-lg space-y-6">
        <div class="text-center mb-2">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="inline-block font-bold text-2xl text-amber-400 mb-2">Teranga<span class="text-white">Devs</span></a>
            <h1 class="text-2xl font-bold text-amber-400">Connexion</h1>
            <p class="text-xs text-slate-400 mt-1">Bon retour parmi nous, développeur !</p>
        </div>

        <?php if ($error !== null && $error !== ''): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg text-center" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($notice !== null && $notice !== ''): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg text-center" role="status"><?= htmlspecialchars($notice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($loginUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="space-y-4">
            <div>
                <label for="email" class="block text-xs text-slate-400 mb-1">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required autocomplete="email" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition" placeholder="dev@terangadevs.sn">
            </div>
            <div>
                <label for="password" class="block text-xs text-slate-400 mb-1">Mot de passe</label>
                <input type="password" id="password" name="password" required autocomplete="current-password" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition" placeholder="********">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition shadow-md">Se connecter</button>
        </form>

        <p class="text-center text-xs text-slate-400 mt-6">
            Pas encore de compte ? <a href="<?= htmlspecialchars($registerUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-amber-400 hover:underline font-semibold">S'inscrire</a>
        </p>
    </div>
</main>

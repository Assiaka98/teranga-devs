<?php
$error = $error ?? null;
$notice = $notice ?? null;
$nom = (string) ($nom ?? '');
$prenom = (string) ($prenom ?? '');
$email = (string) ($email ?? '');
$registerUrl = (string) ($urls['register'] ?? '/register');
$loginUrl = (string) ($urls['login'] ?? '/login');
$homeUrl = (string) ($urls['base'] ?? '/');
?>
<main class="flex-1 flex items-center justify-center p-4 w-full">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 max-w-md w-full shadow-lg space-y-6">
        <div class="text-center mb-2">
            <a href="<?= htmlspecialchars($homeUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="inline-block font-bold text-2xl text-amber-400 mb-2">Teranga<span class="text-white">Devs</span></a>
            <h1 class="text-2xl font-bold text-amber-400">Rejoins Teranga Devs</h1>
            <p class="text-xs text-slate-400 mt-1">Crée ton compte pour participer à la communauté</p>
        </div>

        <?php if ($error !== null && $error !== ''): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg text-center" role="alert"><?= htmlspecialchars($error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <?php endif; ?>
        <?php if ($notice !== null && $notice !== ''): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg text-center" role="status"><?= htmlspecialchars($notice, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?></div>
        <?php endif; ?>

        <form method="post" action="<?= htmlspecialchars($registerUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="prenom" class="block text-xs text-slate-400 mb-1">Prénom</label>
                    <input type="text" id="prenom" name="prenom" value="<?= htmlspecialchars($prenom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required autocomplete="given-name" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition">
                </div>
                <div>
                    <label for="nom" class="block text-xs text-slate-400 mb-1">Nom</label>
                    <input type="text" id="nom" name="nom" value="<?= htmlspecialchars($nom, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required autocomplete="family-name" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition">
                </div>
            </div>
            <div>
                <label for="email" class="block text-xs text-slate-400 mb-1">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($email, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" required autocomplete="email" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition" placeholder="dev@terangadevs.sn">
            </div>
            <div>
                <label for="titre_bio" class="block text-xs text-slate-400 mb-1">Titre professionnel (optionnel)</label>
                <input type="text" id="titre_bio" name="titre_bio" maxlength="255" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition" placeholder="ex: Fullstack Developer | PHP, IoT">
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-xs text-slate-400 mb-1">Mot de passe</label>
                    <input type="password" id="password" name="password" required autocomplete="new-password" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition" placeholder="********">
                </div>
                <div>
                    <label for="password_confirmation" class="block text-xs text-slate-400 mb-1">Confirmation</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" required autocomplete="new-password" class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-amber-400 focus:outline-none transition" placeholder="********">
                </div>
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-emerald-600 to-emerald-500 hover:opacity-90 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition shadow-md">S'inscrire</button>
        </form>

        <p class="text-center text-xs text-slate-400 mt-6">
            Déjà un compte ? <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>" class="text-amber-400 hover:underline font-semibold">Se connecter</a>
        </p>
    </div>
</main>

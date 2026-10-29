<?php
require_once 'config/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom']);
    $prenom = trim($_POST['prenom']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($_POST['password'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $password]);
            $success = "Compte créé avec succès ! Tu peux te connecter.";
        } catch (Exception $e) {
            $error = "Cet email est déjà utilisé ou une erreur est survenue.";
        }
    } else {
        $error = "Tous les champs sont obligatoires.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Inscription - Teranga Devs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { teranga: { green: '#00853F', gold: '#FCD116', red: '#E31B23' } } } } }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 max-w-md w-full shadow-lg">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-teranga-green via-teranga-gold to-teranga-red bg-clip-text text-transparent">Rejoins Teranga Devs</h1>
            <p class="text-xs text-slate-400 mt-1">Crée ton compte pour participer à la communauté</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg mb-4"><?= $error ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg mb-4"><?= $success ?> <a href="login.php" class="underline font-bold">Connecte-toi</a></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Prénom</label>
                    <input type="text" name="prenom" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-teranga-green focus:outline-none">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nom</label>
                    <input type="text" name="nom" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-teranga-green focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Email</label>
                <input type="email5" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-teranga-green focus:outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Mot de passe</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-teranga-green focus:outline-none">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-teranga-green to-emerald-600 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition shadow-md">S'inscrire</button>
        </form>
        <p class="text-center text-xs text-slate-400 mt-6">Déjà un compte ? <a href="login.php" class="text-teranga-gold hover:underline">Se connecter</a></p>
    </div>
</body>
</html>
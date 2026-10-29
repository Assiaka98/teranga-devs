<?php
require_once 'config/db.php';
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        header('Location: index.php');
        exit;
    } else {
        $error = "Email ou mot de passe incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <title>Connexion - Teranga Devs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { teranga: { green: '#00853F', gold: '#FCD116', red: '#E31B23' } } } } }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 max-w-md w-full shadow-lg">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold bg-gradient-to-r from-teranga-green via-teranga-gold to-teranga-red bg-clip-text text-transparent">Connexion</h1>
            <p class="text-xs text-slate-400 mt-1">Bon retour parmi nous, développeur !</p>
        </div>

        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg mb-4"><?= $error ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Email</label>
                <input type="email" name="email" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-teranga-green focus:outline-none">
            </div>
            <div>
                <label class="block text-xs text-slate-400 mb-1">Mot de passe</label>
                <input type="password" name="password" required class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2 text-sm text-slate-100 focus:border-teranga-green focus:outline-none">
            </div>
            <button type="submit" class="w-full bg-gradient-to-r from-teranga-green to-emerald-600 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition shadow-md">Se connecter</button>
        </form>
        <p class="text-center text-xs text-slate-400 mt-6">Pas encore de compte ? <a href="register.php" class="text-teranga-gold hover:underline">S'inscrire</a></p>
    </div>
</body>
</html>
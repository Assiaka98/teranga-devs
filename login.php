<?php
session_start();
require_once 'includes/auth_guard.php'; // Bloque l'accès si non connecté[cite: 5]
require_once 'config/db.php';

$error = '';
$success = '';

// Gestion des messages de succès (par exemple après inscription)
if (isset($_GET['success']) && $_GET['success'] === 'registered') {
    $success = 'Inscription réussie ! Tu peux te connecter.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['mot_de_passe'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['nom'];
        $_SESSION['user_prenom'] = $user['prenom'];
        $_SESSION['username'] = $user['prenom'] . ' ' . $user['nom'];
        
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion - Teranga Devs</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = { 
            darkMode: 'class', 
            theme: { 
                extend: { 
                    colors: { 
                        teranga: { green: '#00853F', gold: '#FCD116', red: '#E31B23' } 
                    } 
                } 
            } 
        }
    </script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-4">
    <div class="bg-slate-900 border border-slate-800 rounded-xl p-8 max-w-md w-full shadow-lg space-y-6">
        
        <div class="text-center mb-2">
            <a href="index.php" class="inline-block font-bold text-2xl text-amber-400 mb-2">Teranga<span class="text-white">Devs</span></a>
            <h1 class="text-2xl font-bold bg-gradient-to-r from-teranga-green via-teranga-gold to-teranga-red bg-clip-text text-transparent">Connexion</h1>
            <p class="text-xs text-slate-400 mt-1">Bon retour parmi nous, développeur !</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg text-center"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div>
                <label class="block text-xs text-slate-400 mb-1">Email</label>
                <input type="email" name="email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required 
                    class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-teranga-green focus:outline-none transition"
                    placeholder="dev@terangadevs.sn">
            </div>
            
            <div>
                <label class="block text-xs text-slate-400 mb-1">Mot de passe</label>
                <div class="relative">
                    <input type="password" name="password" id="password" required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-teranga-green focus:outline-none transition"
                        placeholder="••••••••">
                    <button type="button" onclick="togglePassword()" class="absolute right-3 top-3 text-xs text-slate-500 hover:text-slate-300">
                        👁️
                    </button>
                </div>
            </div>

            <button type="submit" class="w-full bg-gradient-to-r from-teranga-green to-emerald-600 text-slate-950 font-bold py-2.5 rounded-lg text-sm transition shadow-md hover:opacity-90">
                Se connecter
            </button>
        </form>

        <p class="text-center text-xs text-slate-400 mt-6">
            Pas encore de compte ? <a href="register.php" class="text-teranga-gold hover:underline font-semibold">S'inscrire</a>
        </p>
    </div>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }
    </script>
</body>
</html>
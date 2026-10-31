<?php
require_once 'config/db.php';
session_start();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($nom) && !empty($prenom) && !empty($email) && !empty($password)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $pdo->prepare("INSERT INTO users (nom, prenom, email, mot_de_passe) VALUES (?, ?, ?, ?)");
            $stmt->execute([$nom, $prenom, $email, $hashedPassword]);
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription - Teranga Devs</title>
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
            <h1 class="text-2xl font-bold bg-gradient-to-r from-teranga-green via-teranga-gold to-teranga-red bg-clip-text text-transparent">Rejoins Teranga Devs</h1>
            <p class="text-xs text-slate-400 mt-1">Crée ton compte pour participer à la communauté</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 text-xs p-3 rounded-lg text-center"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if (!empty($success)): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs p-3 rounded-lg text-center">
                <?= htmlspecialchars($success) ?> <a href="login.php" class="underline font-bold text-emerald-300">Connecte-toi</a>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Prénom</label>
                    <input type="text" name="prenom" value="<?= htmlspecialchars($_POST['prenom'] ?? '') ?>" required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-teranga-green focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-xs text-slate-400 mb-1">Nom</label>
                    <input type="text" name="nom" value="<?= htmlspecialchars($_POST['nom'] ?? '') ?>" required 
                        class="w-full bg-slate-950 border border-slate-800 rounded-lg px-3 py-2.5 text-sm text-slate-100 focus:border-teranga-green focus:outline-none transition">
                </div>
            </div>
            
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
                S'inscrire
            </button>
        </form>

        <p class="text-center text-xs text-slate-400 mt-6">
            Déjà un compte ? <a href="login.php" class="text-teranga-gold hover:underline font-semibold">Se connecter</a>
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
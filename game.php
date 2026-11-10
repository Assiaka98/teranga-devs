<?php
session_start();
require_once 'config/db.php';
?>
<!DOCTYPE html>
<html lang="fr" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pause Détente : 3 Pions - Teranga Devs</title>
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
<body class="bg-slate-950 text-slate-100 font-sans min-h-screen flex flex-col justify-between">
<?php require_once __DIR__ . '/includes/header.php'; ?>

    <!-- Contenu du Jeu (C'est ici que ton extrait se place) -->
    <main class="max-w-4xl mx-auto px-4 py-8 flex-grow w-full space-y-6 flex flex-col items-center">
        <div class="text-center space-y-2">
            <h1 class="text-2xl font-bold text-slate-100">Pause Détente : Jeu des 3 Pions 🇸🇳</h1>
            <p class="text-sm text-slate-400">Défie l'adversaire ou l'IA directement depuis Teranga Devs entre deux lignes de code !</p>
        </div>

        <!-- Conteneur iFrame pour afficher ton jeu Netlify -->
        <div class="w-full bg-slate-900 border border-slate-800 rounded-2xl p-4 shadow-lg flex justify-center items-center overflow-hidden">
            <iframe src="https://3-pions.netlify.app/" 
                class="w-full h-[600px] rounded-xl border border-slate-800 bg-slate-950" 
                title="Jeu 3 Pions">
            </iframe>
        </div>
    </main>

    <!-- Footer -->
<?php
require_once 'includes/footer.php';
?>
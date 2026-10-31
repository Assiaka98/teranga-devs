<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Récupérer le nom du fichier actuel
$current_page = basename($_SERVER['PHP_SELF']);

// Pages publiques autorisées sans être connecté
$public_pages = ['login.php', 'register.php', 'index.php', 'question.php'];

// Si la page n'est pas publique et que l'utilisateur n'est pas connecté
if (!in_array($current_page, $public_pages) && !isset($_SESSION['user_id'])) {
    header('Location: login.php?error=unauthorized');
    exit;
}
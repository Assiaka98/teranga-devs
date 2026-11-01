<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$current_page = basename($_SERVER['PHP_SELF']);

// Pages publiques autorisées sans être connecté (ajout de articles, article_read, game)
$public_pages = ['login.php', 'register.php', 'index.php', 'question.php', 'articles.php', 'article_read.php', 'game.php'];

if (!in_array($current_page, $public_pages) && !isset($_SESSION['user_id'])) {
    header('Location: login.php?error=unauthorized');
    exit;
}
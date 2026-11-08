<?php
session_start();
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/auth_guard.php';

$article_id = $_GET['id'] ?? null;
$user_id = $_SESSION['user_id'] ?? null;

if (!$article_id || !$user_id) {
    header('Location: articles.php');
    exit;
}

try {
    // 1. Vérifier que l'article appartient bien à l'utilisateur connecté
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$article_id]);
    $article = $stmt->fetch();

    if ($article && $article['user_id'] == $user_id) {
        // 2. Supprimer les votes liés à l'article pour respecter l'intégrité référentielle
        $stmtVotes = $pdo->prepare("DELETE FROM article_votes WHERE article_id = ?");
        $stmtVotes->execute([$article_id]);

        // 3. Supprimer l'article
        $stmtDel = $pdo->prepare("DELETE FROM articles WHERE id = ? AND user_id = ?");
        $stmtDel.execute([$article_id, $user_id]);
    }
} catch (Exception $e) {
    // En cas d'erreur silencieuse, on redirige quand même
}

header('Location: articles.php');
exit;
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// Appel du système de session, de sécurité et de la base de données
require_once __DIR__ . '/includes/auth_guard.php';
require_once __DIR__ . '/config/db.php';

// Récupération du slug OU de l'ID depuis la requête GET
$slug = $_GET['slug'] ?? null;
$article_id = $_GET['id'] ?? null;

if (!$slug && !$article_id) {
    header('Location: articles.php');
    exit;
}

$user_id = $_SESSION['user_id'] ?? null;

// Récupération des informations de l'article (par slug ou par id)
try {
    if ($slug) {
        $stmt = $pdo->prepare("
            SELECT a.*, u.nom, u.prenom, u.reputation, u.id as author_id 
            FROM articles a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.slug = ?
        ");
        $stmt->execute([$slug]);
    } else {
        $stmt = $pdo->prepare("
            SELECT a.*, u.nom, u.prenom, u.reputation, u.id as author_id 
            FROM articles a 
            JOIN users u ON a.user_id = u.id 
            WHERE a.id = ?
        ");
        $stmt->execute([$article_id]);
    }

    $article = $stmt->fetch();
    if (!$article) {
        header('Location: articles.php');
        exit;
    }
    
    // Définir $article_id pour le système de votes
    $article_id = $article['id'];

} catch (Exception $e) {
    header('Location: articles.php');
    exit;
}

// Traitement du vote...
if (isset($_POST['upvote']) && $user_id) {
    $stmtCheck = $pdo->prepare("SELECT * FROM article_votes WHERE article_id = ? AND user_id = ?");
    $stmtCheck->execute([$article_id, $user_id]);
    
    if ($stmtCheck->rowCount() == 0 && $article['author_id'] != $user_id) {
        $insertVote = $pdo->prepare("INSERT INTO article_votes (article_id, user_id) VALUES (?, ?)");
        $insertVote->execute([$article_id, $user_id]);

        $updateStmt = $pdo->prepare("UPDATE articles SET votes = votes + 1 WHERE id = ?");
        $updateStmt->execute([$article_id]);
        
        $karmaStmt = $pdo->prepare("UPDATE users SET reputation = reputation + 5 WHERE id = ?");
        $karmaStmt->execute([$article['author_id']]);
    }
    
    // Redirection après le vote
    $redirectUrl = !empty($article['slug']) ? "article_read.php?slug=" . $article['slug'] : "article_read.php?id=" . $article_id;
    header("Location: " . $redirectUrl);
    exit;
}

// Vérification si déjà voté
$has_voted = false;
if ($user_id) {
    $stmtVoteCheck = $pdo->prepare("SELECT * FROM article_votes WHERE article_id = ? AND user_id = ?");
    $stmtVoteCheck->execute([$article_id, $user_id]);
    $has_voted = $stmtVoteCheck->rowCount() > 0;
}

$pageTitle = $article['titre'] . " - Teranga Devs";
?>
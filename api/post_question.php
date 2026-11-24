<?php
// post_question.php
session_start();
require_once 'config/db.php';

// Redirection ou réponse si l'utilisateur n'est pas connecté
if (!isset($_SESSION['user_id'])) {
    if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour publier.']);
    } else {
        header('Location: login.php');
    }
    exit;
}

// Récupérer les données (provenant d'un formulaire classique POST ou d'une requête JSON)
$titre = trim($_POST['titre'] ?? '');
$contenu = trim($_POST['contenu'] ?? '');
$tags = trim($_POST['tags'] ?? '');

// Si le formulaire est soumis en JSON (API)
$contentType = isset($_SERVER["CONTENT_TYPE"]) ? trim($_SERVER["CONTENT_TYPE"]) : '';
if (strpos($contentType, 'application/json') !== false) {
    $data = json_decode(file_get_contents('php://input'), true);
    $titre = trim($data['titre'] ?? '');
    $contenu = trim($data['contenu'] ?? '');
    $tags = trim($data['tags'] ?? '');
}

// Validation des champs obligatoires
if (empty($titre) || empty($contenu)) {
    if (strpos($contentType, 'application/json') !== false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Données incomplètes (titre et contenu requis).']);
    } else {
        // Redirection vers l'accueil avec une erreur si formulaire classique
        header('Location: index.php?error=champs_incomplets');
    }
    exit;
}

try {
    // S'assurer que la table questions possède bien les colonnes nécessaires en local (SQLite)
    $pdo->exec("CREATE TABLE IF NOT EXISTS questions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        titre TEXT,
        contenu TEXT,
        tags TEXT,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $stmt = $pdo->prepare("INSERT INTO questions (user_id, titre, contenu, tags, date_creation) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'],
        $titre,
        $contenu,
        $tags,
        date('Y-m-d H:i:s')
    ]);

    $question_id = $pdo->lastInsertId();

    // Réponse adaptée selon qu'il s'agit d'une requête API (JSON) ou d'un formulaire classique
    if (strpos($contentType, 'application/json') !== false) {
        echo json_encode([
            'success' => true,
            'message' => 'Question publiée avec succès !',
            'id' => $question_id
        ]);
    } else {
        // Redirection propre vers la page d'accueil (ou le détail de la question)
        header('Location: index.php');
        exit;
    }

} catch (Exception $e) {
    if (strpos($contentType, 'application/json') !== false) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    } else {
        die("Erreur lors de la publication de la question : " . htmlspecialchars($e->getMessage()));
    }
}
?>
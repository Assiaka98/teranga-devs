<?php
session_start();
header('Content-Type: application/json');
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour voter.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$response_id = filter_var($data['response_id'] ?? null, FILTER_VALIDATE_INT);
$user_id = $_SESSION['user_id'];

if (!$response_id) {
    echo json_encode(['success' => false, 'error' => 'Identifiant de réponse invalide.']);
    exit;
}

try {
    // 1. Récupérer la réponse et son auteur
    $stmt = $pdo->prepare("SELECT user_id, votes FROM responses WHERE id = ?");
    $stmt->execute([$response_id]);
    $response = $stmt->fetch();

    if (!$response) {
        echo json_encode(['success' => false, 'error' => 'Réponse introuvable.']);
        exit;
    }

    if ($response['user_id'] == $user_id) {
        echo json_encode(['success' => false, 'error' => 'Vous ne pouvez pas voter pour votre propre réponse.']);
        exit;
    }

    // 2. Vérifier si l'utilisateur a déjà voté
    $stmtCheck = $pdo->prepare("SELECT id FROM response_votes WHERE response_id = ? AND user_id = ?");
    $stmtCheck->execute([$response_id, $user_id]);
    
    if ($stmtCheck->fetch()) {
        echo json_encode(['success' => false, 'error' => 'Vous avez déjà voté pour cette réponse.']);
        exit;
    }

    // 3. Enregistrer le vote
    $stmtVote = $pdo->prepare("INSERT INTO response_votes (response_id, user_id) VALUES (?, ?)");
    $stmtVote->execute([$response_id, $user_id]);

    // 4. Incrémenter les votes de la réponse
    $stmtUpdateResp = $pdo->prepare("UPDATE responses SET votes = votes + 1 WHERE id = ?");
    $stmtUpdateResp->execute([$response_id]);

    // 5. Ajouter +10 Karma à l'auteur
    $stmtUpdateUser = $pdo->prepare("UPDATE users SET karma = karma + 10 WHERE id = ?");
    $stmtUpdateUser->execute([$response['user_id']]);

    $new_votes = $response['votes'] + 1;

    echo json_encode(['success' => true, 'new_votes' => $new_votes]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Erreur technique : ' . $e->getMessage()]);
}
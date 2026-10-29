<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/db.php';

// 1. Vérifier si l'utilisateur est bien connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour répondre.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['question_id'], $data['contenu'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données incomplètes']);
    exit;
}

try {
    // 2. Insérer la réponse en utilisant l'ID de l'utilisateur connecté via la session
    $stmt = $pdo->prepare("INSERT INTO responses (question_id, user_id, contenu) VALUES (?, ?, ?)");
    $stmt->execute([
        $data['question_id'],
        $_SESSION['user_id'], // ID de session sécurisé
        $data['contenu']
    ]);

    // 3. Augmenter le karma / réputation de l'utilisateur qui a répondu (+5 points)
    $stmtKarma = $pdo->prepare("UPDATE users SET reputation = reputation + 5 WHERE id = ?");
    $stmtKarma->execute([$_SESSION['user_id']]);

    echo json_encode([
        'success' => true,
        'message' => 'Réponse ajoutée avec succès !'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
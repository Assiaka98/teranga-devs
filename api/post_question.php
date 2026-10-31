<?php
session_start();
require_once 'includes/auth_guard.php'; // Bloque l'accès si non connecté
require_once 'config/db.php';

// Le reste de ta page sensible...

// Vérifier si l'utilisateur est bien connecté
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Vous devez être connecté pour publier.']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['titre'], $data['contenu'], $data['tags'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Données incomplètes']);
    exit;
}

try {
    $stmt = $pdo->prepare("INSERT INTO questions (user_id, titre, contenu, tags) VALUES (?, ?, ?, ?)");
    $stmt->execute([
        $_SESSION['user_id'], // Utilise l'ID de session de l'utilisateur actif
        $data['titre'],
        $data['contenu'],
        $data['tags']
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Question publiée avec succès !'
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

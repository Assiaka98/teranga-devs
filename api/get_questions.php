<?php
header('Content-Type: application/json; charset=UTF-8');
require_once '../config/db.php';

try {
    $stmt = $pdo->query("
        SELECT q.*, u.nom, u.prenom, u.reputation 
        FROM questions q 
        JOIN users u ON q.user_id = u.id 
        ORDER BY q.date_creation DESC
    ");
    $questions = $stmt->fetchAll();
    
    echo json_encode([
        'success' => true,
        'data' => $questions
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
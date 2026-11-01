<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response_id = filter_var($_POST['response_id'] ?? null, FILTER_VALIDATE_INT);
    $question_id = filter_var($_POST['question_id'] ?? null, FILTER_VALIDATE_INT);
    $user_id = $_SESSION['user_id'];

    if ($response_id && $question_id) {
        // Vérifier que l'utilisateur connecté est bien l'auteur de la question
        $stmtQ = $pdo->prepare("SELECT user_id FROM questions WHERE id = ?");
        $stmtQ->execute([$question_id]);
        $question = $stmtQ->fetch();

        if ($question && $question['user_id'] == $user_id) {
            // Récupérer l'auteur de la réponse pour lui donner du karma
            $stmtR = $pdo->prepare("SELECT user_id FROM responses WHERE id = ?");
            $stmtR->execute([$response_id]);
            $response = $stmtR->fetch();

            if ($response) {
                // Marquer la réponse comme solution
                $stmtUpdate = $pdo->prepare("UPDATE responses SET is_solution = 1 WHERE id = ?");
                $stmtUpdate->execute([$response_id]);

                // Ajouter +50 karma à l'auteur de la réponse
                $stmtKarma = $pdo->prepare("UPDATE users SET karma = karma + 50 WHERE id = ?");
                $stmtKarma->execute([$response['user_id']]);
            }
        }
    }

    // Rediriger vers la page de la question avec son slug
    $stmtSlug = $pdo->prepare("SELECT titre FROM questions WHERE id = ?");
    $stmtSlug->execute([$question_id]);
    $qData = $stmtSlug->fetch();
    $slug = $qData ? slugify($qData['titre']) : '';

    header("Location: ../question.php?id=" . $question_id . "&slug=" . $slug);
    exit;
}
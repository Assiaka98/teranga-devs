<?php

declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$root = dirname(__DIR__);

require_once $root . '/core/Router.php';
require_once $root . '/app/Models/QuestionModel.php';
require_once $root . '/app/Controllers/QuestionController.php';
require_once $root . '/app/Models/ArticleModel.php';
require_once $root . '/app/Controllers/ArticleController.php';
require_once $root . '/app/Models/UserModel.php';
require_once $root . '/app/Controllers/UserController.php';
require_once $root . '/app/Controllers/AuthController.php';
require_once $root . '/app/Controllers/GameController.php';
require_once $root . '/config/db.php';

$userModel = new UserModel($pdo);
$controller = new QuestionController(new QuestionModel($pdo));
$articleController = new ArticleController(new ArticleModel($pdo));
$userController = new UserController($userModel);
$authController = new AuthController($userModel);
$gameController = new GameController();

$router = new Router();
$router->get('/', [$controller, 'index']);
$router->get('/questions', [$controller, 'index']);
$router->get('/questions/{id}', [$controller, 'show']);
$router->post('/', [$controller, 'store']);
$router->post('/questions', [$controller, 'store']);
$router->get('/articles', [$articleController, 'index']);
$router->get('/articles/create', [$articleController, 'create']);
$router->get('/articles/{slug}', [$articleController, 'show']);
$router->get('/articles/{id}/edit', [$articleController, 'edit']);
$router->post('/articles', [$articleController, 'store']);
$router->post('/articles/{id}/edit', [$articleController, 'update']);
$router->post('/articles/{id}/delete', [$articleController, 'destroy']);
$router->post('/articles/{id}/vote', [$articleController, 'vote']);
$router->get('/login', [$authController, 'showLogin']);
$router->post('/login', [$authController, 'login']);
$router->get('/register', [$authController, 'showRegister']);
$router->post('/register', [$authController, 'register']);
$router->get('/logout', [$authController, 'logout']);
$router->post('/logout', [$authController, 'logout']);
$router->get('/profile', [$userController, 'profile']);
$router->get('/profile/edit', [$userController, 'edit']);
$router->post('/profile/edit', [$userController, 'update']);
$router->get('/profile/{id}', [$userController, 'profile']);
$router->post('/profile/{id}', [$userController, 'update']);
$router->post('/profile/{id}/password', [$userController, 'password']);
$router->get('/game', [$gameController, 'index']);
$router->get('/game.php', [$gameController, 'index']);
$router->dispatch();

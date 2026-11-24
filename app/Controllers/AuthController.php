<?php

declare(strict_types=1);

final class AuthController
{
    public function __construct(private UserModel $userModel)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function showLogin(): void
    {
        if ($this->userId() !== null && !$this->wantsJson()) {
            $this->redirect($this->homeUrl());
            return;
        }

        $this->html($this->render('auth/login', [
            'pageTitle' => 'Connexion',
            'error' => $this->message(isset($_GET['error']) ? (string) $_GET['error'] : null),
            'notice' => $this->message(isset($_GET['success']) ? (string) $_GET['success'] : null),
            'email' => isset($_GET['email']) ? (string) $_GET['email'] : '',
        ]));
    }

    public function login(): void
    {
        $wantsJson = $this->wantsJson();
        $data = $this->requestData();
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');

        if ($email === '' || $password === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->loginFail('Adresse email ou mot de passe invalide.', $wantsJson, $email);
            return;
        }

        $user = $this->userModel->findByEmail($email);
        if ($user === null || !password_verify($password, (string) ($user['mot_de_passe'] ?? ''))) {
            $this->loginFail('Email ou mot de passe incorrect.', $wantsJson, $email);
            return;
        }

        session_regenerate_id(true);
        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['user_nom'] = (string) ($user['nom'] ?? '');
        $_SESSION['user_prenom'] = (string) ($user['prenom'] ?? '');
        $_SESSION['nom'] = $_SESSION['user_nom'];
        $_SESSION['prenom'] = $_SESSION['user_prenom'];
        $_SESSION['username'] = trim($_SESSION['user_prenom'] . ' ' . $_SESSION['user_nom']);

        $publicUser = $user;
        unset($publicUser['mot_de_passe']);

        if ($wantsJson) {
            $this->json(200, ['success' => true, 'message' => 'Connexion réussie.', 'data' => $publicUser]);
            return;
        }

        $this->redirect($this->homeUrl());
    }

    public function showRegister(): void
    {
        $this->html($this->render('auth/register', [
            'pageTitle' => 'Inscription',
            'error' => $this->message(isset($_GET['error']) ? (string) $_GET['error'] : null),
            'notice' => $this->message(isset($_GET['success']) ? (string) $_GET['success'] : null),
            'nom' => isset($_GET['nom']) ? (string) $_GET['nom'] : '',
            'prenom' => isset($_GET['prenom']) ? (string) $_GET['prenom'] : '',
            'email' => isset($_GET['email']) ? (string) $_GET['email'] : '',
        ]));
    }

    public function register(): void
    {
        $wantsJson = $this->wantsJson();
        $data = $this->requestData();
        $nom = trim((string) ($data['nom'] ?? ''));
        $prenom = trim((string) ($data['prenom'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $password = (string) ($data['password'] ?? '');
        $confirm = (string) ($data['password_confirmation'] ?? '');
        $titreBio = isset($data['titre_bio']) ? trim((string) $data['titre_bio']) : null;

        if ($nom === '' || $prenom === '' || filter_var($email, FILTER_VALIDATE_EMAIL) === false) {
            $this->registerFail('Tous les champs sont obligatoires et l\'email doit être valide.', $wantsJson, $data);
            return;
        }
        if (mb_strlen($password) < 6) {
            $this->registerFail('Le mot de passe doit contenir au moins 6 caractères.', $wantsJson, $data);
            return;
        }
        if ($password !== $confirm) {
            $this->registerFail('La confirmation du mot de passe ne correspond pas.', $wantsJson, $data);
            return;
        }
        if ($this->userModel->findByEmail($email) !== null) {
            $this->registerFail('Cet email est déjà utilisé.', $wantsJson, $data);
            return;
        }

        try {
            $user = $this->userModel->create(
                $prenom . ' ' . $nom,
                $email,
                password_hash($password, PASSWORD_BCRYPT),
                $titreBio === '' || $titreBio === null ? null : $titreBio
            );
        } catch (Throwable $e) {
            error_log($e->getMessage());
            $this->registerFail('Une erreur est survenue lors de l\'inscription.', $wantsJson, $data, 500);
            return;
        }

        $publicUser = $user;
        unset($publicUser['mot_de_passe']);

        if ($wantsJson) {
            $this->json(201, ['success' => true, 'message' => 'Compte créé avec succès.', 'data' => $publicUser]);
            return;
        }

        $this->redirect('/login?success=registered');
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }
        session_destroy();

        if ($this->wantsJson()) {
            $this->json(200, ['success' => true, 'message' => 'Déconnecté.']);
            return;
        }

        $this->redirect('/login?success=logout');
    }

    private function loginFail(string $error, bool $wantsJson, string $email): void
    {
        if ($wantsJson) {
            $this->json(401, ['success' => false, 'error' => $error]);
            return;
        }

        $this->html($this->render('auth/login', [
            'pageTitle' => 'Connexion',
            'error' => $error,
            'notice' => null,
            'email' => $email,
        ]));
    }

    private function registerFail(string $error, bool $wantsJson, array $data, int $status = 400): void
    {
        if ($wantsJson) {
            $this->json($status, ['success' => false, 'error' => $error]);
            return;
        }

        $this->html($this->render('auth/register', [
            'pageTitle' => 'Inscription',
            'error' => $error,
            'notice' => null,
            'nom' => (string) ($data['nom'] ?? ''),
            'prenom' => (string) ($data['prenom'] ?? ''),
            'email' => (string) ($data['email'] ?? ''),
        ]));
    }

    private function wantsJson(): bool
    {
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        $accept = strtolower((string) ($_SERVER['HTTP_ACCEPT'] ?? ''));
        $requestedWith = strtolower((string) ($_SERVER['HTTP_X_REQUESTED_WITH'] ?? ''));

        return str_contains($contentType, 'application/json')
            || str_contains($accept, 'application/json')
            || $requestedWith === 'xmlhttprequest';
    }

    private function requestData(): array
    {
        $contentType = strtolower((string) ($_SERVER['CONTENT_TYPE'] ?? ''));
        if (!str_contains($contentType, 'application/json')) {
            return $_POST;
        }

        $raw = file_get_contents('php://input');
        $data = is_string($raw) ? json_decode($raw, true) : null;

        return is_array($data) ? $data : [];
    }

    private function userId(): ?int
    {
        $userId = filter_var($_SESSION['user_id'] ?? null, FILTER_VALIDATE_INT);

        return $userId === false ? null : $userId;
    }

    private function json(int $status, array $payload): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    }

    private function html(string $content, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
    }

    private function redirect(string $location): void
    {
        header('Location: ' . $location);
    }

    private function render(string $view, array $data = []): string
    {
        $data['pageTitle'] ??= 'Teranga Devs';

        return $this->renderLayout((string) $data['pageTitle'], $this->view($view, $data));
    }

    private function renderLayout(string $title, string $body): string
    {
        $pageTitle = $title;
        $urls = $this->urls();
        $root = dirname(__DIR__, 2);

        ob_start();
        require $root . '/app/Views/layouts/header.php';
        echo $body;
        require $root . '/app/Views/layouts/footer.php';

        return (string) ob_get_clean();
    }

    private function view(string $name, array $data): string
    {
        $data['urls'] ??= $this->urls();
        extract($data, EXTR_SKIP);
        $root = dirname(__DIR__, 2);

        ob_start();
        require $root . '/app/Views/' . $name . '.php';

        return (string) ob_get_clean();
    }

    private function urls(): array
    {
        return [
            'base' => '/',
            'articles' => '/articles',
            'questions' => '/questions',
            'profile' => '/profile',
            'login' => '/login',
            'register' => '/register',
            'logout' => '/logout',
            'game' => '/game',
            'mainJs' => '/assets/js/main.js',
        ];
    }

    private function message(?string $code): ?string
    {
        if ($code === null) {
            return null;
        }

        return match ($code) {
            'registered' => 'Compte créé avec succès ! Tu peux te connecter.',
            'logout' => 'Tu as été déconnecté.',
            'required' => 'Vous devez être connecté pour accéder à cette page.',
            default => $code,
        };
    }

    private function homeUrl(): string
    {
        return '/';
    }
}

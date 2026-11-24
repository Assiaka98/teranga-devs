<?php

declare(strict_types=1);

final class UserController
{
    private const MAX_AVATAR_BYTES = 2097152;

    public function __construct(private UserModel $userModel)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function profile(?string $id = null): void
    {
        $wantsJson = $this->wantsJson();
        $profileId = $this->resolveProfileId($id, $wantsJson);
        if ($profileId === null) {
            return;
        }

        $user = $this->userModel->findById($profileId);
        if ($user === null) {
            $this->fail(404, 'Utilisateur introuvable.');
            return;
        }

        $articles = $this->userModel->getArticlesByUser($profileId);
        $questions = $this->userModel->getQuestionsByUser($profileId);

        if ($wantsJson) {
            $this->json(200, [
                'success' => true,
                'data' => ['user' => $user, 'articles' => $articles, 'questions' => $questions],
            ]);
            return;
        }

        $this->html($this->render('users/profile', [
            'pageTitle' => 'Profil de ' . $user['prenom'] . ' ' . $user['nom'],
            'user' => $user,
            'articles' => $articles,
            'questions' => $questions,
            'isMyProfile' => $this->userId() === $profileId,
            'editOpen' => false,
            'profileUrl' => $this->profileUrl($profileId),
            'editUrl' => '/profile/edit',
            'error' => $this->message(isset($_GET['error']) ? (string) $_GET['error'] : null),
            'notice' => $this->message(isset($_GET['success']) ? (string) $_GET['success'] : null),
        ]));
    }

    public function show(?string $id = null): void
    {
        $this->profile($id);
    }

    public function edit(): void
    {
        $userId = $this->userId();
        if ($userId === null) {
            $this->unauthorized(false);
            return;
        }

        $user = $this->userModel->findById($userId);
        if ($user === null) {
            $this->fail(404, 'Utilisateur introuvable.');
            return;
        }

        $this->html($this->render('users/profile', [
            'pageTitle' => 'Modifier mon profil',
            'user' => $user,
            'articles' => $this->userModel->getArticlesByUser($userId),
            'questions' => $this->userModel->getQuestionsByUser($userId),
            'isMyProfile' => true,
            'editOpen' => true,
            'profileUrl' => $this->profileUrl($userId),
            'editUrl' => '/profile/edit',
            'error' => $this->message(isset($_GET['error']) ? (string) $_GET['error'] : null),
            'notice' => $this->message(isset($_GET['success']) ? (string) $_GET['success'] : null),
        ]));
    }

    public function update(?string $id = null): void
    {
        $wantsJson = $this->wantsJson();
        $profileId = $this->resolveOwnedProfileId($id, $wantsJson);
        if ($profileId === null) {
            return;
        }

        $user = $this->userModel->findById($profileId);
        if ($user === null) {
            $this->fail(404, 'Utilisateur introuvable.');
            return;
        }

        try {
            $data = $this->requestData();
            $nom = array_key_exists('nom', $data) ? trim((string) $data['nom']) : (string) ($user['nom'] ?? '');
            if ($nom === '') {
                throw new InvalidArgumentException('Le nom est obligatoire.');
            }

            $titreBio = null;
            if (array_key_exists('titre_bio', $data)) {
                $titreBio = trim((string) $data['titre_bio']);
                if (mb_strlen($titreBio) > 255) {
                    throw new InvalidArgumentException('La description ne doit pas dépasser 255 caractères.');
                }
            }

            $avatarPath = $this->uploadedAvatar();
            $this->userModel->updateProfile($profileId, $nom, $titreBio, $avatarPath);

            if ($wantsJson) {
                $this->json(200, [
                    'success' => true,
                    'message' => 'Profil mis à jour avec succès.',
                    'data' => $this->userModel->findById($profileId),
                ]);
                return;
            }

            $this->redirect($this->profileUrl($profileId) . '?success=profil');
        } catch (InvalidArgumentException $e) {
            $this->badRequest($e->getMessage(), $wantsJson, $profileId);
        } catch (Throwable $e) {
            $this->serverError($e, $wantsJson, $profileId);
        }
    }

    public function password(string $id): void
    {
        $profileId = filter_var($id, FILTER_VALIDATE_INT);
        if ($profileId === false) {
            $this->fail(400, 'Identifiant invalide.');
            return;
        }

        $wantsJson = $this->wantsJson();
        $userId = $this->userId();
        if ($userId === null) {
            $this->unauthorized($wantsJson);
            return;
        }
        if ($userId !== $profileId) {
            $this->fail(403, 'Vous ne pouvez pas modifier ce profil.');
            return;
        }

        try {
            $data = $this->requestData();
            $current = (string) ($data['current_password'] ?? '');
            $new = (string) ($data['new_password'] ?? '');
            $confirm = (string) ($data['confirm_password'] ?? '');

            if ($current === '' || $new === '' || $confirm === '') {
                throw new InvalidArgumentException('Tous les champs de mot de passe sont obligatoires.');
            }
            if ($new !== $confirm) {
                throw new InvalidArgumentException('La confirmation ne correspond pas au nouveau mot de passe.');
            }
            if (mb_strlen($new) < 6) {
                throw new InvalidArgumentException('Le nouveau mot de passe doit faire au moins 6 caractères.');
            }

            $hash = $this->userModel->findPasswordHash($profileId);
            if ($hash === null || !password_verify($current, $hash)) {
                throw new InvalidArgumentException('Le mot de passe actuel est incorrect.');
            }

            $this->userModel->updatePassword($profileId, password_hash($new, PASSWORD_BCRYPT));

            if ($wantsJson) {
                $this->json(200, ['success' => true, 'message' => 'Mot de passe modifié avec succès.']);
                return;
            }

            $this->redirect($this->profileUrl($profileId) . '?success=password');
        } catch (InvalidArgumentException $e) {
            $this->badRequest($e->getMessage(), $wantsJson, $profileId);
        } catch (Throwable $e) {
            $this->serverError($e, $wantsJson, $profileId);
        }
    }

    private function resolveProfileId(?string $id, bool $wantsJson): ?int
    {
        if ($id !== null && $id !== '') {
            $profileId = filter_var($id, FILTER_VALIDATE_INT);
            if ($profileId === false) {
                $this->fail(400, 'Identifiant invalide.');
                return null;
            }

            return $profileId;
        }

        $userId = $this->userId();
        if ($userId === null) {
            $this->unauthorized($wantsJson);
            return null;
        }

        return $userId;
    }

    private function resolveOwnedProfileId(?string $id, bool $wantsJson): ?int
    {
        $userId = $this->userId();
        if ($userId === null) {
            $this->unauthorized($wantsJson);
            return null;
        }
        if ($id === null || $id === '') {
            return $userId;
        }

        $profileId = filter_var($id, FILTER_VALIDATE_INT);
        if ($profileId === false) {
            $this->fail(400, 'Identifiant invalide.');
            return null;
        }
        if ($profileId !== $userId) {
            $this->fail(403, 'Vous ne pouvez pas modifier ce profil.');
            return null;
        }

        return $profileId;
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

    private function uploadedAvatar(): ?string
    {
        $file = $_FILES['avatar_file'] ?? null;
        if (!is_array($file)) {
            return null;
        }

        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        if ($error === UPLOAD_ERR_NO_FILE) {
            return null;
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new InvalidArgumentException('Le téléversement de l\'image a échoué.');
        }

        $tmp = (string) ($file['tmp_name'] ?? '');
        if ($tmp === '' || !is_uploaded_file($tmp)) {
            throw new InvalidArgumentException('Fichier téléversé invalide.');
        }
        if ((int) ($file['size'] ?? 0) > self::MAX_AVATAR_BYTES) {
            throw new InvalidArgumentException('L\'image ne doit pas dépasser 2 Mo.');
        }

        $allowed = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'webp' => 'image/webp',
            'gif' => 'image/gif',
        ];
        $extension = strtolower(pathinfo((string) ($file['name'] ?? ''), PATHINFO_EXTENSION));
        if (!isset($allowed[$extension])) {
            throw new InvalidArgumentException('Format d\'image non autorisé.');
        }

        $mime = (string) (new finfo(FILEINFO_MIME_TYPE))->file($tmp);
        if ($mime !== $allowed[$extension]) {
            throw new InvalidArgumentException('Le fichier n\'est pas une image valide.');
        }

        $uploadDir = dirname(__DIR__, 2) . '/uploads';
        if (!is_dir($uploadDir) && !mkdir($uploadDir, 0755, true) && !is_dir($uploadDir)) {
            throw new RuntimeException('Impossible de créer le dossier de téléversement.');
        }

        $name = 'avatar_' . bin2hex(random_bytes(8)) . '.' . $extension;
        if (!move_uploaded_file($tmp, $uploadDir . '/' . $name)) {
            throw new RuntimeException('Impossible d\'enregistrer l\'avatar.');
        }

        return '/uploads/' . $name;
    }

    private function unauthorized(bool $wantsJson): void
    {
        if ($wantsJson) {
            $this->json(401, ['success' => false, 'error' => 'Vous devez être connecté.']);
            return;
        }

        $this->redirect('/login?error=required');
    }

    private function badRequest(string $error, bool $wantsJson, int $profileId): void
    {
        if ($wantsJson) {
            $this->json(400, ['success' => false, 'error' => $error]);
            return;
        }

        $this->redirect($this->profileUrl($profileId) . '?error=' . rawurlencode($error));
    }

    private function serverError(Throwable $e, bool $wantsJson, int $profileId): void
    {
        error_log($e->getMessage());

        if ($wantsJson) {
            $this->json(500, ['success' => false, 'error' => 'Une erreur est survenue.']);
            return;
        }

        $this->redirect($this->profileUrl($profileId) . '?error=serveur');
    }

    private function fail(int $status, string $error): void
    {
        if ($this->wantsJson()) {
            $this->json($status, ['success' => false, 'error' => $error]);
            return;
        }

        $this->html($this->renderLayout('Erreur', '<h1>' . $this->escape($error) . '</h1>'), $status);
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
            'profil' => 'Profil mis à jour avec succès.',
            'password' => 'Mot de passe modifié avec succès.',
            'required' => 'Vous devez être connecté.',
            'serveur' => 'Une erreur est survenue lors de l\'opération.',
            default => $code,
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function profileUrl(int $id): string
    {
        return '/profile/' . rawurlencode((string) $id);
    }
}

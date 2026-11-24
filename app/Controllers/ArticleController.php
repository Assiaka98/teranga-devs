<?php

declare(strict_types=1);

final class ArticleController
{
    private const DEFAULT_COVER = 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?auto=format&fit=crop&w=800&q=80';

    public function __construct(private ArticleModel $articleModel)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $articles = $this->articleModel->getAll();

        if ($this->wantsJson()) {
            $this->json(200, ['success' => true, 'data' => $articles]);
            return;
        }

        $error = $this->message(isset($_GET['error']) ? (string) $_GET['error'] : null);
        $notice = $this->message(isset($_GET['success']) ? (string) $_GET['success'] : null);

        $this->html($this->render('articles/index', [
            'pageTitle' => 'Articles & Tutoriels',
            'articles' => $articles,
            'error' => $error,
            'notice' => $notice,
            'canCreate' => $this->userId() !== null,
        ]));
    }

    public function show(string $slug): void
    {
        $article = ctype_digit($slug)
            ? $this->articleModel->findById((int) $slug)
            : $this->articleModel->findBySlug($slug);

        if ($article === null) {
            $this->fail(404, 'Article introuvable.');
            return;
        }

        if ($this->wantsJson()) {
            $this->json(200, ['success' => true, 'data' => $article]);
            return;
        }

        $articleId = (int) ($article['id'] ?? 0);
        $userId = $this->userId();

        $this->html($this->render('articles/show', [
            'pageTitle' => (string) ($article['titre'] ?? 'Article'),
            'article' => $article,
            'isLoggedIn' => $userId !== null,
            'canManage' => $userId !== null && $userId === (int) ($article['user_id'] ?? 0),
            'hasVoted' => $userId !== null && $this->articleModel->hasVoted($articleId, $userId),
        ]));
    }

    public function create(): void
    {
        if ($this->userId() === null) {
            $this->unauthorized(false);
            return;
        }

        $this->html($this->render('articles/create', [
            'pageTitle' => 'Rédiger un article',
            'article' => [],
            'action' => $this->listUrl(),
            'isEdit' => false,
        ]));
    }

    public function edit(string $id): void
    {
        $articleId = filter_var($id, FILTER_VALIDATE_INT);
        if ($articleId === false) {
            $this->fail(400, 'Identifiant invalide.');
            return;
        }

        $userId = $this->userId();
        if ($userId === null) {
            $this->unauthorized(false);
            return;
        }

        $article = $this->articleModel->findById($articleId);
        if ($article === null) {
            $this->fail(404, 'Article introuvable.');
            return;
        }
        if ((int) $article['user_id'] !== $userId) {
            $this->fail(403, 'Vous ne pouvez pas modifier cet article.');
            return;
        }

        $this->html($this->render('articles/create', [
            'pageTitle' => 'Modifier l\'article',
            'article' => $article,
            'action' => $this->articleUrl((string) $articleId) . '/edit',
            'isEdit' => true,
        ]));
    }

    public function store(): void
    {
        $wantsJson = $this->wantsJson();
        $userId = $this->userId();

        if ($userId === null) {
            $this->unauthorized($wantsJson);
            return;
        }

        try {
            [$titre, $contenu, $extrait] = $this->validateInput($this->requestData());
            $cover = $this->uploadedCover() ?? self::DEFAULT_COVER;
            $slug = $this->uniqueSlug($titre);
            $article = $this->articleModel->create($userId, $titre, $slug, $extrait, $contenu, $cover);

            if ($wantsJson) {
                $this->json(201, [
                    'success' => true,
                    'message' => 'Article publié avec succès.',
                    'data' => $article,
                ]);
                return;
            }

            $this->redirect($this->articleUrl((string) ($article['slug'] ?? $article['id'])));
        } catch (InvalidArgumentException $e) {
            $this->badRequest($e->getMessage(), $wantsJson);
        } catch (Throwable $e) {
            $this->serverError($e, $wantsJson);
        }
    }

    public function update(string $id): void
    {
        $articleId = filter_var($id, FILTER_VALIDATE_INT);
        if ($articleId === false) {
            $this->fail(400, 'Identifiant invalide.');
            return;
        }

        $wantsJson = $this->wantsJson();
        $userId = $this->userId();

        if ($userId === null) {
            $this->unauthorized($wantsJson);
            return;
        }

        $article = $this->articleModel->findById($articleId);
        if ($article === null) {
            $this->fail(404, 'Article introuvable.');
            return;
        }
        if ((int) $article['user_id'] !== $userId) {
            $this->fail(403, 'Vous ne pouvez pas modifier cet article.');
            return;
        }

        try {
            [$titre, $contenu, $extrait] = $this->validateInput($this->requestData());
            $cover = $this->uploadedCover();
            $slug = $this->uniqueSlug($titre, $article['slug'] ?? null);
            $this->articleModel->update($articleId, $userId, $titre, $slug, $extrait, $contenu, $cover);

            if ($wantsJson) {
                $this->json(200, [
                    'success' => true,
                    'message' => 'Article mis à jour avec succès.',
                    'data' => $this->articleModel->findById($articleId),
                ]);
                return;
            }

            $this->redirect($this->articleUrl($slug));
        } catch (InvalidArgumentException $e) {
            $this->badRequest($e->getMessage(), $wantsJson);
        } catch (Throwable $e) {
            $this->serverError($e, $wantsJson);
        }
    }

    public function destroy(string $id): void
    {
        $articleId = filter_var($id, FILTER_VALIDATE_INT);
        if ($articleId === false) {
            $this->fail(400, 'Identifiant invalide.');
            return;
        }

        $wantsJson = $this->wantsJson();
        $userId = $this->userId();

        if ($userId === null) {
            $this->unauthorized($wantsJson);
            return;
        }

        $article = $this->articleModel->findById($articleId);
        if ($article === null) {
            $this->fail(404, 'Article introuvable.');
            return;
        }
        if ((int) $article['user_id'] !== $userId) {
            $this->fail(403, 'Vous ne pouvez pas supprimer cet article.');
            return;
        }

        $this->articleModel->delete($articleId, $userId);

        if ($wantsJson) {
            $this->json(200, ['success' => true, 'message' => 'Article supprimé avec succès.']);
            return;
        }

        $this->redirect($this->listUrl() . '?success=supprime');
    }

    public function vote(string $id): void
    {
        $articleId = filter_var($id, FILTER_VALIDATE_INT);
        if ($articleId === false) {
            $this->fail(400, 'Identifiant invalide.');
            return;
        }

        $wantsJson = $this->wantsJson();
        $userId = $this->userId();

        if ($userId === null) {
            $this->unauthorized($wantsJson);
            return;
        }

        $article = $this->articleModel->findById($articleId);
        if ($article === null) {
            $this->fail(404, 'Article introuvable.');
            return;
        }

        if (!$this->articleModel->vote($articleId, $userId, (int) $article['user_id'])) {
            if ($wantsJson) {
                $this->json(409, ['success' => false, 'error' => 'Vote impossible pour cet article.']);
                return;
            }
            $this->redirect($this->articleUrl((string) ($article['slug'] ?? $articleId)) . '?error=already_voted');
            return;
        }

        if ($wantsJson) {
            $updated = $this->articleModel->findById($articleId);
            $this->json(200, [
                'success' => true,
                'message' => 'Vote enregistré.',
                'votes' => (int) ($updated['votes'] ?? 0),
            ]);
            return;
        }

        $this->redirect($this->articleUrl((string) ($article['slug'] ?? $articleId)) . '?success=vote');
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

    private function validateInput(array $data): array
    {
        $titre = trim((string) ($data['titre'] ?? ''));
        $contenu = trim((string) ($data['contenu'] ?? ''));
        $extrait = trim((string) ($data['extrait'] ?? ''));

        if ($titre === '' || $contenu === '') {
            throw new InvalidArgumentException('Le titre et le contenu sont requis.');
        }

        if ($extrait === '') {
            $extrait = mb_substr(strip_tags($contenu), 0, 150) . '...';
        }

        return [$titre, $contenu, $extrait];
    }

    private function uniqueSlug(string $titre, ?string $currentSlug = null): string
    {
        $base = $this->slugify($titre);
        $slug = $base;
        $suffix = 2;

        while ($slug !== $currentSlug && $this->articleModel->slugExists($slug)) {
            $slug = $base . '-' . $suffix++;
        }

        return $slug;
    }

    private function slugify(string $text): string
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = (string) iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);

        return $text === '' ? 'article' : $text;
    }

    private function uploadedCover(): ?string
    {
        $file = $_FILES['couverture_file'] ?? null;
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

        $name = 'cover_' . bin2hex(random_bytes(8)) . '.' . $extension;
        if (!move_uploaded_file($tmp, $uploadDir . '/' . $name)) {
            throw new RuntimeException('Impossible d\'enregistrer l\'image de couverture.');
        }

        return '/uploads/' . $name;
    }

    private function unauthorized(bool $wantsJson): void
    {
        if ($wantsJson) {
            $this->json(401, ['success' => false, 'error' => 'Vous devez être connecté.']);
            return;
        }

        $this->redirect($this->loginUrl());
    }

    private function badRequest(string $error, bool $wantsJson): void
    {
        if ($wantsJson) {
            $this->json(400, ['success' => false, 'error' => $error]);
            return;
        }

        $this->redirect($this->listUrl() . '?error=' . rawurlencode($error));
    }

    private function serverError(Throwable $e, bool $wantsJson): void
    {
        error_log($e->getMessage());

        if ($wantsJson) {
            $this->json(500, ['success' => false, 'error' => 'Une erreur est survenue.']);
            return;
        }

        $this->redirect($this->listUrl() . '?error=serveur');
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
        $data['urls'] = $this->urls();
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
            'articles' => $this->listUrl(),
            'questions' => '/questions',
            'login' => '/login',
            'register' => '/register',
            'logout' => '/logout',
            'profile' => '/profile',
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
            '1', 'publie' => 'Article publié avec succès.',
            'modifie' => 'Article mis à jour avec succès.',
            'supprime' => 'Article supprimé avec succès.',
            'vote' => 'Vote enregistré.',
            'serveur' => 'Une erreur est survenue lors de l\'opération.',
            default => $code,
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function listUrl(): string
    {
        return '/articles';
    }

    private function articleUrl(string $slug): string
    {
        return $this->listUrl() . '/' . rawurlencode($slug);
    }

    private function loginUrl(): string
    {
        return '/login?error=required';
    }
}

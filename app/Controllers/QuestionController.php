<?php

declare(strict_types=1);

final class QuestionController
{
    public function __construct(private QuestionModel $questionModel)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $search = isset($_GET['q']) ? trim((string) $_GET['q']) : '';
        $tag = isset($_GET['tag']) ? trim((string) $_GET['tag']) : '';

        if ($tag !== '') {
            $questions = $this->questionModel->findByTag($tag);
        } elseif ($search !== '') {
            $questions = $this->questionModel->search($search);
        } else {
            $questions = $this->questionModel->getAll();
        }

        if ($this->wantsJson()) {
            $this->json(200, [
                'success' => true,
                'data' => $questions,
                'popularTags' => $this->questionModel->getPopularTags(),
            ]);
            return;
        }

        $error = $this->message(isset($_GET['error']) ? (string) $_GET['error'] : null);
        $notice = isset($_GET['success']) ? 'Question publiée avec succès.' : null;
        $this->html($this->renderList($questions, $error, $notice, $search, $tag));
    }

    public function show(string $id): void
    {
        $questionId = filter_var($id, FILTER_VALIDATE_INT);
        if ($questionId === false) {
            $this->fail(400, 'Identifiant invalide.');
            return;
        }

        $question = $this->questionModel->findById($questionId);
        if ($question === null) {
            $this->fail(404, 'Question introuvable.');
            return;
        }

        if ($this->wantsJson()) {
            $this->json(200, ['success' => true, 'data' => $question]);
            return;
        }

        $this->html($this->renderDetail($question));
    }

    public function store(): void
    {
        $wantsJson = $this->wantsJson();

        if (!isset($_SESSION['user_id'])) {
            if ($wantsJson) {
                $this->json(401, ['success' => false, 'error' => 'Vous devez être connecté pour publier.']);
                return;
            }
            $this->redirect($this->loginUrl());
            return;
        }

        $data = $this->requestData();
        $titre = trim((string) ($data['titre'] ?? ''));
        $contenu = trim((string) ($data['contenu'] ?? ''));
        $tags = trim((string) ($data['tags'] ?? ''));

        if ($titre === '' || $contenu === '') {
            if ($wantsJson) {
                $this->json(400, ['success' => false, 'error' => 'Données incomplètes (titre et contenu requis).']);
                return;
            }
            $this->redirect($this->listUrl() . '?error=champs_incomplets');
            return;
        }

        try {
            $question = $this->questionModel->create((int) $_SESSION['user_id'], $titre, $contenu, $tags);

            if ($wantsJson) {
                $this->json(201, [
                    'success' => true,
                    'message' => 'Question publiée avec succès !',
                    'data' => $question,
                ]);
                return;
            }

            $this->redirect($this->listUrl() . '?success=1');
        } catch (Throwable $e) {
            error_log($e->getMessage());

            if ($wantsJson) {
                $this->json(500, ['success' => false, 'error' => $e->getMessage()]);
                return;
            }

            $this->redirect($this->listUrl() . '?error=serveur');
        }
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

    private function fail(int $status, string $error): void
    {
        if ($this->wantsJson()) {
            $this->json($status, ['success' => false, 'error' => $error]);
            return;
        }

        http_response_code($status);
        $this->html($this->layout('Erreur', '<main class="max-w-4xl mx-auto px-4 py-8"><div class="bg-slate-900 border border-slate-800 rounded-xl p-8 text-slate-100"><h1 class="text-2xl font-bold text-amber-400">' . $this->escape($error) . '</h1></div></main>'));
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

    private function renderList(array $questions, ?string $error, ?string $notice, string $search, string $tag): string
    {
        foreach ($questions as &$question) {
            $question['url'] = $this->questionUrl((string) ($question['id'] ?? ''));
        }
        unset($question);

        return $this->layout('Questions', $this->view('questions/index', [
            'questions' => $questions,
            'popularTags' => $this->questionModel->getPopularTags(),
            'search' => $search,
            'tag' => $tag,
            'error' => $error,
            'notice' => $notice,
            'isLoggedIn' => isset($_SESSION['user_id']),
        ]));
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

    private function renderDetail(array $question): string
    {
        $titre = (string) ($question['titre'] ?? '');
        $contenu = (string) ($question['contenu'] ?? '');
        $tags = (string) ($question['tags'] ?? '');
        $auteur = trim((string) ($question['prenom'] ?? '') . ' ' . (string) ($question['nom'] ?? ''));
        $date = (string) ($question['date_creation'] ?? '');

        $body = '<main class="max-w-4xl mx-auto px-4 py-8 space-y-6 w-full"><article class="bg-slate-900 border border-slate-800 rounded-2xl p-6 sm:p-8 space-y-4 shadow-xl">'
            . '<h1 class="text-2xl sm:text-3xl font-bold text-slate-100">' . $this->escape($titre) . '</h1>'
            . '<p class="text-xs text-slate-400">' . $this->escape($auteur) . ' — ' . $this->escape($date) . '</p>'
            . '<p class="text-slate-300 text-sm leading-relaxed">' . nl2br($this->escape($contenu)) . '</p>'
            . '<p class="text-xs text-amber-400">' . $this->escape($tags) . '</p>'
            . '<p><a href="' . $this->escape($this->listUrl()) . '" class="text-xs bg-slate-800 hover:bg-slate-700 text-slate-300 px-4 py-2 rounded-lg transition inline-block">&larr; Retour</a></p>'
            . '</article></main>';

        return $this->layout($titre, $body);
    }

    private function layout(string $title, string $body): string
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

    private function urls(): array
    {
        return [
            'base' => '/',
            'articles' => '/articles',
            'questions' => $this->listUrl(),
            'questionBase' => $this->listUrl() . '/',
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
            'champs_incomplets' => 'Le titre et le contenu sont requis.',
            'unauthorized' => 'Vous devez être connecté.',
            'serveur' => 'Une erreur est survenue lors de la publication.',
            default => $code,
        };
    }

    private function escape(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    private function listUrl(): string
    {
        return '/questions';
    }

    private function questionUrl(string $id): string
    {
        return $this->listUrl() . '/' . rawurlencode($id);
    }

    private function loginUrl(): string
    {
        return '/login?error=required';
    }
}

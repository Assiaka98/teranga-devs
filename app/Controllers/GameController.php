<?php

declare(strict_types=1);

final class GameController
{
    public function __construct()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function index(): void
    {
        $this->html($this->render('game/index', [
            'pageTitle' => 'Pause Détente : 3 Pions - Teranga Devs',
            'title' => 'Pause Détente : Jeu des 3 Pions 🇸🇳',
            'description' => "Défie l'adversaire ou l'IA directement depuis Teranga Devs entre deux lignes de code !",
            'iframeSrc' => 'https://3-pions.netlify.app/',
            'iframeTitle' => 'Jeu 3 Pions',
        ]));
    }

    private function html(string $content): void
    {
        http_response_code(200);
        header('Content-Type: text/html; charset=utf-8');
        echo $content;
    }

    private function render(string $view, array $data): string
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
            'articles' => '/articles',
            'questions' => '/questions',
            'login' => '/login',
            'register' => '/register',
            'logout' => '/logout',
            'profile' => '/profile',
            'game' => '/game',
            'mainJs' => '/assets/js/main.js',
        ];
    }
}

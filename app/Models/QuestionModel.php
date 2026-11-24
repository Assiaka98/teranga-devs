<?php

declare(strict_types=1);

final class QuestionModel
{
    private const QUESTION_COLUMNS = 'q.id, q.user_id, q.titre, q.contenu, q.tags, q.date_creation, u.nom, u.prenom';

    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        return $this->fetchQuestions();
    }

    public function search(string $search): array
    {
        $needle = '%' . trim($search) . '%';

        return $this->fetchQuestions(
            'WHERE q.titre LIKE :needle OR q.contenu LIKE :needle_content OR q.tags LIKE :needle_tags',
            [
                'needle' => $needle,
                'needle_content' => $needle,
                'needle_tags' => $needle,
            ]
        );
    }

    public function findByTag(string $tag): array
    {
        return $this->fetchQuestions(
            "WHERE FIND_IN_SET(:tag, REPLACE(COALESCE(q.tags, ''), ' ', '')) > 0",
            ['tag' => trim($tag)]
        );
    }

    public function getPopularTags(int $limit = 12): array
    {
        $stmt = $this->pdo->prepare("SELECT tags FROM questions WHERE tags IS NOT NULL AND tags <> :empty");
        $stmt->execute(['empty' => '']);

        $counts = [];
        while (($rawTags = $stmt->fetchColumn()) !== false) {
            foreach (explode(',', (string) $rawTags) as $tag) {
                $tag = trim($tag);
                if ($tag === '') {
                    continue;
                }
                $key = mb_strtolower($tag);
                if (!isset($counts[$key])) {
                    $counts[$key] = ['tag' => $tag, 'total' => 0];
                }
                $counts[$key]['total']++;
            }
        }

        uasort($counts, static function (array $a, array $b): int {
            return ($b['total'] <=> $a['total']) ?: strcasecmp($a['tag'], $b['tag']);
        });

        return array_slice(array_values($counts), 0, max(1, $limit));
    }

    private function fetchQuestions(string $where = '', array $params = []): array
    {
        $sql = 'SELECT ' . self::QUESTION_COLUMNS . '
                FROM questions q
                INNER JOIN users u ON u.id = q.user_id
                ' . $where . '
                ORDER BY q.date_creation DESC';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT q.id, q.user_id, q.titre, q.contenu, q.tags, q.date_creation, u.nom, u.prenom
             FROM questions q
             INNER JOIN users u ON u.id = q.user_id
             WHERE q.id = :id'
        );
        $stmt->execute(['id' => $id]);

        $question = $stmt->fetch(PDO::FETCH_ASSOC);

        return $question === false ? null : $question;
    }

    public function create(int $userId, string $titre, string $contenu, string $tags): array
    {
        $now = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO questions (user_id, titre, contenu, tags, date_creation)
             VALUES (:user_id, :titre, :contenu, :tags, :date_creation)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'titre' => $titre,
            'contenu' => $contenu,
            'tags' => $tags,
            'date_creation' => $now,
        ]);

        $id = (int) $this->pdo->lastInsertId();
        $question = $this->findById($id);

        return $question ?? [
            'id' => $id,
            'user_id' => $userId,
            'titre' => $titre,
            'contenu' => $contenu,
            'tags' => $tags,
            'date_creation' => $now,
        ];
    }
}

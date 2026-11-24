<?php

declare(strict_types=1);

final class ArticleModel
{
    private const ARTICLE_COLUMNS = 'a.id, a.user_id, a.titre, a.slug, a.extrait, a.contenu, a.couverture,
        a.votes, a.date_publication, a.date_creation, u.nom, u.prenom, u.reputation';

    public function __construct(private PDO $pdo)
    {
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::ARTICLE_COLUMNS . '
             FROM articles a
             INNER JOIN users u ON u.id = a.user_id
             ORDER BY a.date_publication DESC, a.id DESC'
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::ARTICLE_COLUMNS . '
             FROM articles a
             INNER JOIN users u ON u.id = a.user_id
             WHERE a.id = :id'
        );
        $stmt->execute(['id' => $id]);

        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        return $article === false ? null : $article;
    }

    public function findBySlug(string $slug): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::ARTICLE_COLUMNS . '
             FROM articles a
             INNER JOIN users u ON u.id = a.user_id
             WHERE a.slug = :slug'
        );
        $stmt->execute(['slug' => $slug]);

        $article = $stmt->fetch(PDO::FETCH_ASSOC);

        return $article === false ? null : $article;
    }

    public function slugExists(string $slug): bool
    {
        $stmt = $this->pdo->prepare('SELECT 1 FROM articles WHERE slug = :slug');
        $stmt->execute(['slug' => $slug]);

        return $stmt->fetchColumn() !== false;
    }

    public function create(
        int $userId,
        string $titre,
        string $slug,
        string $extrait,
        string $contenu,
        string $couverture
    ): array {
        $datePublication = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare(
            'INSERT INTO articles (user_id, titre, slug, extrait, contenu, couverture, date_publication)
             VALUES (:user_id, :titre, :slug, :extrait, :contenu, :couverture, :date_publication)'
        );
        $stmt->execute([
            'user_id' => $userId,
            'titre' => $titre,
            'slug' => $slug,
            'extrait' => $extrait,
            'contenu' => $contenu,
            'couverture' => $couverture,
            'date_publication' => $datePublication,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [
            'id' => $id,
            'user_id' => $userId,
            'titre' => $titre,
            'slug' => $slug,
            'extrait' => $extrait,
            'contenu' => $contenu,
            'couverture' => $couverture,
            'votes' => 0,
            'date_publication' => $datePublication,
        ];
    }

    public function update(
        int $id,
        int $userId,
        string $titre,
        string $slug,
        string $extrait,
        string $contenu,
        ?string $couverture
    ): bool {
        $sql = 'UPDATE articles
                SET titre = :titre, slug = :slug, extrait = :extrait, contenu = :contenu';

        $params = [
            'id' => $id,
            'user_id' => $userId,
            'titre' => $titre,
            'slug' => $slug,
            'extrait' => $extrait,
            'contenu' => $contenu,
        ];

        if ($couverture !== null) {
            $sql .= ', couverture = :couverture';
            $params['couverture'] = $couverture;
        }

        $sql .= ' WHERE id = :id AND user_id = :user_id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function delete(int $id, int $userId): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM articles WHERE id = :id AND user_id = :user_id');
        $stmt->execute(['id' => $id, 'user_id' => $userId]);

        return $stmt->rowCount() > 0;
    }

    public function hasVoted(int $articleId, int $userId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT 1 FROM article_votes WHERE article_id = :article_id AND user_id = :user_id'
        );
        $stmt->execute(['article_id' => $articleId, 'user_id' => $userId]);

        return $stmt->fetchColumn() !== false;
    }

    public function vote(int $articleId, int $userId, int $authorId): bool
    {
        if ($articleId === $authorId || $this->hasVoted($articleId, $userId)) {
            return false;
        }

        $this->pdo->beginTransaction();

        try {
            $stmt = $this->pdo->prepare(
                'INSERT INTO article_votes (article_id, user_id) VALUES (:article_id, :user_id)'
            );
            $stmt->execute(['article_id' => $articleId, 'user_id' => $userId]);

            $stmt = $this->pdo->prepare('UPDATE articles SET votes = votes + 1 WHERE id = :id');
            $stmt->execute(['id' => $articleId]);

            $stmt = $this->pdo->prepare('UPDATE users SET karma = karma + 10 WHERE id = :id');
            $stmt->execute(['id' => $authorId]);

            $this->pdo->commit();

            return true;
        } catch (Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }

            if ($e instanceof PDOException && $e->getCode() === '23000') {
                return false;
            }

            throw $e;
        }
    }
}

<?php

declare(strict_types=1);

final class UserModel
{
    private const USER_COLUMNS = 'id, nom, prenom, email, karma, reputation, titre_bio, avatar, date_creation';

    public function __construct(private PDO $pdo)
    {
    }

    public function findByEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::USER_COLUMNS . ', mot_de_passe
             FROM users
             WHERE email = :email'
        );
        $stmt->execute(['email' => $email]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT ' . self::USER_COLUMNS . '
             FROM users
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user === false ? null : $user;
    }

    public function create(string $nom, string $email, string $passwordHash, ?string $titreBio = null): array
    {
        $parts = preg_split('/\s+/u', trim($nom)) ?: [];
        $prenom = (string) array_shift($parts);
        $nomFamille = trim(implode(' ', $parts));
        if ($prenom === '') {
            $prenom = trim($nom);
        }
        if ($nomFamille === '') {
            $nomFamille = $prenom;
        }

        $stmt = $this->pdo->prepare(
            'INSERT INTO users (nom, prenom, email, mot_de_passe, titre_bio)
             VALUES (:nom, :prenom, :email, :mot_de_passe, :titre_bio)'
        );
        $stmt->execute([
            'nom' => $nomFamille,
            'prenom' => $prenom,
            'email' => $email,
            'mot_de_passe' => $passwordHash,
            'titre_bio' => $titreBio,
        ]);

        $id = (int) $this->pdo->lastInsertId();

        return $this->findById($id) ?? [
            'id' => $id,
            'nom' => $nomFamille,
            'prenom' => $prenom,
            'email' => $email,
            'karma' => 0,
            'reputation' => 0,
            'titre_bio' => $titreBio,
            'avatar' => null,
        ];
    }

    public function updateProfile(int $id, string $nom, ?string $titreBio, ?string $avatarPath = null): bool
    {
        $sql = 'UPDATE users SET nom = :nom';
        $params = ['id' => $id, 'nom' => $nom];

        if ($titreBio !== null) {
            $sql .= ', titre_bio = :titre_bio';
            $params['titre_bio'] = $titreBio;
        }
        if ($avatarPath !== null) {
            $sql .= ', avatar = :avatar';
            $params['avatar'] = $avatarPath;
        }

        $sql .= ' WHERE id = :id';
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount() > 0;
    }

    public function updateBio(int $id, string $titreBio): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET titre_bio = :titre_bio WHERE id = :id');
        $stmt->execute(['id' => $id, 'titre_bio' => $titreBio]);

        return $stmt->rowCount() > 0;
    }

    public function updateAvatar(int $id, ?string $avatarPath): bool
    {
        if ($avatarPath === null) {
            return false;
        }

        $stmt = $this->pdo->prepare('UPDATE users SET avatar = :avatar WHERE id = :id');
        $stmt->execute(['id' => $id, 'avatar' => $avatarPath]);

        return $stmt->rowCount() > 0;
    }

    public function updatePassword(int $id, string $hash): bool
    {
        $stmt = $this->pdo->prepare('UPDATE users SET mot_de_passe = :hash WHERE id = :id');
        $stmt->execute(['id' => $id, 'hash' => $hash]);

        return $stmt->rowCount() > 0;
    }

    public function findPasswordHash(int $id): ?string
    {
        $stmt = $this->pdo->prepare('SELECT mot_de_passe FROM users WHERE id = :id');
        $stmt->execute(['id' => $id]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row === false ? null : (string) ($row['mot_de_passe'] ?? '');
    }

    public function getArticlesByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, titre, slug, extrait, couverture, votes, date_publication
             FROM articles
             WHERE user_id = :user_id
             ORDER BY date_publication DESC, id DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getQuestionsByUser(int $userId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, titre, contenu, tags, date_creation
             FROM questions
             WHERE user_id = :user_id
             ORDER BY date_creation DESC, id DESC'
        );
        $stmt->execute(['user_id' => $userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

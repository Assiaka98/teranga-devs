CREATE DATABASE IF NOT EXISTS teranga_devs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE teranga_devs;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    reputation INT DEFAULT 0,
    karma INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Table des articles (essentielle pour le CRUD d'articles)
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    votes INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_article_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des votes sur les articles (pour empêcher les votes multiples)
CREATE TABLE IF NOT EXISTS article_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_article_vote (article_id, user_id),
    CONSTRAINT fk_vote_article FOREIGN KEY (article_id) REFERENCES articles(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_article_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des questions (Q&A)
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    tags VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_question_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des réponses aux questions
CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NOT NULL,
    contenu TEXT NOT NULL,
    votes INT DEFAULT 0,
    est_solution TINYINT(1) DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_response_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_response_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Table des votes sur les réponses
CREATE TABLE IF NOT EXISTS response_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_response_vote (response_id, user_id),
    CONSTRAINT fk_vote_response FOREIGN KEY (response_id) REFERENCES responses(id) ON DELETE CASCADE,
    CONSTRAINT fk_vote_response_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Données de test initiales
INSERT INTO users (nom, prenom, email, mot_de_passe, reputation, karma) 
VALUES ('Traore', 'Issiaka', 'isacadel98@gmail.com', '$2y$10$exemple', 150, 150);

INSERT INTO articles (user_id, titre, contenu, votes) 
VALUES (1, 'Modélisation du jeu 3 Pions en PHP', 'Les jeux de stratégie traditionnels de nos quartiers possèdent des mécaniques mathématiques et géométriques fascinantes adaptées au web.', 1);

INSERT INTO questions (user_id, titre, contenu, tags) 
VALUES (1, 'Comment optimiser une API PHP pour du faible réseau ?', 'Des astuces pour réduire la taille des payloads JSON sous connexion 3G/4G instable ?', 'PHP,Performance,API');
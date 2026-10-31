CREATE DATABASE IF NOT EXISTS teranga_devs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE teranga_devs;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL,
    reputation INT DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    titre VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    tags VARCHAR(255) NOT NULL,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_question_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    user_id INT NOT NULL,
    contenu TEXT NOT NULL,
    est_solution TINYINT(1) DEFAULT 0,
    date_creation DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_response_question FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    CONSTRAINT fk_response_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE response_votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    response_id INT NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_vote (response_id, user_id)
);

-- Ajouter le compteur de votes aux réponses (par défaut à 0)
ALTER TABLE responses ADD COLUMN votes INT DEFAULT 0;

-- Ajouter le karma aux utilisateurs (par défaut à 0)
ALTER TABLE users ADD COLUMN karma INT DEFAULT 0;
-- Données de test
INSERT INTO users (nom, prenom, email, mot_de_passe, reputation) 
VALUES ('Traore', 'Issiaka', 'isacadel98@gmail.com', '$2y$10$exemple', 150);

INSERT INTO questions (user_id, titre, contenu, tags) 
VALUES (1, 'Comment optimiser une API PHP pour du faible réseau ?', 'Des astuces pour réduire la taille des payloads JSON sous connexion 3G/4G instable ?', 'PHP,Performance,API');
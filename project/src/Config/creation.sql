CREATE TABLE users (
    users_id INT AUTO_INCREMENT PRIMARY KEY,
    users_email VARCHAR(255) NOT NULL,
    users_username VARCHAR(100) NOT NULL,
    users_mdp VARCHAR(255) NOT NULL,
    users_date_creation DATETIME,
    users_nbVictoire INT DEFAULT 0,
    users_elo int default 200,
);

CREATE TABLE joueur (
    joueur_id INT AUTO_INCREMENT PRIMARY KEY,
    joueur_numero INT,
    joueur_nbPierre INT DEFAULT 0,
    joueur_nbFeuille INT DEFAULT 0,
    joueur_nbCiseau INT DEFAULT 0,
    users_id INT,
    FOREIGN KEY (users_id) REFERENCES users(users_id) ON DELETE CASCADE
);

CREATE TABLE partie (
    partie_id INT AUTO_INCREMENT PRIMARY KEY,
    partie_code INT,
    partie_plateau JSON,
    partie_etat VARCHAR(10) CHECK (partie_etat IN ('TERMINEE','EN COURS', 'EN ATTENTE')),
    partie_joueurActif INT,
    partie_date_creation DATETIME,
    partie_date_modif DATETIME,
    partie_joueurGagnant INT,
    partie_tdj INT,
    partie_public BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (partie_joueurGagnant) REFERENCES joueur(joueur_id) ON DELETE SET NULL
);

CREATE TABLE archive_partie (
    archive_id INT AUTO_INCREMENT PRIMARY KEY,
    partie_id INT NOT NULL,
    users_id INT NOT NULL,
    users2_id INT NOT NULL,
    username1 VARCHAR(100) NOT NULL,
    username2 VARCHAR(100) NOT NULL,
    date_creation DATETIME,
    date_fin DATETIME,
    gagnant BOOLEAN DEFAULT FALSE,
    public BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (users_id) REFERENCES users(users_id) ON DELETE CASCADE,
    FOREIGN KEY (users2_id) REFERENCES users(users_id) ON DELETE CASCADE
);

CREATE TABLE archives_tourdejeu (
    tdj_id INT AUTO_INCREMENT PRIMARY KEY,
    tdj_nTour INT NOT NULL,
    tdj_joueurActif INT NOT NULL,
    xDepart INT NOT NULL,
    yDepart INT NOT NULL,
    xArrive INT NOT NULL,
    yArrive INT NOT NULL,
    tdj_aManger BOOLEAN,
    partie_id INT,
    FOREIGN KEY (partie_id) REFERENCES partie(partie_id) ON DELETE CASCADE,
    FOREIGN KEY (tdj_joueurActif) REFERENCES joueur(joueur_id) ON DELETE CASCADE
);

CREATE TABLE salon (
    partie_id INT PRIMARY KEY,
    joueur1 INT,
    joueur2 INT,
    FOREIGN KEY (partie_id) REFERENCES partie(partie_id) ON DELETE CASCADE,
    FOREIGN KEY (joueur1) REFERENCES joueur(joueur_id) ON DELETE CASCADE,
    FOREIGN KEY (joueur2) REFERENCES joueur(joueur_id) ON DELETE CASCADE
);

CREATE TABLE tourdejeu (
    tdj_id INT AUTO_INCREMENT PRIMARY KEY,
    tdj_nTour INT NOT NULL,
    tdj_joueurActif INT NOT NULL,
    xDepart INT NOT NULL,
    yDepart INT NOT NULL,
    xArrive INT NOT NULL,
    yArrive INT NOT NULL,
    tdj_aManger BOOLEAN,
    partie_id INT,
    FOREIGN KEY (partie_id) REFERENCES partie(partie_id) ON DELETE CASCADE,
    FOREIGN KEY (tdj_joueurActif) REFERENCES joueur(joueur_id) ON DELETE CASCADE
);

CREATE TABLE codes_attente (
    code INT PRIMARY KEY,
    user_id INT NOT NULL,
    username VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_public BOOLEAN DEFAULT TRUE,
    INDEX idx_created (created_at)
);

CREATE TABLE IF NOT EXISTS rematch_invitations (
    id INT NOT NULL AUTO_INCREMENT,
    code INT(6) NOT NULL UNIQUE,
    old_partie_code INT(6) NOT NULL,
    host_user_id INT NOT NULL,
    invited_user_id INT NOT NULL,
    host_username VARCHAR(255) NOT NULL,
    invited_username VARCHAR(255) NOT NULL,
    status ENUM('EN ATTENTE', 'ACCEPTER', 'REFUSER') NOT NULL DEFAULT 'EN ATTENTE',
    created_at DATETIME NOT NULL,
    accepted_at DATETIME NULL DEFAULT NULL,
    refused_at DATETIME NULL DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_host_user (host_user_id),
    KEY idx_invited_user (invited_user_id),
    KEY idx_status (status),
    KEY idx_status_invited (status, invited_user_id),
    KEY idx_created_at (created_at),
    CONSTRAINT rematch_invitations_ibfk_1 FOREIGN KEY (host_user_id) REFERENCES users(users_id) ON DELETE CASCADE,
    CONSTRAINT rematch_invitations_ibfk_2 FOREIGN KEY (invited_user_id) REFERENCES users(users_id) ON DELETE CASCADE
);

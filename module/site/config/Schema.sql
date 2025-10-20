    -- Table des administrateurs
    CREATE TABLE IF NOT EXISTS admins (
        id INT AUTO_INCREMENT PRIMARY KEY,
        email VARCHAR(255) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        nom VARCHAR(100),
        prenom VARCHAR(100),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        INDEX idx_email (email)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Table des tokens de réinitialisation
    CREATE TABLE IF NOT EXISTS reset_tokens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        admin_id INT NOT NULL,
        token VARCHAR(255) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_admin (admin_id),
        INDEX idx_token (token),
        FOREIGN KEY (admin_id) REFERENCES admins(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Table des étudiants
    CREATE TABLE IF NOT EXISTS etudiants (
        id INT AUTO_INCREMENT PRIMARY KEY,
        numetu VARCHAR(20) UNIQUE NOT NULL,
        nom VARCHAR(100) NOT NULL,
        prenom VARCHAR(100) NOT NULL,
        email VARCHAR(255),
        telephone VARCHAR(20),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_numetu (numetu),
        INDEX idx_nom (nom, prenom)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Table des dossiers étudiants
    CREATE TABLE IF NOT EXISTS dossiers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        etudiant_id INT NOT NULL,
        statut ENUM('en_cours', 'complet', 'valide') DEFAULT 'en_cours',
        date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        date_validation TIMESTAMP NULL,
        valide_par INT NULL,
        INDEX idx_statut (statut),
        INDEX idx_etudiant (etudiant_id),
        FOREIGN KEY (etudiant_id) REFERENCES etudiants(id) ON DELETE CASCADE,
        FOREIGN KEY (valide_par) REFERENCES admins(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    -- Table des relances
    CREATE TABLE IF NOT EXISTS relances (
        id INT AUTO_INCREMENT PRIMARY KEY,
        dossier_id INT NOT NULL,
        date_relance TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        message TEXT,
        envoye_par INT,
        INDEX idx_dossier (dossier_id),
        INDEX idx_date (date_relance),
        FOREIGN KEY (dossier_id) REFERENCES dossiers(id) ON DELETE CASCADE,
        FOREIGN KEY (envoye_par) REFERENCES admins(id) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

    ALTER TABLE etudiants
    ADD COLUMN last_connexion TIMESTAMP NULL AFTER created_at,
    ADD COLUMN type_etudiant ENUM('entrant', 'sortant') NULL AFTER prenom;
<?php

namespace Model\Folder;

use Database;
use PDO;

class RelanceModel
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    /**
     * Retrieve all incomplete dossiers.
     *
     * Returns useful information for sending reminders:
     * - dossier_id: int ID of the dossier
     * - etudiant_id: int ID of the student
     * - email_responsable: string|null Responsible person's email (if any)
     * - email_etudiant: string Student's email
     * - nom: string Student's last name
     * - prenom: string Student's first name
     *
     * @return array List of incomplete dossiers
     */
    public function getIncompleteDossiers(): array
    {
        $sql = "
            SELECT d.id AS dossier_id,
                   d.etudiant_id,
                   d.email_responsable,
                   e.email AS email_etudiant,
                   e.nom, e.prenom
            FROM dossiers d
            LEFT JOIN etudiants e ON e.id = d.etudiant_id
            WHERE d.iscomplet = 0
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert a reminder (relance) for a given dossier.
     *
     * @param int $dossierId ID of the dossier
     * @param string $message Text content of the reminder
     * @param int|null $envoyePar Admin ID who sends the reminder, NULL if automatic/scripted
     * @return bool True on success, false on failure
     */
    public function insertRelance(int $dossierId, string $message, ?int $envoyePar = null): bool
    {
        $sql = "INSERT INTO relances (dossier_id, message, envoye_par) VALUES (:dossier_id, :message, :envoye_par)";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':dossier_id' => $dossierId,
            ':message' => $message,
            ':envoye_par' => $envoyePar,
        ]);
    }

    /**
     * Check if a reminder has been sent within the last X days.
     *
     * Useful to avoid sending reminders too frequently.
     *
     * @param int $dossierId ID of the dossier
     * @param int $days Number of days to check
     * @return bool True if a reminder exists in the last X days, false otherwise
     */
    public function lastRelanceWithinDays(int $dossierId, int $days): bool
    {
        $sql = "SELECT 1 FROM relances WHERE dossier_id = :dossier_id AND date_relance >= (NOW() - INTERVAL :days DAY) LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':dossier_id', $dossierId, PDO::PARAM_INT);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();
        return (bool)$stmt->fetchColumn();
    }
}

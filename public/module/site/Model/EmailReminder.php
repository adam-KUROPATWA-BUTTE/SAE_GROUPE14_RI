<?php

// phpcs:disable Generic.Files.LineLength

namespace Model;

use PDO;

/**
 * Class EmailReminder
 *
 * Handles database interactions for sending email reminders to students.
 * Located at module/site/Model/EmailReminder.php
 */
class EmailReminder
{
    private PDO $pdo;

    /**
     * Constructor.
     * Initializes the database connection.
     */
    public function __construct()
    {
        // Assuming Database class is in global namespace or available via autoloader
        $this->pdo = \Database::getInstance()->getConnection();
    }

    /**
     * Insert a reminder (relance) for a given student using their NumEtu.
     *
     * @param string   $numetu    The student identifier (NumEtu).
     * @param string   $message   Text content of the reminder.
     * @param int|null $envoyePar ID of the admin sending the reminder (optional).
     * @return bool True on success, False on failure.
     */
    public function insertRelance(string $numetu, string $message, ?int $envoyePar = null): bool
    {
        $sql = "INSERT INTO relances (numetu, message, envoye_par, date_relance) 
                VALUES (:numetu, :message, :envoye_par, NOW())";

        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([
            ':numetu'     => $numetu,
            ':message'    => $message,
            ':envoye_par' => $envoyePar,
        ]);
    }

    /**
     * Check if a reminder has been sent to this student within the last X days.
     *
     * @param string $numetu The student identifier.
     * @param int    $days   Number of days to check.
     * @return bool True if a reminder exists in the last X days.
     */
    public function lastRelanceWithinDays(string $numetu, int $days): bool
    {
        $sql = "SELECT 1 FROM relances 
                WHERE numetu = :numetu 
                AND date_relance >= (NOW() - INTERVAL :days DAY) 
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':numetu', $numetu);
        $stmt->bindValue(':days', $days, PDO::PARAM_INT);
        $stmt->execute();

        return (bool)$stmt->fetchColumn();
    }

    /**
     * Retrieve all incomplete dossiers (Optional helper).
     *
     * @return array List of incomplete dossiers.
     */
    public function getIncompleteDossiers(): array
    {
        $sql = "
            SELECT d.NumEtu, d.Nom, d.Prenom, d.EmailPersonnel
            FROM dossiers d
            WHERE d.IsComplete = 0 OR d.IsComplete IS NULL
        ";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}

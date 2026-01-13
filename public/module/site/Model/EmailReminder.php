<?php

namespace Model;

use PDO;
use PDOException;

/**
 * Class EmailReminder
 *
 * Handles database interactions related to sending email reminders to students.
 * Manages the 'relances' table and checks for incomplete folders.
 */
class EmailReminder
{
    /** @var PDO The database connection instance */
    private PDO $pdo;

    /**
     * Constructor.
     * Initializes the database connection via the Singleton.
     */
    public function __construct()
    {
        // Use global namespace for Database class
        $this->pdo = \Database::getInstance()->getConnection();
    }

    /**
     * Inserts a reminder record into the database.
     *
     * @param string   $numetu    The student identifier (NumEtu).
     * @param string   $message   The content of the reminder message.
     * @param int|null $envoyePar The ID of the admin who sent the reminder (nullable for auto-cron).
     * @return bool True on success, false on failure.
     */
    public function insertRelance(string $numetu, string $message, ?int $envoyePar = null): bool
    {
        $sql = "INSERT INTO relances (numetu, message, envoye_par, date_relance) 
                VALUES (:numetu, :message, :envoye_par, NOW())";

        try {
            $stmt = $this->pdo->prepare($sql);

            return $stmt->execute([
                ':numetu'     => $numetu,
                ':message'    => $message,
                ':envoye_par' => $envoyePar,
            ]);
        } catch (PDOException $e) {
            error_log("Error inserting reminder: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Checks if a reminder has already been sent to this student within the last X days.
     *
     * @param string $numetu The student identifier.
     * @param int    $days   The number of days to look back.
     * @return bool True if a reminder was sent recently, false otherwise.
     */
    public function lastRelanceWithinDays(string $numetu, int $days): bool
    {
        $sql = "SELECT 1 FROM relances 
                WHERE numetu = :numetu 
                AND date_relance >= (NOW() - INTERVAL :days DAY) 
                LIMIT 1";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':numetu', $numetu);
            $stmt->bindValue(':days', $days, PDO::PARAM_INT);
            $stmt->execute();

            // fetchColumn returns mixed (false if no row), cast to bool
            return (bool)$stmt->fetchColumn();
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Retrieves a list of students with incomplete dossiers.
     *
     * @return array<int, array<string, mixed>> List of incomplete dossiers.
     */
    public function getIncompleteDossiers(): array
    {
        // Select dossiers where IsComplete is 0 (False)
        $sql = "
            SELECT d.NumEtu, d.Nom, d.Prenom, d.EmailPersonnel
            FROM dossiers d
            WHERE d.IsComplete = 0
        ";

        try {
            $stmt = $this->pdo->query($sql);

            // Check if query failed
            if ($stmt === false) {
                return [];
            }

            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Check if fetchAll failed
            return is_array($result) ? $result : [];
        } catch (PDOException $e) {
            error_log("Error fetching incomplete dossiers: " . $e->getMessage());
            return [];
        }
    }
}
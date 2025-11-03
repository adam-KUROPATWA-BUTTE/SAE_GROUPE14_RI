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
     * Récupère les dossiers incomplets.
     * Renvoie des éléments utiles : id dossier, etudiant_id, email_responsable (ou email etudiant), nom/prenom.
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
     * Insère une relance dans la table relances.
     * message : texte sommaire de la relance (ex: "Relance automatique envoyée par script cron")
     * envoye_par : nullable (id de l'admin qui envoie), pass NULL si script automatique.
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
     * (Optionnel) Vous pouvez ajouter une méthode pour vérifier la dernière relance
     * afin d'éviter d'envoyer trop fréquemment (ex: vérifier relances sur les 7 derniers jours).
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
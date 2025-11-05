<?php
namespace Model\Folder;

use PDO;
use PDOException;

class FolderStudent
{
    /**
     * Connexion à la base via la classe Database
     */
    private static function getConnection(): PDO
    {
        // ✅ Vérifie que la classe Database existe bien
        if (!class_exists('\Database')) {
            throw new \RuntimeException("Classe Database introuvable. Assure-toi que Config/Database.php est chargé.");
        }

        return \Database::getInstance()->getConnection();
    }

    /**
     * Récupère les infos du dossier étudiant à partir du NumEtu
     */
    public static function getStudentDetails(string $numetu): ?array
    {
        $pdo = self::getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT 
                    NumEtu,
                    Nom,
                    Prenom,
                    DateNaissance,
                    Sexe,
                    Adresse,
                    CodePostal,
                    Ville,
                    EmailPersonnel,
                    EmailAMU,
                    Telephone,
                    CodeDepartement,
                    Type,
                    Zone,
                    IsComplete,
                    PiecesJustificatives
                FROM dossiers
                WHERE NumEtu = :numetu
                LIMIT 1
            ");
            $stmt->execute(['numetu' => $numetu]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $result['pieces'] = !empty($result['PiecesJustificatives'])
                    ? json_decode($result['PiecesJustificatives'], true)
                    : [];
            }

            return $result ?: null;
        } catch (PDOException $e) {
            error_log("Erreur récupération dossier étudiant : " . $e->getMessage());
            return null;
        }
    }

    /**
     * Méthode utilisée par le dashboard étudiant
     */
    public static function getMyFolder(int $etudiantId): ?array
    {
        // Compatibilité avec DashboardController
        return self::getStudentDetails((string)$etudiantId);
    }

    /**
     * Mise à jour du dossier étudiant (champs modifiables)
     */
    public static function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null): bool
    {
        $pdo = self::getConnection();

        try {
            // Récupérer les anciennes pièces
            $stmt = $pdo->prepare("SELECT PiecesJustificatives FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute(['numetu' => $data['NumEtu']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            $pieces = $existing && !empty($existing['PiecesJustificatives'])
                ? json_decode($existing['PiecesJustificatives'], true)
                : [];

            // Mise à jour des fichiers
            if ($photoData !== null) {
                $pieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $pieces['cv'] = base64_encode($cvData);
            }

            $piecesJson = json_encode($pieces);

            // Mise à jour SQL
            $stmt = $pdo->prepare("
                UPDATE dossiers
                SET 
                    Adresse = :Adresse,
                    CodePostal = :CodePostal,
                    Ville = :Ville,
                    Telephone = :Telephone,
                    EmailPersonnel = :EmailPersonnel,
                    PiecesJustificatives = :PiecesJustificatives
                WHERE NumEtu = :NumEtu
            ");

            return $stmt->execute([
                'NumEtu' => $data['NumEtu'],
                'Adresse' => $data['Adresse'] ?? '',
                'CodePostal' => $data['CodePostal'] ?? '',
                'Ville' => $data['Ville'] ?? '',
                'Telephone' => $data['Telephone'] ?? '',
                'EmailPersonnel' => $data['EmailPersonnel'] ?? '',
                'PiecesJustificatives' => $piecesJson
            ]);
        } catch (PDOException $e) {
            error_log("Erreur mise à jour dossier étudiant : " . $e->getMessage());
            return false;
        }
    }
}

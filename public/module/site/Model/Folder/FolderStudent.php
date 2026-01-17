<?php

namespace Model\Folder;

use PDO;
use PDOException;
use RuntimeException;
use DateTime;

class FolderStudent
{
    /**
     * @return PDO
     */
    private static function getConnection(): PDO
    {
        if (!class_exists('\Database')) {
            throw new RuntimeException('Database class not found.');
        }

        return \Database::getInstance()->getConnection();
    }

    /**
     * @param string $numetu
     * @return array<string, mixed>|null
     */
    public static function getStudentDetails(string $numetu): ?array
    {
        try {
            $stmt = self::getConnection()->prepare(
                'SELECT * FROM dossiers WHERE NumEtu = :NumEtu LIMIT 1'
            );
            $stmt->execute(['NumEtu' => $numetu]);

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!is_array($result)) {
                return null;
            }

            $result['pieces'] = [];

            if (
                isset($result['PiecesJustificatives']) &&
                is_string($result['PiecesJustificatives'])
            ) {
                $decoded = json_decode($result['PiecesJustificatives'], true);
                if (is_array($decoded)) {
                    $result['pieces'] = $decoded;
                }
            }

            return $result;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return null;
        }
    }

    /**
     * @param int $etudiantId
     * @return array<string, mixed>|null
     */
    public static function getMyFolder(int $etudiantId): ?array
    {
        return self::getStudentDetails((string) $etudiantId);
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function createDossier(
        array $data,
        ?string $photoData = null,
        ?string $cvData = null,
        ?string $conventionData = null,
        ?string $lettreData = null
    ): bool {
        try {
            if (
                isset($data['DateNaissance']) &&
                is_string($data['DateNaissance'])
            ) {
                $date = DateTime::createFromFormat('Y-m-d', $data['DateNaissance']);
                if (!$date) {
                    $data['DateNaissance'] = null;
                }
            } else {
                $data['DateNaissance'] = null;
            }

            $pieces = [];

            if ($photoData !== null) {
                $pieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $pieces['cv'] = base64_encode($cvData);
            }
            if ($conventionData !== null) {
                $pieces['convention'] = base64_encode($conventionData);
            }
            if ($lettreData !== null) {
                $pieces['lettre_motivation'] = base64_encode($lettreData);
            }

            $stmt = self::getConnection()->prepare(
                'INSERT INTO dossiers (
                    NumEtu, Nom, Prenom, DateNaissance,
                    EmailPersonnel, Telephone, PiecesJustificatives, IsComplete
                ) VALUES (
                    :NumEtu, :Nom, :Prenom, :DateNaissance,
                    :EmailPersonnel, :Telephone, :PiecesJustificatives, 0
                )'
            );

            return $stmt->execute([
                ':NumEtu' => $data['NumEtu'],
                ':Nom' => $data['Nom'],
                ':Prenom' => $data['Prenom'],
                ':DateNaissance' => $data['DateNaissance'],
                ':EmailPersonnel' => $data['EmailPersonnel'],
                ':Telephone' => $data['Telephone'],
                ':PiecesJustificatives' => json_encode($pieces),
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public static function updateDossier(
        array $data,
        ?string $photoData = null,
        ?string $cvData = null,
        ?string $conventionData = null,
        ?string $lettreData = null
    ): bool {
        try {
            $stmt = self::getConnection()->prepare(
                'SELECT PiecesJustificatives FROM dossiers WHERE NumEtu = :NumEtu'
            );
            $stmt->execute(['NumEtu' => $data['NumEtu']]);
            $existing = $stmt->fetch(PDO::FETCH_ASSOC);

            $pieces = [];

            if (
                is_array($existing) &&
                isset($existing['PiecesJustificatives']) &&
                is_string($existing['PiecesJustificatives'])
            ) {
                $decoded = json_decode($existing['PiecesJustificatives'], true);
                if (is_array($decoded)) {
                    $pieces = $decoded;
                }
            }

            if ($photoData !== null) {
                $pieces['photo'] = base64_encode($photoData);
            }
            if ($cvData !== null) {
                $pieces['cv'] = base64_encode($cvData);
            }
            if ($conventionData !== null) {
                $pieces['convention'] = base64_encode($conventionData);
            }
            if ($lettreData !== null) {
                $pieces['lettre_motivation'] = base64_encode($lettreData);
            }

            $stmt = self::getConnection()->prepare(
                'UPDATE dossiers
                 SET EmailPersonnel = :EmailPersonnel,
                     Telephone = :Telephone,
                     Adresse = :Adresse,
                     CodePostal = :CodePostal,
                     Ville = :Ville,
                     PiecesJustificatives = :PiecesJustificatives
                 WHERE NumEtu = :NumEtu'
            );

            return $stmt->execute([
                ':NumEtu' => $data['NumEtu'],
                ':EmailPersonnel' => $data['EmailPersonnel'],
                ':Telephone' => $data['Telephone'],
                ':Adresse' => $data['Adresse'],         
                ':CodePostal' => $data['CodePostal'], 
                ':Ville' => $data['Ville'],
                ':PiecesJustificatives' => json_encode($pieces),
            ]);
        } catch (PDOException $e) {
            error_log($e->getMessage());
            return false;
        }
    }
}

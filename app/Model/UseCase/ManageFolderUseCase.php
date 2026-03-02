<?php

namespace Model\UseCase;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Model\Repository\DossierRepositoryInterface;
use Model\Persistence\DossierRepositoryPDO;

/**
 * Class ManageFolderUseCase
 * Contains the business logic for managing student folders.
 */
class ManageFolderUseCase
{
    private DossierRepositoryInterface $dossierRepo;

    public function __construct()
    {
        $this->dossierRepo = new DossierRepositoryPDO();
    }

    /**
     * Retrieves all student folders.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllFolders(): array
    {
        return $this->dossierRepo->findAll();
    }

    /**
     * Retrieves detailed information about a specific student folder.
     *
     * @param string $numetu
     * @return array<string, mixed>|null
     */
    public function getStudentDetails(string $numetu): ?array
    {
        $result = $this->dossierRepo->findByNumEtu($numetu);

        if (!$result) {
            return null;
        }

        $piecesJson = $result['PiecesJustificatives'] ?? '';
        $result['pieces'] = (is_string($piecesJson) && $piecesJson !== '')
            ? (json_decode($piecesJson, true) ?? [])
            : [];

        $statutsJson = $result['StatutDocuments'] ?? '';
        $result['statuts'] = (is_string($statutsJson) && $statutsJson !== '')
            ? (json_decode($statutsJson, true) ?? [])
            : [];

        return $result;
    }

    /**
     * @param string $numetu
     * @return array<string, mixed>|null
     */
    public function getByNumetu(string $numetu): ?array
    {
        return $this->getStudentDetails($numetu);
    }

    /**
     * @param string $numetu
     * @return bool
     */
    public function toggleCompleteStatus(string $numetu): bool
    {
        return $this->dossierRepo->toggleStatus($numetu);
    }

    /**
     * Searches and paginates folders based on criteria.
     *
     * @param array<string, mixed> $filters
     * @param int $page
     * @param int $perPage
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int}
     */
    public function rechercherAvecPagination(array $filters, int $page = 1, int $perPage = 10): array
    {
        return $this->dossierRepo->searchWithPagination($filters, $page, $perPage);
    }

    /**
     * Creates a new student folder.
     *
     * @param array<string, mixed> $data
     * @param string|null $photoData
     * @param string|null $cvData
     * @param string|null $conventionData
     * @param string|null $lettreData
     * @return bool
     */
    public function creerDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $photoData = $photoData ?? (is_string($data['photo'] ?? null) ? $data['photo'] : null);
        $cvData = $cvData ?? (is_string($data['cv'] ?? null) ? $data['cv'] : null);
        $conventionData = $conventionData ?? (is_string($data['convention'] ?? null) ? $data['convention'] : null);
        $lettreData = $lettreData ?? (is_string($data['lettre_motivation'] ?? null) ? $data['lettre_motivation'] : null);

        // Map HTML form names OR existing keys
        $rawDate = $data['naissance'] ?? ($data['DateNaissance'] ?? null);
        $dateNaissance = null;
        if (!empty($rawDate) && is_string($rawDate)) {
            $date = \DateTime::createFromFormat('Y-m-d', $rawDate);
            $dateNaissance = ($date && $date->format('Y-m-d') === $rawDate) ? $rawDate : null;
        }

        $pieces = [];
        if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
        if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
        if ($conventionData !== null) $pieces['convention'] = base64_encode($conventionData);
        if ($lettreData !== null) $pieces['lettre_motivation'] = base64_encode($lettreData);

        // Ensure empty json objects are stored as '{}' and not '[]'
        $piecesJson = empty($pieces) ? '{}' : json_encode($pieces);

        $formattedData = [
            'NumEtu' => $data['numetu'] ?? ($data['NumEtu'] ?? null),
            'Nom' => $data['nom'] ?? ($data['Nom'] ?? null),
            'Prenom' => $data['prenom'] ?? ($data['Prenom'] ?? null),
            'DateNaissance' => $dateNaissance,
            'Sexe' => $data['sexe'] ?? ($data['Sexe'] ?? null),
            'Adresse' => $data['adresse'] ?? ($data['Adresse'] ?? null),
            'CodePostal' => $data['cp'] ?? ($data['CodePostal'] ?? null),
            'Ville' => $data['ville'] ?? ($data['Ville'] ?? null),
            'EmailPersonnel' => $data['email_perso'] ?? ($data['EmailPersonnel'] ?? null),
            'EmailAMU' => $data['email_amu'] ?? ($data['EmailAMU'] ?? null),
            'Telephone' => $data['telephone'] ?? ($data['Telephone'] ?? null),
            'CodeDepartement' => $data['departement'] ?? ($data['CodeDepartement'] ?? null),
            'Type' => $data['type'] ?? ($data['Type'] ?? null),
            'Zone' => $data['zone'] ?? ($data['Zone'] ?? null),
            'PiecesJustificatives' => $piecesJson,
            'status' => $data['status'] ?? 'depot'
        ];

        // Ensure empty strings are cast to null for cleaner DB insertion
        foreach ($formattedData as $key => $value) {
            if ($value === '') $formattedData[$key] = null;
        }

        return $this->dossierRepo->create($formattedData);
    }

    /**
     * Updates an existing student folder.
     *
     * @param array<string, mixed> $data
     * @param string|null $photoData
     * @param string|null $cvData
     * @param string|null $conventionData
     * @param string|null $lettreData
     * @return bool
     */
    public function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        // Safe check for the Student ID from either the HTML form or direct array mapping
        $numEtu = strval($data['numetu'] ?? ($data['NumEtu'] ?? ''));

        if ($numEtu === '') {
            error_log("ManageFolderUseCase: Cannot update folder without a valid NumEtu.");
            return false;
        }

        $existing = $this->getStudentDetails($numEtu);
        if (!$existing) {
            error_log("ManageFolderUseCase: Student $numEtu not found for update.");
            return false;
        }

        /** @var array<string, string> $oldPieces */
        $oldPieces = isset($existing['pieces']) && is_array($existing['pieces']) ? $existing['pieces'] : [];

        $photoData = $photoData ?? (is_string($data['photo'] ?? null) ? $data['photo'] : null);
        $cvData = $cvData ?? (is_string($data['cv'] ?? null) ? $data['cv'] : null);
        $conventionData = $conventionData ?? (is_string($data['convention'] ?? null) ? $data['convention'] : null);
        $lettreData = $lettreData ?? (is_string($data['lettre_motivation'] ?? null) ? $data['lettre_motivation'] : null);

        if (!empty($photoData)) $oldPieces['photo'] = base64_encode($photoData);
        if (!empty($cvData)) $oldPieces['cv'] = base64_encode($cvData);
        if (!empty($conventionData)) $oldPieces['convention'] = base64_encode($conventionData);
        if (!empty($lettreData)) $oldPieces['lettre_motivation'] = base64_encode($lettreData);

        // Force an empty array to become `{}` in JSON instead of `[]` to prevent parsing issues
        $piecesJson = empty($oldPieces) ? '{}' : json_encode($oldPieces);

        // Date handling
        $rawDate = $data['naissance'] ?? ($data['DateNaissance'] ?? null);
        $dateNaissance = null;
        if (!empty($rawDate) && is_string($rawDate)) {
            $date = \DateTime::createFromFormat('Y-m-d', $rawDate);
            $dateNaissance = ($date && $date->format('Y-m-d') === $rawDate) ? $rawDate : null;
        }

        // Map HTML form names (lowercase keys) to Database expectations (CamelCase keys)
        $formattedData = [
            ':Nom' => $data['nom'] ?? ($data['Nom'] ?? null),
            ':Prenom' => $data['prenom'] ?? ($data['Prenom'] ?? null),
            ':DateNaissance' => $dateNaissance,
            ':Sexe' => $data['sexe'] ?? ($data['Sexe'] ?? null),
            ':Adresse' => $data['adresse'] ?? ($data['Adresse'] ?? null),
            ':CodePostal' => $data['cp'] ?? ($data['CodePostal'] ?? null),
            ':Ville' => $data['ville'] ?? ($data['Ville'] ?? null),
            ':EmailPersonnel' => $data['email_perso'] ?? ($data['EmailPersonnel'] ?? null),
            ':EmailAMU' => $data['email_amu'] ?? ($data['EmailAMU'] ?? null),
            ':Telephone' => $data['telephone'] ?? ($data['Telephone'] ?? null),
            ':CodeDepartement' => $data['departement'] ?? ($data['CodeDepartement'] ?? null),
            ':Type' => $data['type'] ?? ($data['Type'] ?? null),
            ':Zone' => $data['zone'] ?? ($data['Zone'] ?? null),
            ':PiecesJustificatives' => $piecesJson,
            ':status' => $existing['status'] ?? 'depot'
        ];

        return $this->dossierRepo->update($numEtu, $formattedData);
    }

    /**
     * Reads an Excel or CSV file and imports multiple folders into the database.
     *
     * @param string $filePath The temporary path of the uploaded file.
     * @return bool
     */
    public function importFoldersFromCSV(string $filePath): bool
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();

            $rows = $worksheet->toArray();
            array_shift($rows);

            $dossiersToInsert = [];

            foreach ($rows as $data) {
                if (!is_array($data) || empty(trim(strval($data[0] ?? '')))) {
                    continue;
                }

                $dossiersToInsert[] = [
                    'NumEtu'    => trim(strval($data[0] ?? '')),
                    'Nom'       => trim(strval($data[1] ?? '')),
                    'Prenom'    => trim(strval($data[2] ?? '')),
                    'EmailPersonnel' => trim(strval($data[3] ?? '')),
                    'Telephone' => trim(strval($data[4] ?? '')),
                    'Type'      => strtolower(trim(strval($data[5] ?? 'sortant'))),
                    'Zone'      => strtolower(trim(strval($data[6] ?? 'europe')))
                ];
            }

            $lignesInserees = $this->dossierRepo->upsertMultiple($dossiersToInsert);

            return $lignesInserees > 0;

        } catch (\Exception $e) {
            error_log("Import Error (PhpSpreadsheet/UseCase): " . $e->getMessage());
            return false;
        }
    }

    public function cycleFolderStatus(string $numEtu): bool
    {
        return $this->dossierRepo->cycleStatus($numEtu);
    }

    /**
     * Analyse les documents manquants d'un dossier
     *
     * @param string $numetu
     * @return array{manquants: array<int, string>, presents: array<int, string>, statuts: array<string, string>}
     */
    public function analyserDocuments(string $numetu): array
    {
        return $this->dossierRepo->analyserDocuments($numetu);
    }

    /**
     * Enregistre la validation des documents
     *
     * @param string $numetu
     * @param array<string, string> $statutsDocuments
     * @param string|null $dateLimite
     * @param string|null $commentaire
     * @return bool
     */
    public function enregistrerValidation(
        string $numetu,
        array $statutsDocuments,
        ?string $dateLimite = null,
        ?string $commentaire = null
    ): bool {
        return $this->dossierRepo->enregistrerValidation(
            $numetu,
            $statutsDocuments,
            $dateLimite,
            $commentaire
        );
    }

}
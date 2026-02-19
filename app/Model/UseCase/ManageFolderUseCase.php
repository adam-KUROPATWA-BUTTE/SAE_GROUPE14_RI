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

        foreach ($data as $key => $value) {
            if ($value === '') $data[$key] = null;
        }

        if (!empty($data['naissance']) && is_string($data['naissance'])) {
            $date = \DateTime::createFromFormat('Y-m-d', $data['naissance']);
            $data['DateNaissance'] = ($date && $date->format('Y-m-d') === $data['naissance']) ? $data['naissance'] : null;
        }

        $pieces = [];
        if ($photoData !== null) $pieces['photo'] = base64_encode($photoData);
        if ($cvData !== null) $pieces['cv'] = base64_encode($cvData);
        if ($conventionData !== null) $pieces['convention'] = base64_encode($conventionData);
        if ($lettreData !== null) $pieces['lettre_motivation'] = base64_encode($lettreData);

        $formattedData = [
            ':NumEtu' => $data['NumEtu'] ?? null,
            ':Nom' => $data['Nom'] ?? null,
            ':Prenom' => $data['Prenom'] ?? null,
            ':DateNaissance' => $data['DateNaissance'] ?? null,
            ':Sexe' => $data['Sexe'] ?? null,
            ':Adresse' => $data['Adresse'] ?? null,
            ':CodePostal' => $data['CodePostal'] ?? null,
            ':Ville' => $data['Ville'] ?? null,
            ':EmailPersonnel' => $data['EmailPersonnel'] ?? null,
            ':EmailAMU' => $data['EmailAMU'] ?? null,
            ':Telephone' => $data['Telephone'] ?? null,
            ':CodeDepartement' => $data['CodeDepartement'] ?? null,
            ':Type' => $data['Type'] ?? null,
            ':Zone' => $data['Zone'] ?? null,
            ':PiecesJustificatives' => json_encode($pieces)
        ];

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
        $numEtu = strval($data['NumEtu'] ?? '');
        $existing = $this->getStudentDetails($numEtu);
        
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

        $formattedData = [
            ':Nom' => $data['Nom'] ?? null,
            ':Prenom' => $data['Prenom'] ?? null,
            ':DateNaissance' => $data['DateNaissance'] ?? null,
            ':Sexe' => $data['Sexe'] ?? null,
            ':Adresse' => $data['Adresse'] ?? null,
            ':CodePostal' => $data['CodePostal'] ?? null,
            ':Ville' => $data['Ville'] ?? null,
            ':EmailPersonnel' => $data['EmailPersonnel'] ?? null,
            ':EmailAMU' => $data['EmailAMU'] ?? null,
            ':Telephone' => $data['Telephone'] ?? null,
            ':CodeDepartement' => $data['CodeDepartement'] ?? null,
            ':Type' => $data['Type'] ?? null,
            ':Zone' => $data['Zone'] ?? null,
            ':PiecesJustificatives' => json_encode($oldPieces)
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
}
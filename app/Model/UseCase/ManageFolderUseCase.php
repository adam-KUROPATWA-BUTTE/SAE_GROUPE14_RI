<?php

namespace Model\UseCase;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Model\Repository\DossierRepositoryInterface;
use Model\Persistence\DossierRepositoryPDO;
 
class ManageFolderUseCase
{
    private DossierRepositoryInterface $dossierRepo;

    public function __construct()
    {
        $this->dossierRepo = new DossierRepositoryPDO();
    }

    public function getAllFolders(): array
    {
        return $this->dossierRepo->findAll();
    }

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

    public function getByNumetu(string $numetu): ?array
    {
        return $this->getStudentDetails($numetu);
    }

    public function toggleCompleteStatus(string $numetu): bool
    {
        return $this->dossierRepo->toggleStatus($numetu);
    }

    public function rechercherAvecPagination(array $filters, int $page = 1, int $perPage = 10): array
    {
        return $this->dossierRepo->searchWithPagination($filters, $page, $perPage);
    }

    public function creerDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $photoData = $photoData ?? (is_string($data['photo'] ?? null) ? $data['photo'] : null);
        $cvData = $cvData ?? (is_string($data['cv'] ?? null) ? $data['cv'] : null);
        $conventionData = $conventionData ?? (is_string($data['convention'] ?? null) ? $data['convention'] : null);
        $lettreData = $lettreData ?? (is_string($data['lettre_motivation'] ?? null) ? $data['lettre_motivation'] : null);

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
            'Pays' => $data['pays'] ?? ($data['Pays'] ?? null),
            'Campus' => $data['campus'] ?? ($data['Campus'] ?? null),
            'Discipline' => $data['discipline'] ?? ($data['Discipline'] ?? null),
            'NiveauEtude' => $data['niveau_etude'] ?? ($data['NiveauEtude'] ?? null),
            'Formation' => $data['formation'] ?? ($data['Formation'] ?? null),
            'Moyenne' => $data['moyenne'] ?? ($data['Moyenne'] ?? null),
            'AvisDRI' => $data['avis_dri'] ?? ($data['AvisDRI'] ?? null),
            'DateDebut' => $data['date_debut'] ?? ($data['DateDebut'] ?? null),
            'Langues' => $data['langues'] ?? ($data['Langues'] ?? null),
            'MobiliteAnterieure' => $data['mobilite_anterieure'] ?? ($data['MobiliteAnterieure'] ?? null),
            'PiecesJustificatives' => $piecesJson,
            'status' => $data['status'] ?? 'depot'
        ];

        foreach ($formattedData as $key => $value) {
            if ($value === '') $formattedData[$key] = null;
        }

        return $this->dossierRepo->create($formattedData);
    }

    public function updateDossier(array $data, ?string $photoData = null, ?string $cvData = null, ?string $conventionData = null, ?string $lettreData = null): bool
    {
        $numEtu = strval($data['numetu'] ?? ($data['NumEtu'] ?? ''));
        if ($numEtu === '') return false;

        $existing = $this->getStudentDetails($numEtu);
        if (!$existing) return false;
        
        $oldPieces = isset($existing['pieces']) && is_array($existing['pieces']) ? $existing['pieces'] : [];

        $photoData = $photoData ?? (is_string($data['photo'] ?? null) ? $data['photo'] : null);
        $cvData = $cvData ?? (is_string($data['cv'] ?? null) ? $data['cv'] : null);
        $conventionData = $conventionData ?? (is_string($data['convention'] ?? null) ? $data['convention'] : null);
        $lettreData = $lettreData ?? (is_string($data['lettre_motivation'] ?? null) ? $data['lettre_motivation'] : null);

        if (!empty($photoData)) $oldPieces['photo'] = base64_encode($photoData);
        if (!empty($cvData)) $oldPieces['cv'] = base64_encode($cvData);
        if (!empty($conventionData)) $oldPieces['convention'] = base64_encode($conventionData);
        if (!empty($lettreData)) $oldPieces['lettre_motivation'] = base64_encode($lettreData);

        $piecesJson = empty($oldPieces) ? '{}' : json_encode($oldPieces);

        $rawDate = $data['naissance'] ?? ($data['DateNaissance'] ?? null);
        $dateNaissance = null;
        if (!empty($rawDate) && is_string($rawDate)) {
            $date = \DateTime::createFromFormat('Y-m-d', $rawDate);
            $dateNaissance = ($date && $date->format('Y-m-d') === $rawDate) ? $rawDate : null;
        }

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
            ':Pays' => $data['pays'] ?? ($data['Pays'] ?? null),
            ':Campus' => $data['campus'] ?? ($data['Campus'] ?? null),
            ':Discipline' => $data['discipline'] ?? ($data['Discipline'] ?? null),
            ':NiveauEtude' => $data['niveau_etude'] ?? ($data['NiveauEtude'] ?? null),
            ':Formation' => $data['formation'] ?? ($data['Formation'] ?? null),
            ':Moyenne' => $data['moyenne'] ?? ($data['Moyenne'] ?? null),
            ':AvisDRI' => $data['avis_dri'] ?? ($data['AvisDRI'] ?? null),
            ':DateDebut' => $data['date_debut'] ?? ($data['DateDebut'] ?? null),
            ':Langues' => $data['langues'] ?? ($data['Langues'] ?? null),
            ':MobiliteAnterieure' => $data['mobilite_anterieure'] ?? ($data['MobiliteAnterieure'] ?? null),
            ':PiecesJustificatives' => $piecesJson,
            ':status' => $existing['status'] ?? 'depot'
        ];

        return $this->dossierRepo->update($numEtu, $formattedData);
    }

    public function importFoldersFromCSV(string $filePath): bool
    {
        try {
            $spreadsheet = IOFactory::load($filePath);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            if (empty($rows) || count($rows) < 2) return false;

            $headers = array_map(function($val) {
                return strtolower(trim(strval($val)));
            }, array_shift($rows));
            
            // Dictionnaire des mots-clés enrichi avec 'individu', 'début', 'langue', 'ayant déjà'
            $keywords = [
                'NumEtu'             => ['identifiant', 'numetu', 'etudiant', 'individu', 'formulaire en ligne'],
                'Candidat'           => ['candidat', 'nom'],
                'Prenom'             => ['prénom', 'prenom'],
                'DateNaissance'      => ['naissance', 'birth'],
                'Sexe'               => ['sexe', 'genre'],
                'Adresse'            => ['adresse', 'address', 'rue'],
                'CodePostal'         => ['postal', 'cp', 'zip'],
                'Ville'              => ['ville', 'city', 'commune'],
                'EmailPersonnel'     => ['email', 'courriel', 'mail'],
                'EmailAMU'           => ['amu', 'institutionnel'],
                'Telephone'          => ['phone', 'téléphone', 'telephone', 'mobile', 'tel'],
                'CodeDepartement'    => ['composante', 'département', 'departement', 'faculté'],
                'Type'               => ['type', 'mobilité'],
                'Zone'               => ['zone'],
                'Pays'               => ['pays', 'country'],
                'Campus'             => ['campus'],
                'Discipline'         => ['discipline'],
                'NiveauEtude'        => ['niveau'],
                'Formation'          => ['formation'],
                'Moyenne'            => ['moyenne'],
                'AvisDRI'            => ['avis dri', 'avis'],
                'DateDebut'          => ['période de', 'début', 'date de début'],
                'Langues'            => ['langue'],
                'MobiliteAnterieure' => ['ayant déjà effect', 'mobilité antérieure']
            ];

            $indices = [];
            foreach ($keywords as $field => $searchWords) {
                $indices[$field] = -1;
                foreach ($headers as $index => $header) {
                    foreach ($searchWords as $word) {
                        if (strpos($header, $word) !== false) {
                            $indices[$field] = $index;
                            break 2;
                        }
                    }
                }
            }

            $getVal = function($field) use (&$data, $indices) {
                $idx = $indices[$field];
                return ($idx !== -1 && isset($data[$idx])) ? trim(strval($data[$idx])) : '';
            };

            $dossiersToInsert = [];

            foreach ($rows as $data) {
                if (!is_array($data)) continue;

                $numEtu = $getVal('NumEtu') ?: trim(strval($data[0] ?? ''));
                if (empty($numEtu)) continue;

                $candidatVal = $getVal('Candidat');
                if (!empty($candidatVal)) {
                    $candidatVal = str_replace(',', '', $candidatVal);
                    $parts = explode(' ', $candidatVal, 2);
                    if (count($parts) === 2 && $indices['Prenom'] === -1) {
                        $nom = strtoupper(trim($parts[0]));
                        $prenom = trim($parts[1]);
                    } else {
                        $nom = strtoupper($candidatVal);
                        $prenom = $getVal('Prenom') ?: '-';
                    }
                } else {
                    $nom = 'INCONNU';
                    $prenom = $getVal('Prenom') ?: '-';
                }

                $rawDate = $getVal('DateNaissance');
                $dateNaissance = null;
                if (!empty($rawDate)) {
                    if (preg_match('#^(\d{2})[-/](\d{2})[-/](\d{4})$#', $rawDate, $matches)) {
                        $dateNaissance = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                    } else {
                        $dateNaissance = $rawDate; 
                    }
                }

                $dossiersToInsert[] = [
                    'NumEtu'             => $numEtu,
                    'Nom'                => $nom,
                    'Prenom'             => $prenom,
                    'DateNaissance'      => $dateNaissance,
                    'Sexe'               => $getVal('Sexe'),
                    'Adresse'            => $getVal('Adresse'),
                    'CodePostal'         => $getVal('CodePostal'),
                    'Ville'              => $getVal('Ville'),
                    'EmailPersonnel'     => $getVal('EmailPersonnel'),
                    'EmailAMU'           => $getVal('EmailAMU'),
                    'Telephone'          => $getVal('Telephone'),
                    'CodeDepartement'    => $getVal('CodeDepartement'),
                    'Type'               => $getVal('Type') ?: 'sortant',
                    'Zone'               => $getVal('Zone') ?: 'europe',
                    'Pays'               => $getVal('Pays'),
                    'Campus'             => $getVal('Campus'),
                    'Discipline'         => $getVal('Discipline'),
                    'NiveauEtude'        => $getVal('NiveauEtude'),
                    'Formation'          => $getVal('Formation'),
                    'Moyenne'            => $getVal('Moyenne'),
                    'AvisDRI'            => $getVal('AvisDRI'),
                    'DateDebut'          => $getVal('DateDebut'),
                    'Langues'            => $getVal('Langues'),
                    'MobiliteAnterieure' => $getVal('MobiliteAnterieure')
                ];
            }

            if (!empty($dossiersToInsert)) {
                return $this->dossierRepo->upsertMultiple($dossiersToInsert) > 0;
            }
            return false;
            
        } catch (\Exception $e) {
            error_log("Import Error (PhpSpreadsheet/UseCase): " . $e->getMessage());
            return false;
        }
    }
    
    public function cycleFolderStatus(string $numEtu): bool
    {
        return $this->dossierRepo->cycleStatus($numEtu);
    }
}
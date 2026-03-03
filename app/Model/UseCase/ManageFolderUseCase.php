<?php

namespace Model\UseCase;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date; 
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
        if (!$result) return null;

        $piecesJson = $result['PiecesJustificatives'] ?? '';
        $pieces = (is_string($piecesJson) && $piecesJson !== '') ? (json_decode($piecesJson, true) ?? []) : [];
        
        foreach ($pieces as $key => $val) {
            if (is_string($val)) {
                $pieces[$key] = ['file' => $val, 'status' => 'pending', 'comment' => ''];
            }
        }
            
        $result['pieces'] = $pieces;
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

    public function searchWithoutPagination(array $filters): array
    {
        return $this->dossierRepo->searchWithPagination($filters, 1, 0);
    }

    public function updateDocumentStatus(string $numEtu, string $docType, string $status, string $comment): bool
    {
        $dossier = $this->getStudentDetails($numEtu);
        if (!$dossier) return false;
        
        $pieces = $dossier['pieces'] ?? [];
        if (!isset($pieces[$docType])) {
            $pieces[$docType] = ['file' => '', 'status' => $status, 'comment' => $comment];
        } else {
            $pieces[$docType]['status'] = $status;
            $pieces[$docType]['comment'] = $comment;
        }
        
        return $this->dossierRepo->update($numEtu, [':PiecesJustificatives' => json_encode($pieces)]);
    }

    public function setFolderStatus(string $numEtu, string $status): bool
    {
        return $this->dossierRepo->setStatus($numEtu, $status);
    }

    public function creerDossier(array $data): bool
    {
        $pieces = [];
        $addPiece = function(?string $fileData) {
            return $fileData !== null ? ['file' => base64_encode($fileData), 'status' => 'pending', 'comment' => ''] : null;
        };

        if (!empty($data['photo'])) $pieces['photo'] = $addPiece($data['photo']);
        if (!empty($data['cv'])) $pieces['cv'] = $addPiece($data['cv']);
        if (!empty($data['convention'])) $pieces['convention'] = $addPiece($data['convention']);
        if (!empty($data['lettre_motivation'])) $pieces['lettre_motivation'] = $addPiece($data['lettre_motivation']);
        if (!empty($data['langues_file'])) $pieces['langues'] = $addPiece($data['langues_file']);

        $piecesJson = empty($pieces) ? '{}' : json_encode($pieces);

        $rawDate = $data['naissance'] ?? ($data['DateNaissance'] ?? null);
        $dateNaissance = null;
        if (!empty($rawDate) && is_string($rawDate)) {
            $date = \DateTime::createFromFormat('Y-m-d', $rawDate);
            $dateNaissance = ($date && $date->format('Y-m-d') === $rawDate) ? $rawDate : null;
        }

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
            'Composante' => $data['composante'] ?? ($data['Composante'] ?? null),
            'Type' => $data['type'] ?? ($data['Type'] ?? null),
            'Zone' => $data['zone'] ?? ($data['Zone'] ?? null),
            'Pays' => $data['pays'] ?? ($data['Pays'] ?? null),
            'Campus' => $data['campus'] ?? ($data['Campus'] ?? null),
            'Discipline' => $data['discipline'] ?? ($data['Discipline'] ?? null),
            'NiveauEtude' => $data['niveau_etude'] ?? ($data['NiveauEtude'] ?? null),
            'Formation' => $data['formation'] ?? ($data['Formation'] ?? null),
            'MoyenneBac' => $data['moyenne_bac'] ?? ($data['MoyenneBac'] ?? null),
            'MoyenneSansBac' => $data['moyenne_sans_bac'] ?? ($data['MoyenneSansBac'] ?? null),
            'AvisDRI' => $data['avis_dri'] ?? ($data['AvisDRI'] ?? null),
            'DateDebut' => $data['date_debut'] ?? ($data['DateDebut'] ?? null),
            'MobiliteAnterieure' => $data['mobilite_anterieure'] ?? ($data['MobiliteAnterieure'] ?? null),
            'PiecesJustificatives' => $piecesJson,
            'status' => $data['status'] ?? 'depot'
        ];

        foreach ($formattedData as $key => $value) {
            if ($value === '') $formattedData[$key] = null;
        }

        return $this->dossierRepo->create($formattedData);
    }

    public function updateDossier(array $data): bool
    {
        $numEtu = strval($data['numetu'] ?? ($data['NumEtu'] ?? ''));
        if ($numEtu === '') return false;

        $existing = $this->getStudentDetails($numEtu);
        if (!$existing) return false;
        
        $oldPieces = isset($existing['pieces']) && is_array($existing['pieces']) ? $existing['pieces'] : [];

        $updatePiece = function(array &$arr, string $key, ?string $fileData) {
            if (!empty($fileData)) {
                $arr[$key] = ['file' => base64_encode($fileData), 'status' => 'pending', 'comment' => ''];
            }
        };

        $updatePiece($oldPieces, 'photo', $data['photo'] ?? null);
        $updatePiece($oldPieces, 'cv', $data['cv'] ?? null);
        $updatePiece($oldPieces, 'convention', $data['convention'] ?? null);
        $updatePiece($oldPieces, 'lettre_motivation', $data['lettre_motivation'] ?? null);
        $updatePiece($oldPieces, 'langues', $data['langues_file'] ?? null);

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
            ':Composante' => $data['composante'] ?? ($data['Composante'] ?? null),
            ':Type' => $data['type'] ?? ($data['Type'] ?? null),
            ':Zone' => $data['zone'] ?? ($data['Zone'] ?? null),
            ':Pays' => $data['pays'] ?? ($data['Pays'] ?? null),
            ':Campus' => $data['campus'] ?? ($data['Campus'] ?? null),
            ':Discipline' => $data['discipline'] ?? ($data['Discipline'] ?? null),
            ':NiveauEtude' => $data['niveau_etude'] ?? ($data['NiveauEtude'] ?? null),
            ':Formation' => $data['formation'] ?? ($data['Formation'] ?? null),
            ':MoyenneBac' => $data['moyenne_bac'] ?? ($data['MoyenneBac'] ?? null),
            ':MoyenneSansBac' => $data['moyenne_sans_bac'] ?? ($data['MoyenneSansBac'] ?? null),
            ':AvisDRI' => $data['avis_dri'] ?? ($data['AvisDRI'] ?? null),
            ':DateDebut' => $data['date_debut'] ?? ($data['DateDebut'] ?? null),
            ':MobiliteAnterieure' => $data['mobilite_anterieure'] ?? ($data['MobiliteAnterieure'] ?? null),
            ':PiecesJustificatives' => $piecesJson,
            ':status' => $existing['status'] ?? 'depot'
        ];

        return $this->dossierRepo->update($numEtu, $formattedData);
    }

    public function importFoldersFromCSV(string $filePath, string $originalFileName = ''): bool
    {
        try {
            $ext = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
            $rows = [];

            // RÉSOLUTION DU BUG "FICHIER VIDE" SUR MAC : On force la détection des retours à la ligne
            if ($ext === 'csv') {
                $oldSetting = ini_get('auto_detect_line_endings');
                ini_set('auto_detect_line_endings', '1');
                
                if (($handle = fopen($filePath, "r")) !== false) {
                    $firstLine = fgets($handle);
                    $delimiter = substr_count((string)$firstLine, ';') > substr_count((string)$firstLine, ',') ? ';' : ',';
                    rewind($handle);
                    
                    while (($data = fgetcsv($handle, 0, $delimiter)) !== false) {
                        $rows[] = $data;
                    }
                    fclose($handle);
                }
                ini_set('auto_detect_line_endings', $oldSetting);
            } else {
                $spreadsheet = IOFactory::load($filePath);
                $worksheet = $spreadsheet->getActiveSheet();
                $rows = $worksheet->toArray();
            }
            
            if (empty($rows) || count($rows) < 2) return false;

            $normalize = function($string) {
                $string = mb_strtolower(trim(strval($string)), 'UTF-8');
                $unwanted = [
                    'á'=>'a', 'à'=>'a', 'â'=>'a', 'ä'=>'a', 'ã'=>'a', 'å'=>'a',
                    'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e',
                    'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i',
                    'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'ö'=>'o', 'õ'=>'o',
                    'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u',
                    'ç'=>'c', 'ñ'=>'n', 'œ'=>'oe'
                ];
                $string = strtr($string, $unwanted);
                $string = preg_replace('/[^a-z0-9\s]/', ' ', $string); 
                $string = preg_replace('/\s+/', ' ', $string);
                return trim($string);
            };

            $headers = array_map($normalize, array_shift($rows));
            
            $keywords = [
                'NumEtu'             => ['individus identifiant', 'individu identifiant', 'identifiant utilisateur', 'identifiant', 'individu', 'numetu', 'numero etudiant'],
                'Candidat'           => ['candidat nom prenom', 'candidat'],
                'Nom'                => ['nom de famille', 'nom'],
                'Prenom'             => ['prenom'],
                'DateNaissance'      => ['naissance', 'date de naissance'],
                'Sexe'               => ['sexe', 'genre'],
                'Adresse'            => ['adresse', 'address', 'rue'],
                'CodePostal'         => ['code postal', 'postal', 'cp'],
                'Ville'              => ['ville', 'city', 'commune'],
                'EmailAMU'           => ['amu', 'institutionnel'],
                'EmailPersonnel'     => ['email personnel', 'e mail personnel', 'email', 'courriel', 'mail'],
                'Telephone'          => ['telephone', 'mobile', 'tel', 'phone'],
                'CodeDepartement'    => ['departement', 'filiere'],
                'Composante'         => ['sejour origine', 'sejour', 'composante', 'faculte', 'institut'],
                'Type'               => ['type de mobilite', 'type'], 
                'Zone'               => ['zone'],
                'Pays'               => ['pays 1', 'pays', 'country'],
                'Campus'             => ['campus'],
                'Discipline'         => ['discipline'],
                'NiveauEtude'        => ['niveau d etude', 'niveau'],
                'Formation'          => ['nom formation', 'formation'],
                'MoyenneBac'         => ['moyenne avec bac', 'moyenne bac'],
                'MoyenneSansBac'     => ['moyenne sans bac', 'moyenne hors'],
                'AvisDRI'            => ['avis globale du departement', 'avis globale', 'avis dri', 'decision dri'],
                'DateDebut'          => ['periode de debut', 'debut'],
                'MobiliteAnterieure' => ['deja parti', 'a deja effectue', 'ayant deja effect', 'deja effecte', 'deja effectue', 'mobilite anterieure']
            ];

            $indices = array_fill_keys(array_keys($keywords), -1);

            foreach ($keywords as $field => $searchWords) {
                foreach ($headers as $index => $header) {
                    if ($indices[$field] !== -1) continue;
                    foreach ($searchWords as $word) {
                        if ($header === $word) { $indices[$field] = $index; break 2; }
                    }
                }
            }
            foreach ($keywords as $field => $searchWords) {
                if ($indices[$field] !== -1) continue;
                foreach ($headers as $index => $header) {
                    if (in_array($index, $indices, true)) continue;
                    foreach ($searchWords as $word) {
                        if (strpos($header, $word) === 0) { $indices[$field] = $index; break 2; }
                    }
                }
            }
            foreach ($keywords as $field => $searchWords) {
                if ($indices[$field] !== -1) continue;
                foreach ($headers as $index => $header) {
                    if (in_array($index, $indices, true)) continue;
                    foreach ($searchWords as $word) {
                        if (strpos($header, $word) !== false) { $indices[$field] = $index; break 2; }
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

                $numEtu = $getVal('NumEtu');
                $numEtu = preg_replace('/[^a-zA-Z0-9]/', '', $numEtu); 
                if (empty($numEtu)) continue;

                $candidatVal = $getVal('Candidat');
                $nomVal = $getVal('Nom');
                $nom = '';
                $prenom = '';
                
                if (!empty($candidatVal)) {
                    if (strpos($candidatVal, ',') !== false) {
                        $parts = explode(',', $candidatVal, 2);
                        $nom = strtoupper(trim($parts[0]));
                        $prenom = ucwords(strtolower(trim($parts[1])));
                    } else {
                        $parts = explode(' ', $candidatVal, 2);
                        if (count($parts) === 2 && $indices['Prenom'] === -1) {
                            $nom = strtoupper(trim($parts[0]));
                            $prenom = ucwords(strtolower(trim($parts[1])));
                        } else {
                            $nom = strtoupper(trim($candidatVal));
                            $prenom = $getVal('Prenom') ?: '-';
                        }
                    }
                } else {
                    $nom = !empty($nomVal) ? strtoupper(trim($nomVal)) : 'INCONNU';
                    $prenom = $getVal('Prenom') ?: '-';
                }

                $rawDate = $getVal('DateNaissance');
                $dateNaissance = null;
                if (!empty($rawDate)) {
                    if (is_numeric($rawDate)) {
                        try {
                            $dateObj = Date::excelToDateTimeObject($rawDate);
                            $dateNaissance = $dateObj->format('Y-m-d');
                        } catch (\Exception $e) {}
                    } 
                    if (!$dateNaissance) {
                        if (preg_match('#^(\d{2})[-/](\d{2})[-/](\d{4})$#', $rawDate, $matches)) {
                            $dateNaissance = $matches[3] . '-' . $matches[2] . '-' . $matches[1];
                        } else {
                            $parsed = strtotime($rawDate);
                            $dateNaissance = ($parsed !== false) ? date('Y-m-d', $parsed) : null; 
                        }
                    }
                }

                $moyenneBac = str_replace(',', '.', $getVal('MoyenneBac'));
                $moyenneSansBac = str_replace(',', '.', $getVal('MoyenneSansBac'));

                $composanteRaw = $getVal('Composante');
                if (stripos($composanteRaw, 'IUT') !== false) {
                    $composanteRaw = 'IUT';
                } elseif (stripos($composanteRaw, 'CIVIS') !== false) {
                    $composanteRaw = 'AMU CIVIS';
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
                    'Composante'         => $composanteRaw,
                    'Type'               => $getVal('Type') ?: 'sortant',
                    'Zone'               => $getVal('Zone') ?: 'europe',
                    'Pays'               => $getVal('Pays'),
                    'Campus'             => $getVal('Campus'),
                    'Discipline'         => $getVal('Discipline'),
                    'NiveauEtude'        => $getVal('NiveauEtude'),
                    'Formation'          => $getVal('Formation'),
                    'MoyenneBac'         => $moyenneBac,
                    'MoyenneSansBac'     => $moyenneSansBac,
                    'AvisDRI'            => $getVal('AvisDRI'),
                    'DateDebut'          => $getVal('DateDebut'),
                    'MobiliteAnterieure' => $getVal('MobiliteAnterieure')
                ];
            }

            if (!empty($dossiersToInsert)) {
                return $this->dossierRepo->upsertMultiple($dossiersToInsert) > 0;
            }
            return false;
            
        } catch (\Exception $e) {
            error_log("Import Error: " . $e->getMessage());
            return false;
        }
    }
    
    public function cycleFolderStatus(string $numEtu): bool
    {
        return $this->dossierRepo->cycleStatus($numEtu);
    }
}
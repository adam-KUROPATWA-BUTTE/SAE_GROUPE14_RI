<?php

namespace Model\Persistence;

use Model\Repository\DossierRepositoryInterface;
use PDO;
use Database;
use Model\Entity\DossierStats;
use Model\Entity\GenderStats;

class DossierRepositoryPDO implements DossierRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getGlobalStats(): DossierStats
    {
        return $this->getDossierStats();
    }

    public function getDossierStats(): DossierStats
    {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) as total, SUM(CASE WHEN IsComplete = 1 THEN 1 ELSE 0 END) as completed FROM dossiers");
            $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            $total = is_numeric($result['total'] ?? 0) ? (int)$result['total'] : 0;
            $completed = is_numeric($result['completed'] ?? 0) ? (int)$result['completed'] : 0;
            return new DossierStats($total, $completed);
        } catch (\PDOException $e) { return new DossierStats(0, 0); }
    }

    public function getGenderStats(): GenderStats
    {
        try {
            $stmt = $this->db->query("
                SELECT SUM(CASE WHEN LOWER(Sexe) IN ('m','homme','male','masculin') THEN 1 ELSE 0 END) as male,
                       SUM(CASE WHEN LOWER(Sexe) IN ('f','femme','female','féminin','feminin') THEN 1 ELSE 0 END) as female
                FROM dossiers WHERE Sexe IS NOT NULL AND Sexe != ''
            ");
            $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            return new GenderStats(is_numeric($result['male'] ?? 0) ? (int)$result['male'] : 0, is_numeric($result['female'] ?? 0) ? (int)$result['female'] : 0);
        } catch (\PDOException $e) { return new GenderStats(0, 0); }
    }

    public function getTopCountries(int $limit): array
    {
        try {
            $stmt = $this->db->prepare("SELECT Pays as name, COUNT(*) as count FROM dossiers WHERE Pays IS NOT NULL AND Pays != '' GROUP BY Pays ORDER BY count DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) { return []; }
    }

    public function getDepartmentStats(int $limit): array
    {
        try {
            $stmt = $this->db->prepare("SELECT CodeDepartement as name, COUNT(*) as count FROM dossiers WHERE CodeDepartement IS NOT NULL AND CodeDepartement != '' GROUP BY CodeDepartement ORDER BY count DESC LIMIT :limit");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) { return []; }
    }

    public function findAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement, Composante, Pays,
                       Campus, Discipline, NiveauEtude, Formation, MoyenneBac, MoyenneSansBac, AvisDRI, 
                       DateDebut, MobiliteAnterieure, IsComplete, PiecesJustificatives, status
                FROM dossiers ORDER BY Nom, Prenom
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\PDOException $e) { return []; }
    }

    public function findByNumEtu(string $numEtu): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                       EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Composante, Type, Zone, Pays,
                       Campus, Discipline, NiveauEtude, Formation, MoyenneBac, MoyenneSansBac, AvisDRI,
                       DateDebut, MobiliteAnterieure, IsComplete, PiecesJustificatives, status
                FROM dossiers WHERE NumEtu = :numetu LIMIT 1
            ");
            $stmt->execute([':numetu' => $numEtu]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        } catch (\PDOException $e) { return null; }
    }

    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO dossiers (
                    NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                    EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Composante, Type, Zone, Pays,
                    Campus, Discipline, NiveauEtude, Formation, MoyenneBac, MoyenneSansBac, AvisDRI,
                    DateDebut, MobiliteAnterieure, IsComplete, PiecesJustificatives, status
                ) VALUES (
                    :NumEtu, :Nom, :Prenom, :DateNaissance, :Sexe, :Adresse, :CodePostal, :Ville,
                    :EmailPersonnel, :EmailAMU, :Telephone, :CodeDepartement, :Composante, :Type, :Zone, :Pays,
                    :Campus, :Discipline, :NiveauEtude, :Formation, :MoyenneBac, :MoyenneSansBac, :AvisDRI,
                    :DateDebut, :MobiliteAnterieure, 0, :PiecesJustificatives, :status
                )
            ");
            return $stmt->execute([
                ':NumEtu' => $data['NumEtu'] ?? null, ':Nom' => $data['Nom'] ?? null, ':Prenom' => $data['Prenom'] ?? null,
                ':DateNaissance' => $data['DateNaissance'] ?? null, ':Sexe' => $data['Sexe'] ?? null,
                ':Adresse' => $data['Adresse'] ?? null, ':CodePostal' => $data['CodePostal'] ?? null,
                ':Ville' => $data['Ville'] ?? null, ':EmailPersonnel' => $data['EmailPersonnel'] ?? null,
                ':EmailAMU' => $data['EmailAMU'] ?? null, ':Telephone' => $data['Telephone'] ?? null,
                ':CodeDepartement' => $data['CodeDepartement'] ?? null, ':Composante' => $data['Composante'] ?? null,
                ':Type' => $data['Type'] ?? null, ':Zone' => $data['Zone'] ?? null, ':Pays' => $data['Pays'] ?? null,
                ':Campus' => $data['Campus'] ?? null, ':Discipline' => $data['Discipline'] ?? null,
                ':NiveauEtude' => $data['NiveauEtude'] ?? null, ':Formation' => $data['Formation'] ?? null,
                ':MoyenneBac' => $data['MoyenneBac'] ?? null, ':MoyenneSansBac' => $data['MoyenneSansBac'] ?? null,
                ':AvisDRI' => $data['AvisDRI'] ?? null, ':DateDebut' => $data['DateDebut'] ?? null,
                ':MobiliteAnterieure' => $data['MobiliteAnterieure'] ?? null,
                ':PiecesJustificatives' => $data['PiecesJustificatives'] ?? '{}', ':status' => $data['status'] ?? 'depot'
            ]);
        } catch (\PDOException $e) { return false; }
    }

    public function update(string $numEtu, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE dossiers
                SET 
                    Nom = COALESCE(:Nom, Nom), Prenom = COALESCE(:Prenom, Prenom), DateNaissance = COALESCE(:DateNaissance, DateNaissance),
                    Sexe = COALESCE(:Sexe, Sexe), Adresse = COALESCE(:Adresse, Adresse), CodePostal = COALESCE(:CodePostal, CodePostal),
                    Ville = COALESCE(:Ville, Ville), EmailPersonnel = COALESCE(:EmailPersonnel, EmailPersonnel), EmailAMU = COALESCE(:EmailAMU, EmailAMU),
                    Telephone = COALESCE(:Telephone, Telephone), CodeDepartement = COALESCE(:CodeDepartement, CodeDepartement), 
                    Composante = COALESCE(:Composante, Composante), Type = COALESCE(:Type, Type),
                    Zone = COALESCE(:Zone, Zone), Pays = COALESCE(:Pays, Pays), Campus = COALESCE(:Campus, Campus), 
                    Discipline = COALESCE(:Discipline, Discipline), NiveauEtude = COALESCE(:NiveauEtude, NiveauEtude), 
                    Formation = COALESCE(:Formation, Formation), MoyenneBac = COALESCE(:MoyenneBac, MoyenneBac), MoyenneSansBac = COALESCE(:MoyenneSansBac, MoyenneSansBac),
                    AvisDRI = COALESCE(:AvisDRI, AvisDRI), DateDebut = COALESCE(:DateDebut, DateDebut), MobiliteAnterieure = COALESCE(:MobiliteAnterieure, MobiliteAnterieure),
                    PiecesJustificatives = COALESCE(:PiecesJustificatives, PiecesJustificatives), status = COALESCE(:status, status)
                WHERE NumEtu = :NumEtu
            ");
            $data[':NumEtu'] = $numEtu;
            return $stmt->execute($data);
        } catch (\PDOException $e) { return false; }
    }

    public function toggleStatus(string $numEtu): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT IsComplete FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numEtu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($current)) return false;

            $newStatus = (($current['IsComplete'] ?? 0) == 1) ? 0 : 1;
            $stmt = $this->db->prepare("UPDATE dossiers SET IsComplete = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $newStatus, ':numetu' => $numEtu]);
        } catch (\PDOException $e) { return false; }
    }

    public function setStatus(string $numEtu, string $status): bool
    {
        $allowed = ['depot', 'instruction', 'accepte', 'refuse'];
        if (!in_array($status, $allowed, true)) return false;
        try {
            $stmt = $this->db->prepare("UPDATE dossiers SET status = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $status, ':numetu' => $numEtu]);
        } catch (\PDOException $e) { return false; }
    }

    public function cycleStatus(string $numEtu): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT status FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numEtu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($current)) return false;

            $statuses = ['depot', 'instruction', 'accepte', 'refuse'];
            $currentStatus = strtolower(trim($current['status'] ?? 'depot'));
            $currentIndex = array_search($currentStatus, $statuses, true);
            $nextIndex = ($currentIndex === false || $currentIndex === count($statuses) - 1) ? 0 : $currentIndex + 1;
            $nextStatus = $statuses[$nextIndex];

            $stmt = $this->db->prepare("UPDATE dossiers SET status = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $nextStatus, ':numetu' => $numEtu]);
        } catch (\PDOException $e) { return false; }
    }

    public function searchWithPagination(array $filters, int $page, int $perPage): array
    {
        $params = [];
        $whereConditions = " WHERE 1=1";

        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            $whereConditions .= ($filters['complet'] == '1') ? " AND IsComplete = 1" : " AND (IsComplete = 0 OR IsComplete IS NULL)";
        }
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $whereConditions .= " AND LOWER(Type) = LOWER(:type)";
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['zone']) && $filters['zone'] !== 'all') {
            $whereConditions .= " AND LOWER(Zone) = LOWER(:zone)";
            $params['zone'] = $filters['zone'];
        }
        if (!empty($filters['composante']) && $filters['composante'] !== 'all') {
            $whereConditions .= " AND LOWER(Composante) LIKE LOWER(:composante)";
            $params['composante'] = '%' . $filters['composante'] . '%';
        }
        if (!empty($filters['accord']) && $filters['accord'] !== 'all') {
            $whereConditions .= " AND LOWER(Composante) LIKE LOWER(:accord)";
            $params['accord'] = '%' . $filters['accord'] . '%';
        }
        if (!empty($filters['search'])) {
            $searchValue = $filters['search'] . '%';
            $whereConditions .= " AND (Nom LIKE :search1 OR Prenom LIKE :search2 OR NumEtu LIKE :search3 OR EmailPersonnel LIKE :search4)";
            $params['search1'] = $searchValue;
            $params['search2'] = $searchValue;
            $params['search3'] = $searchValue;
            $params['search4'] = $searchValue;
        }

        $totalCount = 0;
        try {
            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM dossiers" . $whereConditions);
            foreach ($params as $key => $value) $countStmt->bindValue(':' . $key, $value);
            $countStmt->execute();
            $row = $countStmt->fetch(PDO::FETCH_ASSOC);
            $totalCount = is_array($row) ? (int)($row['total'] ?? 0) : 0;
        } catch (\PDOException $e) {}

        $limitClause = "";
        if ($perPage > 0) {
            $limitClause = " LIMIT :limit OFFSET :offset";
        }

        $sql = "SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone, Pays,
                       Campus, Discipline, NiveauEtude, Formation, MoyenneBac, MoyenneSansBac, AvisDRI,
                       DateDebut, MobiliteAnterieure, DateNaissance, Sexe, Adresse, CodePostal, Ville, 
                       EmailAMU, CodeDepartement, Composante, IsComplete, PiecesJustificatives, status
                FROM dossiers " . $whereConditions . " ORDER BY Nom ASC, Prenom ASC" . $limitClause;

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            if ($perPage > 0) {
                $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
            }
            $stmt->execute();

            $totalPages = 1;
            if ($perPage > 0 && $totalCount > 0) {
                $totalPages = (int)ceil($totalCount / $perPage);
            }

            return ['data' => $stmt->fetchAll(PDO::FETCH_ASSOC), 'total' => $totalCount, 'totalPages' => $totalPages];
        } catch (\PDOException $e) { return ['data' => [], 'total' => 0, 'totalPages' => 0]; }
    }

    public function upsertMultiple(array $dossiers): int
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $pdo->beginTransaction();
            
            // LA SÉCURITÉ ABSOLUE EST ICI :
            // 1. On remplace les cases vides par des chaînes vides '' (pour éviter de froisser les contraintes NOT NULL de la BDD).
            // 2. Si on écrase une ligne existante avec une valeur vide, on ordonne à MySQL de GARDER l'ancienne valeur.
            $stmt = $pdo->prepare("
                INSERT INTO dossiers (
                    NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                    EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Composante, Type, Zone, Pays,
                    Campus, Discipline, NiveauEtude, Formation, MoyenneBac, MoyenneSansBac, AvisDRI,
                    DateDebut, MobiliteAnterieure, IsComplete, PiecesJustificatives, status
                ) VALUES (
                    :NumEtu, :Nom, :Prenom, :DateNaissance, :Sexe, :Adresse, :CodePostal, :Ville,
                    :EmailPersonnel, :EmailAMU, :Telephone, :CodeDepartement, :Composante, :Type, :Zone, :Pays,
                    :Campus, :Discipline, :NiveauEtude, :Formation, :MoyenneBac, :MoyenneSansBac, :AvisDRI,
                    :DateDebut, :MobiliteAnterieure, 0, '{}', 'depot'
                )
                ON DUPLICATE KEY UPDATE 
                    Nom = IF(VALUES(Nom) = 'INCONNU', dossiers.Nom, VALUES(Nom)), 
                    Prenom = IF(VALUES(Prenom) = '-', dossiers.Prenom, VALUES(Prenom)),
                    DateNaissance = IF(VALUES(DateNaissance) IS NULL, dossiers.DateNaissance, VALUES(DateNaissance)),
                    Sexe = IF(VALUES(Sexe) IS NULL, dossiers.Sexe, VALUES(Sexe)),
                    Adresse = IF(VALUES(Adresse) = '', dossiers.Adresse, VALUES(Adresse)),
                    CodePostal = IF(VALUES(CodePostal) = '', dossiers.CodePostal, VALUES(CodePostal)),
                    Ville = IF(VALUES(Ville) = '', dossiers.Ville, VALUES(Ville)),
                    EmailPersonnel = IF(VALUES(EmailPersonnel) = '', dossiers.EmailPersonnel, VALUES(EmailPersonnel)),
                    EmailAMU = IF(VALUES(EmailAMU) = '', dossiers.EmailAMU, VALUES(EmailAMU)),
                    Telephone = IF(VALUES(Telephone) = '', dossiers.Telephone, VALUES(Telephone)),
                    CodeDepartement = IF(VALUES(CodeDepartement) = '', dossiers.CodeDepartement, VALUES(CodeDepartement)),
                    Composante = IF(VALUES(Composante) = '', dossiers.Composante, VALUES(Composante)),
                    Type = IF(VALUES(Type) = '', dossiers.Type, VALUES(Type)),
                    Zone = IF(VALUES(Zone) = '', dossiers.Zone, VALUES(Zone)),
                    Pays = IF(VALUES(Pays) = '', dossiers.Pays, VALUES(Pays)),
                    Campus = IF(VALUES(Campus) = '', dossiers.Campus, VALUES(Campus)),
                    Discipline = IF(VALUES(Discipline) = '', dossiers.Discipline, VALUES(Discipline)),
                    NiveauEtude = IF(VALUES(NiveauEtude) = '', dossiers.NiveauEtude, VALUES(NiveauEtude)),
                    Formation = IF(VALUES(Formation) = '', dossiers.Formation, VALUES(Formation)),
                    MoyenneBac = IF(VALUES(MoyenneBac) = '', dossiers.MoyenneBac, VALUES(MoyenneBac)),
                    MoyenneSansBac = IF(VALUES(MoyenneSansBac) = '', dossiers.MoyenneSansBac, VALUES(MoyenneSansBac)),
                    AvisDRI = IF(VALUES(AvisDRI) = '', dossiers.AvisDRI, VALUES(AvisDRI)),
                    DateDebut = IF(VALUES(DateDebut) = '', dossiers.DateDebut, VALUES(DateDebut)),
                    MobiliteAnterieure = IF(VALUES(MobiliteAnterieure) = '', dossiers.MobiliteAnterieure, VALUES(MobiliteAnterieure))
            ");

            $insertedRows = 0;

            foreach ($dossiers as $dossier) {
                $stmt->execute([
                    ':NumEtu'         => $dossier['NumEtu'], 
                    ':Nom'            => $dossier['Nom'], 
                    ':Prenom'         => $dossier['Prenom'],
                    ':DateNaissance'  => $dossier['DateNaissance'] ?: null,
                    ':Sexe'           => $dossier['Sexe'] ?: null,
                    ':Adresse'        => $dossier['Adresse'] ?? '',
                    ':CodePostal'     => $dossier['CodePostal'] ?? '',
                    ':Ville'          => $dossier['Ville'] ?? '',
                    ':EmailPersonnel' => $dossier['EmailPersonnel'] ?? '',
                    ':EmailAMU'       => $dossier['EmailAMU'] ?? '',
                    ':Telephone'      => $dossier['Telephone'] ?? '',
                    ':CodeDepartement'=> $dossier['CodeDepartement'] ?? '',
                    ':Composante'     => $dossier['Composante'] ?? '',
                    ':Type'           => !empty($dossier['Type']) ? $dossier['Type'] : 'sortant',
                    ':Zone'           => !empty($dossier['Zone']) ? $dossier['Zone'] : 'europe',
                    ':Pays'           => $dossier['Pays'] ?? '',
                    ':Campus'         => $dossier['Campus'] ?? '',
                    ':Discipline'     => $dossier['Discipline'] ?? '',
                    ':NiveauEtude'    => $dossier['NiveauEtude'] ?? '',
                    ':Formation'      => $dossier['Formation'] ?? '',
                    ':MoyenneBac'     => $dossier['MoyenneBac'] ?? '',
                    ':MoyenneSansBac' => $dossier['MoyenneSansBac'] ?? '',
                    ':AvisDRI'        => $dossier['AvisDRI'] ?? '',
                    ':DateDebut'      => $dossier['DateDebut'] ?? '',
                    ':MobiliteAnterieure' => $dossier['MobiliteAnterieure'] ?? ''
                ]);
                $insertedRows++;
            }
            
            $pdo->commit();
            return $insertedRows;
            
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log("DB Upsert Error: " . $e->getMessage()); 
            return 0;
        }
    }
}
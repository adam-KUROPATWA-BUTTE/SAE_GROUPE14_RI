<?php

namespace Model\Persistence;

use Model\Repository\DossierRepositoryInterface;
use PDO;
use Database;
use Model\Entity\DossierStats;
use Model\Entity\GenderStats;

/**
 * Class DossierRepositoryPDO
 *
 * PDO implementation of the DossierRepositoryInterface.
 * Handles CRUD operations and statistics retrieval for "dossiers".
 */
class DossierRepositoryPDO implements DossierRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    // ===========================
    // Dashboard Statistics
    // ===========================

    public function getGlobalStats(): DossierStats
    {
        return $this->getDossierStats();
    }

    public function getDossierStats(): DossierStats
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN IsComplete = 1 THEN 1 ELSE 0 END) as completed
                FROM dossiers
            ");
            $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

            $total     = is_numeric($result['total']     ?? 0) ? (int) $result['total']     : 0;
            $completed = is_numeric($result['completed'] ?? 0) ? (int) $result['completed'] : 0;

            return new DossierStats($total, $completed);
        } catch (\PDOException $e) {
            error_log("getDossierStats Error: " . $e->getMessage());
            return new DossierStats(0, 0);
        }
    }

    public function getGenderStats(): GenderStats
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    SUM(CASE WHEN LOWER(Sexe) IN ('m','homme','male','masculin') THEN 1 ELSE 0 END) as male,
                    SUM(CASE WHEN LOWER(Sexe) IN ('f','femme','female','féminin','feminin') THEN 1 ELSE 0 END) as female
                FROM dossiers
                WHERE Sexe IS NOT NULL AND Sexe != ''
            ");
            $result = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;

            $male   = is_numeric($result['male']   ?? 0) ? (int) $result['male']   : 0;
            $female = is_numeric($result['female'] ?? 0) ? (int) $result['female'] : 0;

            return new GenderStats($male, $female);
        } catch (\PDOException $e) {
            error_log("getGenderStats Error: " . $e->getMessage());
            return new GenderStats(0, 0);
        }
    }

    /**
     * @param int $limit
     * @return array<int, array{name: string, count: int}>
     */
    public function getTopCountries(int $limit): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT Pays as name, COUNT(*) as count
                FROM dossiers
                WHERE Pays IS NOT NULL AND Pays != ''
                GROUP BY Pays
                ORDER BY count DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results ?: [];
        } catch (\PDOException $e) {
            error_log("getTopCountries Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @param int $limit
     * @return array<int, array{name: string, count: int}>
     */
    public function getDepartmentStats(int $limit): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT CodeDepartement as name, COUNT(*) as count
                FROM dossiers
                WHERE CodeDepartement IS NOT NULL AND CodeDepartement != ''
                GROUP BY CodeDepartement
                ORDER BY count DESC
                LIMIT :limit
            ");
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return $results ?: [];
        } catch (\PDOException $e) {
            error_log("getDepartmentStats Error: " . $e->getMessage());
            return [];
        }
    }

    /** @return array{incoming: int, outgoing: int} */
    public function getIncomingOutgoingStats(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    SUM(CASE WHEN LOWER(Type) = 'entrant' THEN 1 ELSE 0 END) as incoming,
                    SUM(CASE WHEN LOWER(Type) = 'sortant' THEN 1 ELSE 0 END) as outgoing
                FROM dossiers
            ");
            if ($stmt === false) return ['incoming' => 0, 'outgoing' => 0];
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($result)) return ['incoming' => 0, 'outgoing' => 0];
            return [
                'incoming' => is_numeric($result['incoming']) ? (int) $result['incoming'] : 0,
                'outgoing' => is_numeric($result['outgoing']) ? (int) $result['outgoing'] : 0,
            ];
        } catch (\PDOException $e) {
            error_log("getIncomingOutgoingStats Error: " . $e->getMessage());
            return ['incoming' => 0, 'outgoing' => 0];
        }
    }

    /** @return array<int, array{name: string, count: int}> */
    public function getContinentStats(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT Continent as name, COUNT(*) as count
                FROM dossiers
                WHERE Continent IS NOT NULL AND Continent != ''
                GROUP BY Continent
                ORDER BY count DESC
            ");
            if ($stmt === false) return [];
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($results) ? array_map(fn($r) => [
                'name'  => is_string($r['name'])  ? $r['name']  : '',
                'count' => is_numeric($r['count']) ? (int) $r['count'] : 0,
            ], $results) : [];
        } catch (\PDOException $e) {
            error_log("getContinentStats Error: " . $e->getMessage());
            return [];
        }
    }

    /** @return array{europe_countries: int, non_europe_countries: int} */
    public function getEuropeVsNonEuropeStats(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT
                    COUNT(DISTINCT CASE WHEN LOWER(Zone) = 'europe' THEN Pays END) as europe_countries,
                    COUNT(DISTINCT CASE WHEN LOWER(Zone) = 'hors_europe' THEN Pays END) as non_europe_countries
                FROM dossiers
                WHERE Pays IS NOT NULL AND Pays != ''
            ");
            if ($stmt === false) return ['europe_countries' => 0, 'non_europe_countries' => 0];
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($result)) return ['europe_countries' => 0, 'non_europe_countries' => 0];
            return [
                'europe_countries'     => is_numeric($result['europe_countries'])     ? (int) $result['europe_countries']     : 0,
                'non_europe_countries' => is_numeric($result['non_europe_countries']) ? (int) $result['non_europe_countries'] : 0,
            ];
        } catch (\PDOException $e) {
            error_log("getEuropeVsNonEuropeStats Error: " . $e->getMessage());
            return ['europe_countries' => 0, 'non_europe_countries' => 0];
        }
    }

    /** @return array<int, array{name: string, count: int}> */
    public function getZoneStats(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT Zone as name, COUNT(*) as count
                FROM dossiers
                WHERE Zone IS NOT NULL AND Zone != ''
                GROUP BY Zone
                ORDER BY count DESC
            ");
            if ($stmt === false) return [];
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($results) ? array_map(fn($r) => [
                'name'  => is_string($r['name'])  ? $r['name']  : '',
                'count' => is_numeric($r['count']) ? (int) $r['count'] : 0,
            ], $results) : [];
        } catch (\PDOException $e) {
            error_log("getZoneStats Error: " . $e->getMessage());
            return [];
        }
    }

    // ===========================
    // Continent inference
    // ===========================

    private function inferContinent(string $pays, string $zone): ?string
    {
        if (strtolower($zone) === 'europe') return 'Europe';

        $mapping = [
            'Amérique'     => ['Canada', 'États-Unis', 'Mexique', 'Brésil', 'Argentine', 'Colombie', 'Chili', 'Pérou', 'Venezuela', 'Cuba'],
            'Asie'         => ['Japon', 'Chine', 'Corée du Sud', 'Inde', 'Thaïlande', 'Vietnam', 'Indonésie', 'Singapour', 'Malaisie', 'Philippines', 'Bangladesh', 'Pakistan'],
            'Océanie'      => ['Australie', 'Nouvelle-Zélande', 'Fidji', 'Papouasie-Nouvelle-Guinée'],
            'Afrique'      => ['Maroc', 'Tunisie', 'Algérie', 'Sénégal', 'Côte d\'Ivoire', 'Cameroun', 'Mali', 'Guinée', 'Madagascar', 'Mozambique', 'Tanzanie', 'Kenya', 'Ghana', 'Nigeria', 'Éthiopie'],
            'Moyen-Orient' => ['Turquie', 'Liban', 'Jordanie', 'Égypte', 'Arabie Saoudite', 'Émirats Arabes Unis', 'Qatar', 'Koweït', 'Israël', 'Iran', 'Irak'],
        ];

        foreach ($mapping as $continent => $countries) {
            if (in_array($pays, $countries, true)) return $continent;
        }
        return null;
    }

    // ===========================
    // CRUD Operations
    // ===========================

    /**
     * @return array<int, array<string, mixed>>
     */
    public function findAll(): array
    {
        try {
            $stmt = $this->db->query("
                SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives, status
                FROM dossiers
                ORDER BY Nom, Prenom
            ");
            return $stmt ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (\PDOException $e) {
            error_log("findAll Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<string, mixed>|null
     */
    public function findByNumEtu(string $numEtu): ?array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                       EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone,
                       IsComplete, PiecesJustificatives, status
                FROM dossiers
                WHERE NumEtu = :numetu
                LIMIT 1
            ");
            $stmt->execute([':numetu' => $numEtu]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return is_array($result) ? $result : null;
        } catch (\PDOException $e) {
            error_log("findByNumEtu Error: " . $e->getMessage());
            return null;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO dossiers (
                    NumEtu, Nom, Prenom, DateNaissance, Sexe, Adresse, CodePostal, Ville,
                    EmailPersonnel, EmailAMU, Telephone, CodeDepartement, Type, Zone, Continent,
                    IsComplete, PiecesJustificatives, status
                ) VALUES (
                    :NumEtu, :Nom, :Prenom, :DateNaissance, :Sexe, :Adresse, :CodePostal, :Ville,
                    :EmailPersonnel, :EmailAMU, :Telephone, :CodeDepartement, :Type, :Zone, :Continent,
                    0, :PiecesJustificatives, :status
                )
            ");
            return $stmt->execute([
                ':NumEtu'               => $data['NumEtu']               ?? null,
                ':Nom'                  => $data['Nom']                  ?? null,
                ':Prenom'               => $data['Prenom']               ?? null,
                ':DateNaissance'        => $data['DateNaissance']        ?? null,
                ':Sexe'                 => $data['Sexe']                 ?? null,
                ':Adresse'              => $data['Adresse']              ?? null,
                ':CodePostal'           => $data['CodePostal']           ?? null,
                ':Ville'                => $data['Ville']                ?? null,
                ':EmailPersonnel'       => $data['EmailPersonnel']       ?? null,
                ':EmailAMU'             => $data['EmailAMU']             ?? null,
                ':Telephone'            => $data['Telephone']            ?? null,
                ':CodeDepartement'      => $data['CodeDepartement']      ?? null,
                ':Type'                 => $data['Type']                 ?? null,
                ':Zone'                 => $data['Zone']                 ?? null,
                ':Continent' => $this->inferContinent(
                    is_string($data['Pays'] ?? null) ? $data['Pays'] : '',
                    is_string($data['Zone'] ?? null) ? $data['Zone'] : ''
                ),
                ':PiecesJustificatives' => $data['PiecesJustificatives'] ?? '{}',
                ':status'               => $data['status']               ?? 'depot',
            ]);
        } catch (\PDOException $e) {
            error_log("create Error: " . $e->getMessage());
            return false;
        }
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(string $numEtu, array $data): bool
    {
        try {
            $stmt = $this->db->prepare("
                UPDATE dossiers
                SET 
                    Nom                  = COALESCE(:Nom, Nom),
                    Prenom               = COALESCE(:Prenom, Prenom),
                    DateNaissance        = COALESCE(:DateNaissance, DateNaissance),
                    Sexe                 = COALESCE(:Sexe, Sexe),
                    Adresse              = COALESCE(:Adresse, Adresse),
                    CodePostal           = COALESCE(:CodePostal, CodePostal),
                    Ville                = COALESCE(:Ville, Ville),
                    EmailPersonnel       = COALESCE(:EmailPersonnel, EmailPersonnel),
                    EmailAMU             = COALESCE(:EmailAMU, EmailAMU),
                    Telephone            = COALESCE(:Telephone, Telephone),
                    CodeDepartement      = COALESCE(:CodeDepartement, CodeDepartement),
                    Type                 = COALESCE(:Type, Type),
                    Zone                 = COALESCE(:Zone, Zone),
                    PiecesJustificatives = COALESCE(:PiecesJustificatives, PiecesJustificatives),
                    status               = COALESCE(:status, status)
                WHERE NumEtu = :NumEtu
            ");
            $data[':NumEtu'] = $numEtu;
            return $stmt->execute($data);
        } catch (\PDOException $e) {
            error_log("update Error: " . $e->getMessage());
            return false;
        }
    }

    public function toggleCompleteStatus(string $numEtu): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT IsComplete FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numEtu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($current)) return false;

            $newStatus = (($current['IsComplete'] ?? 0) == 1) ? 0 : 1;
            $stmt = $this->db->prepare("UPDATE dossiers SET IsComplete = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $newStatus, ':numetu' => $numEtu]);
        } catch (\PDOException $e) {
            error_log("toggleCompleteStatus Error: " . $e->getMessage());
            return false;
        }
    }

    public function toggleStatus(string $numEtu): bool
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $stmt = $pdo->prepare("SELECT IsComplete FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numEtu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($current)) return false;

            $newStatus = (($current['IsComplete'] ?? 0) == 1) ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE dossiers SET IsComplete = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $newStatus, ':numetu' => $numEtu]);
        } catch (\PDOException $e) {
            error_log("Error toggling folder status: " . $e->getMessage());
            return false;
        }
    }

    // ===========================
    // Status management
    // ===========================

    public function setStatus(string $numEtu, string $status): bool
    {
        $allowed = ['depot', 'instruction', 'decision'];
        if (!in_array($status, $allowed, true)) return false;

        try {
            $stmt = $this->db->prepare("UPDATE dossiers SET status = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $status, ':numetu' => $numEtu]);
        } catch (\PDOException $e) {
            error_log("setStatus Error: " . $e->getMessage());
            return false;
        }
    }

    public function cycleStatus(string $numEtu): bool
    {
        try {
            $stmt = $this->db->prepare("SELECT status FROM dossiers WHERE NumEtu = :numetu");
            $stmt->execute([':numetu' => $numEtu]);
            $current = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!is_array($current)) return false;

            $statuses     = ['depot', 'instruction', 'decision'];
            $currentStatus = strtolower(trim($current['status'] ?? 'depot'));
            $currentIndex  = array_search($currentStatus, $statuses, true);
            $nextIndex     = ($currentIndex === false || $currentIndex === count($statuses) - 1) ? 0 : $currentIndex + 1;
            $nextStatus    = $statuses[$nextIndex];

            $stmt = $this->db->prepare("UPDATE dossiers SET status = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $nextStatus, ':numetu' => $numEtu]);
        } catch (\PDOException $e) {
            error_log("cycleStatus Error: " . $e->getMessage());
            return false;
        }
    }

    // ===========================
    // Pagination & search
    // ===========================

    /**
     * @param array<string, mixed> $filters
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int}
     */
    public function searchWithPagination(array $filters, int $page, int $perPage): array
    {
        $params          = [];
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
            foreach ($params as $key => $value) {
                $countStmt->bindValue(':' . $key, $value);
            }
            $countStmt->execute();
            $row        = $countStmt->fetch(PDO::FETCH_ASSOC);
            $totalCount = is_array($row) ? (int) ($row['total'] ?? 0) : 0;
        } catch (\PDOException $e) {
            error_log("searchWithPagination count Error: " . $e->getMessage());
        }

        $sql = "SELECT NumEtu, Nom, Prenom, EmailPersonnel as email, Telephone, Type, Zone,
                       DateNaissance, Sexe, Adresse, CodePostal, Ville, EmailAMU, CodeDepartement,
                       IsComplete, PiecesJustificatives, status
                FROM dossiers" . $whereConditions . " ORDER BY Nom ASC, Prenom ASC LIMIT :limit OFFSET :offset";

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(':' . $key, $value);
            }
            $stmt->bindValue(':limit',  $perPage,              PDO::PARAM_INT);
            $stmt->bindValue(':offset', ($page - 1) * $perPage, PDO::PARAM_INT);
            $stmt->execute();

            return [
                'data'       => $stmt->fetchAll(PDO::FETCH_ASSOC),
                'total'      => $totalCount,
                'totalPages' => $totalCount > 0 ? (int) ceil($totalCount / $perPage) : 0,
            ];
        } catch (\PDOException $e) {
            error_log("searchWithPagination fetch Error: " . $e->getMessage());
            return ['data' => [], 'total' => 0, 'totalPages' => 0];
        }
    }

    /**
     * @param array<int, array<string, mixed>> $dossiers
     */
    public function upsertMultiple(array $dossiers): int
    {
        $pdo = Database::getInstance()->getConnection();
        try {
            $pdo->beginTransaction();

            $stmt = $pdo->prepare("
                INSERT INTO dossiers (NumEtu, Nom, Prenom, EmailPersonnel, Telephone, Type, Zone, Continent, IsComplete, PiecesJustificatives) 
                VALUES (:num, :nom, :prenom, :email, :tel, :type, :zone, :continent, 0, '{}')
                ON DUPLICATE KEY UPDATE 
                    Nom            = VALUES(Nom),
                    Prenom         = VALUES(Prenom),
                    EmailPersonnel = VALUES(EmailPersonnel),
                    Telephone      = VALUES(Telephone),
                    Type           = VALUES(Type),
                    Zone           = VALUES(Zone),
                    Continent      = VALUES(Continent)
            ");

            $insertedRows = 0;
            foreach ($dossiers as $dossier) {
                $pays = is_string($dossier['Pays'] ?? null) ? $dossier['Pays'] : '';
                $zone = is_string($dossier['Zone'] ?? null) ? $dossier['Zone'] : '';
                $stmt->execute([
                    ':num'       => $dossier['NumEtu'],
                    ':nom'       => $dossier['Nom'],
                    ':prenom'    => $dossier['Prenom'],
                    ':email'     => $dossier['EmailPersonnel'],
                    ':tel'       => $dossier['Telephone'],
                    ':type'      => $dossier['Type'],
                    ':zone'      => $dossier['Zone'],
                    ':continent' => $this->inferContinent($pays, $zone),
                ]);
                $insertedRows++;
            }

            $pdo->commit();
            return $insertedRows;
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log("Import Error (PDO): " . $e->getMessage());
            return 0;
        }
    }
}
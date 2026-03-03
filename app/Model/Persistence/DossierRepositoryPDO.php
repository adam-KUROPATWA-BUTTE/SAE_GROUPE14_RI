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
 * PDO implementation of DossierRepositoryInterface.
 * Toutes les méthodes de statistiques acceptent un filtre ?string $mobilite
 * (null = tous | 'etude' | 'stage').
 */
class DossierRepositoryPDO implements DossierRepositoryInterface
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }


    /**
     * Valide et retourne le filtre mobilité ou null.
     */
    private function validMobilite(?string $mobilite): ?string
    {
        return in_array($mobilite, ['etude', 'stage'], true) ? $mobilite : null;
    }

    /**
     * Retourne la clause WHERE (ou vide) + les bindings PDO pour le filtre mobilité.
     *
     * @return array{clause: string, bindings: array<string, string>}
     */
    private function mobiliteWhere(?string $mobilite, bool $hasExistingWhere = false): array
    {
        $mobilite = $this->validMobilite($mobilite);

        if ($mobilite === null) {
            return ['clause' => '', 'bindings' => []];
        }

        $keyword = $hasExistingWhere ? 'AND' : 'WHERE';
        return [
            'clause'   => "$keyword Mobilite = :mobilite",
            'bindings' => ['mobilite' => $mobilite],
        ];
    }

    /**
     * Prépare et exécute une requête avec des bindings optionnels.
     */
    private function run(string $sql, array $bindings = []): \PDOStatement|false
    {
        if (empty($bindings)) {
            return $this->db->query($sql);
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute($bindings);
        return $stmt;
    }


    public function getGlobalStats(): DossierStats
    {
        return $this->getDossierStats();
    }

    public function getDossierStats(?string $mobilite = null): DossierStats
    {
        try {
            ['clause' => $where, 'bindings' => $bindings] = $this->mobiliteWhere($mobilite);

            $stmt = $this->run("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN IsComplete = 1 THEN 1 ELSE 0 END) AS completed
                FROM dossiers
                $where
            ", $bindings);

            $result    = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            $total     = is_numeric($result['total']     ?? 0) ? (int) $result['total']     : 0;
            $completed = is_numeric($result['completed'] ?? 0) ? (int) $result['completed'] : 0;

            return new DossierStats($total, $completed);
        } catch (\PDOException $e) { return new DossierStats(0, 0); }
    }

    public function getGenderStats(?string $mobilite = null): GenderStats
    {
        try {
            $mobiliteValid = $this->validMobilite($mobilite);
            $mobiliteClause = $mobiliteValid ? "AND Mobilite = :mobilite" : '';
            $bindings       = $mobiliteValid ? ['mobilite' => $mobiliteValid] : [];

            $stmt = $this->run("
                SELECT
                    SUM(CASE WHEN LOWER(Sexe) IN ('m','homme','male','masculin')             THEN 1 ELSE 0 END) AS male,
                    SUM(CASE WHEN LOWER(Sexe) IN ('f','femme','female','féminin','feminin')  THEN 1 ELSE 0 END) AS female
                FROM dossiers
                WHERE Sexe IS NOT NULL AND Sexe != ''
                $mobiliteClause
            ", $bindings);

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
     * @return array<int, array{name: string, count: int}>
     */
    public function getTopCountries(int $limit, ?string $mobilite = null): array
    {
        try {
            $mobiliteValid  = $this->validMobilite($mobilite);
            $mobiliteClause = $mobiliteValid ? "AND Mobilite = :mobilite" : '';
            $bindings       = $mobiliteValid ? ['mobilite' => $mobiliteValid] : [];

            $stmt = $this->db->prepare("
                SELECT Pays AS name, COUNT(*) AS count
                FROM dossiers
                WHERE Pays IS NOT NULL AND Pays != ''
                $mobiliteClause
                GROUP BY Pays
                ORDER BY count DESC
                LIMIT :limit
            ");

            foreach ($bindings as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("getTopCountries Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getDepartmentStats(int $limit, ?string $mobilite = null): array
    {
        try {
            $mobiliteValid  = $this->validMobilite($mobilite);
            $mobiliteClause = $mobiliteValid ? "AND Mobilite = :mobilite" : '';
            $bindings       = $mobiliteValid ? ['mobilite' => $mobiliteValid] : [];

            $stmt = $this->db->prepare("
                SELECT CodeDepartement AS name, COUNT(*) AS count
                FROM dossiers
                WHERE CodeDepartement IS NOT NULL AND CodeDepartement != ''
                $mobiliteClause
                GROUP BY CodeDepartement
                ORDER BY count DESC
                LIMIT :limit
            ");

            foreach ($bindings as $key => $value) {
                $stmt->bindValue(":$key", $value);
            }
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (\PDOException $e) {
            error_log("getDepartmentStats Error: " . $e->getMessage());
            return [];
        }
    }

    /**
     * @return array{incoming: int, outgoing: int}
     */
    public function getIncomingOutgoingStats(?string $mobilite = null): array
    {
        try {
            ['clause' => $where, 'bindings' => $bindings] = $this->mobiliteWhere($mobilite);

            $stmt = $this->run("
                SELECT
                    SUM(CASE WHEN LOWER(Type) = 'entrant' THEN 1 ELSE 0 END) AS incoming,
                    SUM(CASE WHEN LOWER(Type) = 'sortant' THEN 1 ELSE 0 END) AS outgoing
                FROM dossiers
                $where
            ", $bindings);

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

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getContinentStats(?string $mobilite = null): array
    {
        try {
            $mobiliteValid  = $this->validMobilite($mobilite);
            $mobiliteClause = $mobiliteValid ? "AND Mobilite = :mobilite" : '';
            $bindings       = $mobiliteValid ? ['mobilite' => $mobiliteValid] : [];

            $stmt = $this->run("
                SELECT Continent AS name, COUNT(*) AS count
                FROM dossiers
                WHERE Continent IS NOT NULL AND Continent != ''
                $mobiliteClause
                GROUP BY Continent
                ORDER BY count DESC
            ", $bindings);

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

    /**
     * @return array{europe_countries: int, non_europe_countries: int}
     */
    public function getEuropeVsNonEuropeStats(?string $mobilite = null): array
    {
        try {
            $mobiliteValid  = $this->validMobilite($mobilite);
            $mobiliteClause = $mobiliteValid ? "AND Mobilite = :mobilite" : '';
            $bindings       = $mobiliteValid ? ['mobilite' => $mobiliteValid] : [];

            $stmt = $this->run("
                SELECT
                    COUNT(DISTINCT CASE WHEN LOWER(Zone) = 'europe'      THEN Pays END) AS europe_countries,
                    COUNT(DISTINCT CASE WHEN LOWER(Zone) = 'hors_europe' THEN Pays END) AS non_europe_countries
                FROM dossiers
                WHERE Pays IS NOT NULL AND Pays != ''
                $mobiliteClause
            ", $bindings);

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

    /**
     * @return array<int, array{name: string, count: int}>
     */
    public function getZoneStats(?string $mobilite = null): array
    {
        try {
            $mobiliteValid  = $this->validMobilite($mobilite);
            $mobiliteClause = $mobiliteValid ? "AND Mobilite = :mobilite" : '';
            $bindings       = $mobiliteValid ? ['mobilite' => $mobiliteValid] : [];

            $stmt = $this->run("
                SELECT Zone AS name, COUNT(*) AS count
                FROM dossiers
                WHERE Zone IS NOT NULL AND Zone != ''
                $mobiliteClause
                GROUP BY Zone
                ORDER BY count DESC
            ", $bindings);

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

    // ===========================================================
    // Continent inference
    // ===========================================================

    private function inferContinent(string $pays, string $zone): ?string
    {
        if (strtolower($zone) === 'europe') return 'Europe';

        $mapping = [
            'Amérique'     => ['Canada', 'États-Unis', 'Mexique', 'Brésil', 'Argentine', 'Colombie', 'Chili', 'Pérou', 'Venezuela', 'Cuba'],
            'Asie'         => ['Japon', 'Chine', 'Corée du Sud', 'Inde', 'Thaïlande', 'Vietnam', 'Indonésie', 'Singapour', 'Malaisie', 'Philippines', 'Bangladesh', 'Pakistan'],
            'Océanie'      => ['Australie', 'Nouvelle-Zélande', 'Fidji', 'Papouasie-Nouvelle-Guinée'],
            'Afrique'      => ['Maroc', 'Tunisie', 'Algérie', 'Sénégal', "Côte d'Ivoire", 'Cameroun', 'Mali', 'Guinée', 'Madagascar', 'Mozambique', 'Tanzanie', 'Kenya', 'Ghana', 'Nigeria', 'Éthiopie'],
            'Moyen-Orient' => ['Turquie', 'Liban', 'Jordanie', 'Égypte', 'Arabie Saoudite', 'Émirats Arabes Unis', 'Qatar', 'Koweït', 'Israël', 'Iran', 'Irak'],
        ];

        foreach ($mapping as $continent => $countries) {
            if (in_array($pays, $countries, true)) return $continent;
        }
        return null;
    }

    // ===========================================================
    // CRUD
    // ===========================================================

    /**
     * @return array<int, array<string, mixed>>
     */
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

    /**
     * @return array<string, mixed>|null
     */
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

    /**
     * @param array<string, mixed> $data
     */
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

    /**
     * @param array<string, mixed> $data
     */
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
        } catch (\PDOException $e) {
            error_log("Update Error: " . $e->getMessage());
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
            $currentIndex  = array_search($currentStatus, $statuses, true);
            $nextIndex     = ($currentIndex === false || $currentIndex === count($statuses) - 1) ? 0 : $currentIndex + 1;

            $stmt = $this->db->prepare("UPDATE dossiers SET status = :status WHERE NumEtu = :numetu");
            return $stmt->execute([':status' => $statuses[$nextIndex], ':numetu' => $numEtu]);
        } catch (\PDOException $e) {
            error_log("cycleStatus Error: " . $e->getMessage());
            return false;
        }
    }

    // ===========================================================
    // Pagination & search
    // ===========================================================

    /**
     * @param array<string, mixed> $filters
     * @return array{data: array<int, array<string, mixed>>, total: int, totalPages: int}
     */
    public function searchWithPagination(array $filters, int $page, int $perPage): array
    {
        $params = [];
        $where  = " WHERE 1=1";

        if (isset($filters['complet']) && $filters['complet'] !== 'all') {
            $where .= ($filters['complet'] == '1') ? " AND IsComplete = 1" : " AND (IsComplete = 0 OR IsComplete IS NULL)";
        }
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $where .= " AND LOWER(Type) = LOWER(:type)";
            $params['type'] = $filters['type'];
        }
        if (!empty($filters['mobilite']) && $filters['mobilite'] !== 'all') {
            $valid = $this->validMobilite($filters['mobilite']);
            if ($valid) {
                $where .= " AND Mobilite = :mobilite";
                $params['mobilite'] = $valid;
            }
        }
        if (!empty($filters['zone']) && $filters['zone'] !== 'all') {
            $where .= " AND LOWER(Zone) = LOWER(:zone)";
            $params['zone'] = $filters['zone'];
        }
        if (!empty($filters['composante']) && $filters['composante'] !== 'all') {
            $where .= " AND LOWER(Composante) LIKE LOWER(:composante)";
            $params['composante'] = '%' . $filters['composante'] . '%';
        }
        if (!empty($filters['accord']) && $filters['accord'] !== 'all') {
            $where .= " AND LOWER(Composante) LIKE LOWER(:accord)";
            $params['accord'] = '%' . $filters['accord'] . '%';
        }
        if (!empty($filters['search'])) {
            $s = $filters['search'] . '%';
            $where .= " AND (Nom LIKE :s1 OR Prenom LIKE :s2 OR NumEtu LIKE :s3 OR EmailPersonnel LIKE :s4)";
            $params['s1'] = $s; $params['s2'] = $s; $params['s3'] = $s; $params['s4'] = $s;
        }

        $totalCount = 0;
        try {
            $countStmt = $this->db->prepare("SELECT COUNT(*) as total FROM dossiers" . $where);
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
                FROM dossiers " . $where . " ORDER BY Nom ASC, Prenom ASC" . $limitClause;

        try {
            $stmt = $this->db->prepare($sql);
            foreach ($params as $key => $value) {
                $stmt->bindValue(":$key", $value);
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

            // 1. Préparation de la requête pour la table ETUDIANTS
            $stmtEtu = $pdo->prepare("
                INSERT INTO etudiants (
                    numetu, nom, prenom, email, password, departement, campus, annee_etude, type_etudiant, telephone, adresse, code_postal, ville, sexe, date_naissance, created_at
                ) VALUES (
                    :numetu, :nom, :prenom, :email, :pass, :dept, :campus, :annee, :type, :tel, :adr, :cp, :ville, :sexe, :dob, NOW()
                ) ON DUPLICATE KEY UPDATE 
                    nom = VALUES(nom), prenom = VALUES(prenom), email = VALUES(email), 
                    departement = VALUES(departement), campus = VALUES(campus), type_etudiant = VALUES(type_etudiant)
            ");

            // 2. Préparation de la requête pour la table DOSSIERS
            $stmtDossier = $pdo->prepare("
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

            $count = 0;
            foreach ($dossiers as $d) {
                // ÉTAPE A : CRÉATION/MAJ ÉTUDIANT
                $numEtu = $d['NumEtu'];
                $email  = !empty($d['EmailAMU']) ? $d['EmailAMU'] : ($d['EmailPersonnel'] ?? '');
                $hashedPass = password_hash($numEtu, PASSWORD_DEFAULT);

                $stmtEtu->execute([
                    ':numetu' => $numEtu,
                    ':nom'    => $d['Nom'],
                    ':prenom' => $d['Prenom'],
                    ':email'  => $email,
                    ':pass'   => $hashedPass,
                    ':dept'   => $d['CodeDepartement'] ?? '',
                    ':campus' => $d['Campus'] ?? '',
                    ':annee'  => $d['NiveauEtude'] ?? '',
                    ':type'   => $d['Type'] ?? 'sortant',
                    ':tel'    => $d['Telephone'] ?? '',
                    ':adr'    => $d['Adresse'] ?? '',
                    ':cp'     => $d['CodePostal'] ?? '',
                    ':ville'  => $d['Ville'] ?? '',
                    ':sexe'   => $d['Sexe'] ?? null,
                    ':dob'    => $d['DateNaissance'] ?? null,
                ]);

                // ÉTAPE B : CRÉATION/MAJ DOSSIER
                $stmtDossier->execute([
                    ':NumEtu'             => $numEtu,
                    ':Nom'                => $d['Nom'],
                    ':Prenom'             => $d['Prenom'],
                    ':DateNaissance'      => $d['DateNaissance'] ?: null,
                    ':Sexe'               => $d['Sexe'] ?: null,
                    ':Adresse'            => $d['Adresse'] ?? '',
                    ':CodePostal'         => $d['CodePostal'] ?? '',
                    ':Ville'              => $d['Ville'] ?? '',
                    ':EmailPersonnel'     => $d['EmailPersonnel'] ?? '',
                    ':EmailAMU'           => $d['EmailAMU'] ?? '',
                    ':Telephone'          => $d['Telephone'] ?? '',
                    ':CodeDepartement'    => $d['CodeDepartement'] ?? '',
                    ':Composante'         => $d['Composante'] ?? '',
                    ':Type'               => !empty($d['Type']) ? $d['Type'] : 'sortant',
                    ':Zone'               => !empty($d['Zone']) ? $d['Zone'] : 'europe',
                    ':Pays'               => $d['Pays'] ?? '',
                    ':Campus'             => $d['Campus'] ?? '',
                    ':Discipline'         => $d['Discipline'] ?? '',
                    ':NiveauEtude'        => $d['NiveauEtude'] ?? '',
                    ':Formation'          => $d['Formation'] ?? '',
                    ':MoyenneBac'         => $d['MoyenneBac'] ?? '',
                    ':MoyenneSansBac'     => $d['MoyenneSansBac'] ?? '',
                    ':AvisDRI'            => $d['AvisDRI'] ?? '',
                    ':DateDebut'          => $d['DateDebut'] ?? '',
                    ':MobiliteAnterieure' => $d['MobiliteAnterieure'] ?? '',
                ]);

                $count++;
            }

            $pdo->commit();
            return $count;
        } catch (\PDOException $e) {
            if ($pdo->inTransaction()) { $pdo->rollBack(); }
            error_log("DB Upsert Error: " . $e->getMessage());
            return 0;
        }
    }
}

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

    public function getDossierStats(): DossierStats
    {
        try {
            $stmt = $this->db->query("
                SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN IsComplete = 1 THEN 1 ELSE 0 END) as completed
                FROM dossiers
            ");

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return new DossierStats(
                (int) ($result['total'] ?? 0),
                (int) ($result['completed'] ?? 0)
            );
        } catch (\PDOException $e) {
            error_log("getDossierStats Error: " . $e->getMessage());
            return new DossierStats(0, 0);
        }
    }

    public function getTopCountries(int $limit): array
    {
        try {
            $stmt = $this->db->prepare("
            SELECT 
                Pays as name, 
                COUNT(*) as count 
            FROM dossiers 
            WHERE Pays IS NOT NULL AND Pays != ''
            GROUP BY Pays 
            ORDER BY count DESC 
            LIMIT :limit
        ");

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return !empty($results) ? $results : [];
        } catch (\PDOException $e) {
            error_log("getTopCountries Error: " . $e->getMessage());
            return [];
        }
    }

    public function getGenderStats(): GenderStats
    {
        try {
            // Directement depuis la table dossiers
            $stmt = $this->db->query("
                SELECT 
                    SUM(CASE 
                        WHEN LOWER(Sexe) IN ('m', 'homme', 'male', 'masculin') THEN 1 
                        ELSE 0 
                    END) as male,
                    SUM(CASE 
                        WHEN LOWER(Sexe) IN ('f', 'femme', 'female', 'féminin', 'feminin') THEN 1 
                        ELSE 0 
                    END) as female
                FROM dossiers
                WHERE Sexe IS NOT NULL AND Sexe != ''
            ");

            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            return new GenderStats(
                (int) ($result['male'] ?? 0),
                (int) ($result['female'] ?? 0)
            );
        } catch (\PDOException $e) {
            error_log("getGenderStats Error: " . $e->getMessage());
            return new GenderStats(0, 0);
        }
    }

    public function getDepartmentStats(int $limit): array
    {
        try {
            $stmt = $this->db->prepare("
                SELECT 
                    CodeDepartement as name, 
                    COUNT(*) as count 
                FROM dossiers 
                WHERE CodeDepartement IS NOT NULL AND CodeDepartement != ''
                GROUP BY CodeDepartement 
                ORDER BY count DESC 
                LIMIT :limit
            ");

            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();

            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

            return !empty($results) ? $results : [];
        } catch (\PDOException $e) {
            error_log("getDepartmentStats Error: " . $e->getMessage());
            return [];
        }
    }
}
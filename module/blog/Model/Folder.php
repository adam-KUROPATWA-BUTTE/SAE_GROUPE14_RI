<?php
require_once __DIR__ . '/../config/database.php';
class Dossier
{
    /**
     * Récupère tous les dossiers avec leurs informations
     */
    public static function getAll()
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                e.numetu,
                e.nom,
                e.prenom,
                e.email,
                d.id as dossier_id,
                d.statut,
                d.date_validation,
                COUNT(DISTINCT tp.id) as total_pieces,
                COUNT(DISTINCT CASE WHEN pf.statut = 'valide' THEN pf.id END) as pieces_fournies,
                MAX(r.date_relance) as date_derniere_relance
            FROM etudiants e
            LEFT JOIN dossiers d ON e.id = d.etudiant_id
            CROSS JOIN types_pieces tp WHERE tp.obligatoire = TRUE
            LEFT JOIN pieces_fournies pf ON d.id = pf.dossier_id AND pf.type_piece_id = tp.id
            LEFT JOIN relances r ON d.id = r.dossier_id
            GROUP BY e.id, e.numetu, e.nom, e.prenom, e.email, d.id, d.statut, d.date_validation
            ORDER BY e.nom, e.prenom
        ";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            
            $dossiers = [];
            while ($row = $stmt->fetch()) {
                $dossiers[] = [
                    'numetu' => $row['numetu'],
                    'nom' => $row['nom'],
                    'prenom' => $row['prenom'],
                    'email' => $row['email'],
                    'dossier_id' => $row['dossier_id'],
                    'total_pieces' => (int)$row['total_pieces'],
                    'pieces_fournies' => (int)$row['pieces_fournies'],
                    'date_derniere_relance' => $row['date_derniere_relance'] ?? 'Jamais',
                    'valide' => $row['statut'] === 'valide'
                ];
            }
            
            return $dossiers;
        } catch (PDOException $e) {
            error_log("Erreur getAll dossiers : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Valide un dossier complet
     */
    public static function valider($numetu, $adminId)
    {
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Récupérer le dossier et vérifier qu'il est complet
            $sql = "
                SELECT 
                    d.id as dossier_id,
                    d.statut,
                    COUNT(DISTINCT tp.id) as total_pieces,
                    COUNT(DISTINCT CASE WHEN pf.statut = 'valide' THEN pf.id END) as pieces_fournies
                FROM etudiants e
                JOIN dossiers d ON e.id = d.etudiant_id
                CROSS JOIN types_pieces tp WHERE tp.obligatoire = TRUE
                LEFT JOIN pieces_fournies pf ON d.id = pf.dossier_id AND pf.type_piece_id = tp.id
                WHERE e.numetu = :numetu
                GROUP BY d.id, d.statut
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute(['numetu' => $numetu]);
            $dossier = $stmt->fetch();
            
            if (!$dossier) {
                throw new Exception("Dossier introuvable");
            }
            
            if ($dossier['statut'] === 'valide') {
                throw new Exception("Le dossier est déjà validé");
            }
            
            if ($dossier['total_pieces'] != $dossier['pieces_fournies']) {
                throw new Exception("Le dossier n'est pas complet");
            }
            
            // Mettre à jour le statut du dossier
            $sql = "
                UPDATE dossiers 
                SET statut = 'valide',
                    date_validation = NOW(),
                    valide_par = :admin_id
                WHERE id = :dossier_id
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'admin_id' => $adminId,
                'dossier_id' => $dossier['dossier_id']
            ]);
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur validation dossier : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère un dossier par numéro étudiant
     */
    public static function getByNumetu($numetu)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                e.*,
                d.id as dossier_id,
                d.statut,
                d.date_validation,
                d.date_creation
            FROM etudiants e
            LEFT JOIN dossiers d ON e.id = d.etudiant_id
            WHERE e.numetu = :numetu
        ";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['numetu' => $numetu]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getByNumetu : " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Ajoute une relance pour un dossier
     */
    public static function ajouterRelance($dossierId, $message, $adminId)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            INSERT INTO relances (dossier_id, message, envoye_par, date_relance)
            VALUES (:dossier_id, :message, :admin_id, NOW())
        ";
        
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute([
                'dossier_id' => $dossierId,
                'message' => $message,
                'admin_id' => $adminId
            ]);
        } catch (PDOException $e) {
            error_log("Erreur ajouterRelance : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les pièces d'un dossier
     */
    public static function getPieces($dossierId)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                tp.id,
                tp.nom,
                tp.description,
                tp.obligatoire,
                pf.id as piece_fournie_id,
                pf.fichier_nom,
                pf.fichier_path,
                pf.date_soumission,
                pf.statut as piece_statut
            FROM types_pieces tp
            LEFT JOIN pieces_fournies pf ON tp.id = pf.type_piece_id AND pf.dossier_id = :dossier_id
            WHERE tp.obligatoire = TRUE
            ORDER BY tp.nom
        ";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute(['dossier_id' => $dossierId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Erreur getPieces : " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Crée un nouveau dossier pour un étudiant
     */
    public static function creerDossier($etudiantId)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            INSERT INTO dossiers (etudiant_id, statut, date_creation)
            VALUES (:etudiant_id, 'en_cours', NOW())
        ";
        
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute(['etudiant_id' => $etudiantId]);
        } catch (PDOException $e) {
            error_log("Erreur creerDossier : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Ajoute un étudiant
     */
    public static function ajouterEtudiant($numetu, $nom, $prenom, $email, $telephone = null)
    {
        $db = Database::getInstance()->getConnection();
        
        try {
            $db->beginTransaction();
            
            // Vérifier si l'étudiant existe déjà
            $sql = "SELECT id FROM etudiants WHERE numetu = :numetu";
            $stmt = $db->prepare($sql);
            $stmt->execute(['numetu' => $numetu]);
            
            if ($stmt->fetch()) {
                throw new Exception("Un étudiant avec ce numéro existe déjà");
            }
            
            // Insérer l'étudiant
            $sql = "
                INSERT INTO etudiants (numetu, nom, prenom, email, telephone, created_at)
                VALUES (:numetu, :nom, :prenom, :email, :telephone, NOW())
            ";
            
            $stmt = $db->prepare($sql);
            $stmt->execute([
                'numetu' => $numetu,
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'telephone' => $telephone
            ]);
            
            $etudiantId = $db->lastInsertId();
            
            // Créer automatiquement un dossier pour cet étudiant
            self::creerDossier($etudiantId);
            
            $db->commit();
            return true;
            
        } catch (Exception $e) {
            $db->rollBack();
            error_log("Erreur ajouterEtudiant : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Supprime un dossier (et l'étudiant associé)
     */
    public static function supprimerDossier($numetu)
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "DELETE FROM etudiants WHERE numetu = :numetu";
        
        try {
            $stmt = $db->prepare($sql);
            return $stmt->execute(['numetu' => $numetu]);
        } catch (PDOException $e) {
            error_log("Erreur supprimerDossier : " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Récupère les statistiques des dossiers
     */
    public static function getStatistiques()
    {
        $db = Database::getInstance()->getConnection();
        
        $sql = "
            SELECT 
                COUNT(*) as total_dossiers,
                SUM(CASE WHEN d.statut = 'en_cours' THEN 1 ELSE 0 END) as en_cours,
                SUM(CASE WHEN d.statut = 'complet' THEN 1 ELSE 0 END) as complets,
                SUM(CASE WHEN d.statut = 'valide' THEN 1 ELSE 0 END) as valides
            FROM dossiers d
        ";
        
        try {
            $stmt = $db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("Erreur getStatistiques : " . $e->getMessage());
            return [
                'total_dossiers' => 0,
                'en_cours' => 0,
                'complets' => 0,
                'valides' => 0
            ];
        }
    }
}
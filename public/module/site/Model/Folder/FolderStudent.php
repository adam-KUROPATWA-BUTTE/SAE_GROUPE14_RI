<?php
namespace Model\Folder;

use Database;
use PDO;

class FolderStudent
{
    public static function getMyFolder(int $studentId): ?array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT 
                    e.*,
                    (SELECT COUNT(*) FROM documents WHERE etudiant_id = e.id) as total_pieces,
                    (SELECT COUNT(*) FROM documents WHERE etudiant_id = e.id AND fichier IS NOT NULL) as pieces_fournies
                FROM etudiants e
                WHERE e.id = :student_id
            ");
            $stmt->execute(['student_id' => $studentId]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: null;
        } catch (\PDOException $e) {
            error_log("Erreur getMyFolder: " . $e->getMessage());
            return null;
        }
    }

    public static function getMyDocuments(int $studentId): array
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                SELECT * FROM documents 
                WHERE etudiant_id = :student_id
                ORDER BY type_document
            ");
            $stmt->execute(['student_id' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (\PDOException $e) {
            error_log("Erreur getMyDocuments: " . $e->getMessage());
            return [];
        }
    }

    public static function uploadDocument(int $studentId, string $type, string $filename): bool
    {
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("
                UPDATE documents 
                SET fichier = :filename, uploaded_at = NOW()
                WHERE etudiant_id = :student_id AND type_document = :type
            ");
            return $stmt->execute([
                'student_id' => $studentId,
                'type' => $type,
                'filename' => $filename
            ]);
        } catch (\PDOException $e) {
            error_log("Erreur uploadDocument: " . $e->getMessage());
            return false;
        }
    }
}
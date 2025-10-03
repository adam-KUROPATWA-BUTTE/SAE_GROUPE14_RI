<?php
require_once __DIR__ . '/config/database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    // Lire le fichier SQL
    $sql = file_get_contents(__DIR__ . '/database/schema.sql');
    
    // Exécuter les requêtes
    $db->exec($sql);
    
    echo "✅ Base de données créée avec succès !";
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
}
<?php

require_once __DIR__ . '/../../vendor/autoload.php'; // ou require manuellement vos classes si pas de composer

use Service\Email\EmailReminderService;
use Database;

define('DAYS_BEFORE_RELAY', 7); // nombre de jours avant de renvoyer une relance

$pdo = Database::getInstance()->getConnection();

try {
    // Récupération des étudiants/dossiers incomplets
    // Adapté à ton schéma (table etudiants avec colonne IsComplete)
    $sql = "SELECT NumEtu, Nom, Prenom, EmailAMU, EmailPersonnel FROM etudiants WHERE IsComplete = 0";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$rows) {
        echo "[" . date('Y-m-d H:i:s') . "] Aucun étudiant incomplet trouvé.\n";
        exit(0);
    }

    foreach ($rows as $r) {
        $numEtu = $r['NumEtu'];
        $studentName = trim(($r['Prenom'] ?? '') . ' ' . ($r['Nom'] ?? ''));
        $email = $r['EmailAMU'] ?: $r['EmailPersonnel'] ?: null;

        if (empty($email)) {
            error_log("cron_relances: étudiant {$numEtu} ignoré (aucun email)");
            continue;
        }

        // Vérifier s'il y a eu une relance dans les derniers DAYS_BEFORE_RELAY jours
        // Ici on suppose que la colonne relances.dossier_id contient NumEtu si vous n'avez pas table dossiers.
        // Adapte dossier_id => id dossier si nécessaire.
        $checkSql = "SELECT 1 FROM relances WHERE dossier_id = :dossier_id AND date_relance >= (NOW() - INTERVAL :days DAY) LIMIT 1";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':dossier_id', $numEtu);
        $checkStmt->bindValue(':days', DAYS_BEFORE_RELAY, PDO::PARAM_INT);
        $checkStmt->execute();
        $already = (bool)$checkStmt->fetchColumn();

        if ($already) {
            error_log("cron_relances: étudiant {$numEtu} - relance déjà envoyée dans les " . DAYS_BEFORE_RELAY . " derniers jours, skip.");
            continue;
        }

        // Récupérer la liste des pièces manquantes si tu peux (ici on laisse vide ou on met un exemple)
        $itemsToComplete = [
            // récupère les vrais types depuis table documents si tu veux
            // 'Copie de la carte d\'identité',
            // 'Justificatif de domicile'
        ];

        // Envoi du mail
        $sent = EmailReminderService::sendRelance($email, (int)$numEtu, $studentName, $itemsToComplete);

        if ($sent) {
            // Insérer une relance dans la table relances
            $message = "Relance automatique envoyée à {$email}";
            $insSql = "INSERT INTO relances (dossier_id, message, envoye_par) VALUES (:dossier_id, :message, NULL)";
            $insStmt = $pdo->prepare($insSql);
            $insStmt->execute([
                ':dossier_id' => $numEtu,
                ':message' => $message
            ]);

            error_log("cron_relances: étudiant {$numEtu} - email envoyé à {$email}");
        } else {
            error_log("cron_relances: étudiant {$numEtu} - échec envoi à {$email}");
        }
    }

    echo "[" . date('Y-m-d H:i:s') . "] Traitement terminé.\n";
} catch (Exception $e) {
    error_log("cron_relances: exception - " . $e->getMessage());
    echo "Erreur: " . $e->getMessage() . PHP_EOL;
    exit(1);
}
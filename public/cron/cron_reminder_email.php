<?php

/**
 * cron_reminder_email.php
 *
 * Exécuter en CLI :
 *   php public/cron/cron_reminder_email.php        # envoi réel
 *   php public/cron/cron_reminder_email.php --dry-run   # simulation (pas d'envoi)
 *
 * Placez ce fichier dans le dossier public/cron/ de votre projet.
 */

// require project autoloader and Database class (paths relative to this file)
require_once __DIR__ . '/../../Autoloader.php';
require_once __DIR__ . '/../../Database.php';

// try to load vendor/autoload (to use vlucas/phpdotenv if present)
$projectRoot = realpath(__DIR__ . '/../../');
if ($projectRoot !== false && file_exists($projectRoot . '/vendor/autoload.php')) {
    require_once $projectRoot . '/vendor/autoload.php';

    // load ..env into $_ENV for CLI if present
    if (file_exists($projectRoot . '/.env')) {
        try {
            $dot = Dotenv\Dotenv::createImmutable($projectRoot);
            $dot->load();
        } catch (Exception $e) {
            error_log("Unable to load ..env: " . $e->getMessage());
        }
    }
}

// Import namespaced classes that the autoloader maps
use Service\Email\EmailReminderService;

define('DAYS_BEFORE_RELAY', 7); // nombre de jours avant de renvoyer une relance

$dryRun = (isset($argv) && in_array('--dry-run', $argv, true));

try {
    // Create DB connection via your singleton Database class
    $pdo = Database::getInstance()->getConnection();

    // Lire les dossiers incomplets depuis la table `dossiers`
    $sql  = "SELECT NumEtu, Nom, Prenom, EmailAMU, EmailPersonnel FROM dossiers WHERE IsComplete = 0";
    $stmt = $pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "[" . date('Y-m-d H:i:s') . "] Aucun dossier incomplet trouvé.\n";
        exit(0);
    }

    foreach ($rows as $r) {
        $numEtu      = $r['NumEtu']; // reste string (ex: 'k2025002')
        $studentName = trim(($r['Prenom'] ?? '') . ' ' . ($r['Nom'] ?? ''));
        $email       = $r['EmailAMU'] ?: $r['EmailPersonnel'] ?: null;

        if (empty($email)) {
            error_log("cron_relances: dossier {$numEtu} ignoré (aucun email)");
            continue;
        }

        // Vérifier s'il y a eu une relance pour ce NumEtu dans les derniers DAYS_BEFORE_RELAY jours
        $checkSql = "SELECT 1 FROM relances WHERE dossier_id = :dossier_id AND date_relance >= (NOW() - INTERVAL :days DAY) LIMIT 1";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':dossier_id', $numEtu, PDO::PARAM_STR);
        $checkStmt->bindValue(':days', DAYS_BEFORE_RELAY, PDO::PARAM_INT);
        $checkStmt->execute();
        $already = (bool) $checkStmt->fetchColumn();

        if ($already) {
            error_log("cron_relances: dossier {$numEtu} - relance déjà envoyée dans les " . DAYS_BEFORE_RELAY . " derniers jours, skip.");
            continue;
        }

        // Construire la liste des pièces manquantes si vous avez la logique (laisser vide sinon)
        $itemsToComplete = [];

        if ($dryRun) {
            echo "[" . date('Y-m-d H:i:s') . "] Dry-run: would send to {$email} for dossier {$numEtu} ({$studentName})\n";
            continue;
        }

        // Envoi du mail (numEtu passé en string)
        $sent = EmailReminderService::sendRelance($email, $numEtu, $studentName, $itemsToComplete);

        // --- Remplacer par ce bloc (insertion avec dossier_id = NULL) ---
        if ($sent) {
            $message = "Relance automatique envoyée à {$email} pour NumEtu={$numEtu}";

            $insSql = "INSERT INTO relances (dossier_id, message, envoye_par) VALUES (:dossier_id, :message, NULL)";
            $insStmt = $pdo->prepare($insSql);
            $insStmt->bindValue(':dossier_id', $numEtu, PDO::PARAM_STR);
            $insStmt->bindValue(':message', $message, PDO::PARAM_STR);
            $insStmt->execute();

            error_log("cron_relances: dossier {$numEtu} - email envoyé à {$email}");
            echo "[" . date('Y-m-d H:i:s') . "] Sent to {$email} (NumEtu: {$numEtu})\n";
        } else {
            error_log("cron_relances: dossier {$numEtu} - échec envoi à {$email}");
            echo "[" . date('Y-m-d H:i:s') . "] Failed to send to {$email} (NumEtu: {$numEtu})\n";
        }

        // Optionnel : pause courte pour ne pas surcharger le relay
        usleep(150000); // 150ms
    }

    echo "[" . date('Y-m-d H:i:s') . "] Traitement terminé.\n";
    exit(0);
} catch (Exception $e) {
    error_log("cron_relances: exception - " . $e->getMessage());
    echo "Erreur: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

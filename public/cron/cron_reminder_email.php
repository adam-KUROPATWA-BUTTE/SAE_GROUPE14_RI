<?php

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable Generic.Files.LineLength

/**
 * cron_reminder_email.php
 *
 * Exécuter en CLI :
 * php cron/cron_reminder_email.php        # envoi réel
 * php cron/cron_reminder_email.php --dry-run   # simulation (pas d'envoi)
 */

// Calcul de la racine
$projectRoot = realpath(__DIR__ . '/..');

// CORRECTION PHPSTAN : Si realpath échoue (renvoie false), on arrête tout de suite.
// Cela garantit que $projectRoot est une "string" pour la suite du code.
if ($projectRoot === false) {
    die("Erreur critique : Impossible de localiser la racine du projet.\n");
}

// On charge la classe Database
require_once $projectRoot . '/Database.php';

// On charge l'autoloader de Composer
if (file_exists($projectRoot . '/vendor/autoload.php')) {
    require_once $projectRoot . '/vendor/autoload.php';

    // Chargement du .env si nécessaire
    if (file_exists($projectRoot . '/.env')) {
        try {
            // $projectRoot est maintenant garanti d'être une string
            $dot = Dotenv\Dotenv::createImmutable($projectRoot);
            $dot->load();
        } catch (Exception $e) {
            error_log("Unable to load .env: " . $e->getMessage());
        }
    }
}

use Service\Email\EmailReminderService;

define('DAYS_BEFORE_RELAY', 7);

$dryRun = (isset($argv) && in_array('--dry-run', $argv, true));

try {
    $pdo = Database::getInstance()->getConnection();

    // Lire les dossiers incomplets
    $sql  = "SELECT NumEtu, Nom, Prenom, EmailAMU, EmailPersonnel FROM dossiers WHERE IsComplete = 0";
    $stmt = $pdo->query($sql);

    // Vérification retour SQL
    if ($stmt === false) {
        echo "[" . date('Y-m-d H:i:s') . "] Erreur SQL lors de la récupération des dossiers.\n";
        exit(1);
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($rows)) {
        echo "[" . date('Y-m-d H:i:s') . "] Aucun dossier incomplet trouvé.\n";
        exit(0);
    }

    foreach ($rows as $r) {
        $numEtu      = (string)$r['NumEtu'];
        $studentName = trim(($r['Prenom'] ?? '') . ' ' . ($r['Nom'] ?? ''));
        $email       = $r['EmailAMU'] ?: $r['EmailPersonnel'] ?: null;

        if (empty($email)) {
            error_log("cron_relances: dossier {$numEtu} ignoré (aucun email)");
            continue;
        }

        // Vérifier s'il y a eu une relance récemment
        $checkSql = "SELECT 1 FROM relances WHERE dossier_id = :dossier_id AND date_relance >= (NOW() - INTERVAL :days DAY) LIMIT 1";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':dossier_id', $numEtu, PDO::PARAM_STR);
        $checkStmt->bindValue(':days', DAYS_BEFORE_RELAY, PDO::PARAM_INT);
        $checkStmt->execute();
        $already = (bool) $checkStmt->fetchColumn();

        if ($already) {
            error_log("cron_relances: dossier {$numEtu} - relance déjà envoyée récemment, skip.");
            continue;
        }

        $itemsToComplete = [];

        if ($dryRun) {
            echo "[" . date('Y-m-d H:i:s') . "] Dry-run: would send to {$email} for dossier {$numEtu} ({$studentName})\n";
            continue;
        }

        $sent = EmailReminderService::sendRelance($email, $numEtu, $studentName, $itemsToComplete);

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

        usleep(150000); // 150ms pause
    }

    echo "[" . date('Y-m-d H:i:s') . "] Traitement terminé.\n";
    exit(0);
} catch (Exception $e) {
    error_log("cron_relances: exception - " . $e->getMessage());
    echo "Erreur: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

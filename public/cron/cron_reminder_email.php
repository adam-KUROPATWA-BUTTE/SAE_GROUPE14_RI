<?php
// public/cron/cron_relances.php
// Exécuter via cron : php /chemin/vers/project/public/cron/cron_relances.php

require_once __DIR__ . '/../../vendor/autoload.php';

use Model\Folder\RelanceModel;
use Service\Email\RelanceEmailService;

// Si vous n'avez pas composer autoload, require vos fichiers manuellement.

$relanceModel = new RelanceModel();

$dossiers = $relanceModel->getIncompleteDossiers();

if (empty($dossiers)) {
    error_log("cron_relances: aucun dossier incomplet trouvé.");
    exit(0);
}

foreach ($dossiers as $d) {
    $dossierId = (int)$d['dossier_id'];
    $email = null;

    // Priorité : email_responsable si renseigné, sinon email etudiant
    if (!empty($d['email_responsable'])) {
        $email = $d['email_responsable'];
    } elseif (!empty($d['email_etudiant'])) {
        $email = $d['email_etudiant'];
    }

    if (empty($email)) {
        error_log("cron_relances: dossier {$dossierId} ignoré (aucun email).");
        continue;
    }

    // Eviter renvoi trop fréquent : exemple 7 jours
    $already = $relanceModel->lastRelanceWithinDays($dossierId, 7);
    if ($already) {
        error_log("cron_relances: dossier {$dossierId} - relance déjà envoyée dans les 7 derniers jours, skip.");
        continue;
    }

    $studentName = trim(($d['prenom'] ?? '') . ' ' . ($d['nom'] ?? ''));

    // Si vous avez un moyen de récupérer les pièces manquantes, remplacez [] par le tableau.
    $itemsToComplete = [];

    $sent = RelanceEmailService::sendRelance($email, $dossierId, $studentName, $itemsToComplete);

    if ($sent) {
        $message = "Relance automatique envoyée au {$email} via cron.";
        $relanceModel->insertRelance($dossierId, $message, null);
        error_log("cron_relances: dossier {$dossierId} - email envoyé à {$email}");
    } else {
        error_log("cron_relances: dossier {$dossierId} - échec envoi à {$email}");
    }
}
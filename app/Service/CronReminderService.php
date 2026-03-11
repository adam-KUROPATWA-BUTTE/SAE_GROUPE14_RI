<?php

namespace Service;

use Model\Repository\FolderRepositoryInterface;
use Model\Repository\RelanceRepositoryInterface;
use Service\Email\EmailReminderService;

class CronReminderService
{
    public function __construct(
        private FolderRepositoryInterface $folderRepo,
        private RelanceRepositoryInterface $relanceRepo
    ) {}

    public function run(bool $dryRun, int $daysBeforeRelay): void
    {
        $folders = $this->folderRepo->findIncompleteFolders();

        foreach ($folders as $folder) {

            // Correction PHPStan : On force le type string car $folder[] est 'mixed'
            $numEtu = isset($folder['NumEtu']) ? strval($folder['NumEtu']) : '';

            $emailAmu = isset($folder['EmailAMU']) ? strval($folder['EmailAMU']) : '';
            $emailPerso = isset($folder['EmailPersonnel']) ? strval($folder['EmailPersonnel']) : '';
            
            // Priorité à l'email AMU, sinon personnel
            $email = $emailAmu !== '' ? $emailAmu : $emailPerso;

            // Si pas d'email ou pas de numéro étudiant, on ignore
            if ($email === '' || $numEtu === '') {
                continue;
            }

            if ($this->relanceRepo->wasRecentlySent($numEtu, $daysBeforeRelay)) {
                continue;
            }

            if ($dryRun) {
                // $email est maintenant garanti d'être une string
                echo "Dry-run → {$email}\n";
                continue;
            }

            // Correction PHPStan : Casting explicite pour le nom aussi
            $prenom = isset($folder['Prenom']) ? strval($folder['Prenom']) : '';
            $nom = isset($folder['Nom']) ? strval($folder['Nom']) : '';
            $studentName = trim($prenom . ' ' . $nom);

            $sent = EmailReminderService::sendRelance(
                $email,
                $numEtu,
                $studentName,
                []
            );

            if ($sent) {
                $this->relanceRepo->save(
                    $numEtu,
                    "Relance automatique envoyée à {$email}"
                );
            }
        }
    }
}
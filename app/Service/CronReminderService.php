<?php

namespace Service\Cron;

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

            $numEtu = $folder['NumEtu'];
            $email = $folder['EmailAMU'] ?: $folder['EmailPersonnel'];

            if (!$email) {
                continue;
            }

            if ($this->relanceRepo->wasRecentlySent($numEtu, $daysBeforeRelay)) {
                continue;
            }

            if ($dryRun) {
                echo "Dry-run → {$email}\n";
                continue;
            }

            $studentName = trim(($folder['Prenom'] ?? '') . ' ' . ($folder['Nom'] ?? ''));

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

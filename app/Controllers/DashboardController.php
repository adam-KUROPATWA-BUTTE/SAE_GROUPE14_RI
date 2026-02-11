<?php

namespace Controllers\site;

use Controllers\ControllerInterface;
use Model\Folder\FolderAdmin;
use Model\Folder\FolderStudent;
use View\Dashboard\DashboardPageAdmin;
use View\Dashboard\DashboardPageStudent;

class DashboardController implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['dashboard-admin', 'dashboard-student'], true) && $method === 'GET';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $page = $_GET['page'] ?? parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $page = is_string($page) ? trim($page, '/') : '';

        switch ($page) {
            case 'dashboard-admin':
                $this->showAdminDashboard();
                break;

            case 'dashboard-student':
                $this->showStudentDashboard();
                break;

            default:
                http_response_code(404);
                echo "Page not found";
                break;
        }
    }

    private function showAdminDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = is_string($_GET['lang'] ?? null) ? $_GET['lang'] : 'fr';

        // 1. Récupération des filtres depuis l'URL
        $filters = [
            'student' => strtolower(trim(is_string($_GET['student'] ?? null) ? $_GET['student'] : '')),
            'dept'    => is_string($_GET['dept'] ?? null) ? $_GET['dept'] : '',
            'type'    => is_string($_GET['type'] ?? null) ? $_GET['type'] : '',
            'year'    => is_string($_GET['year'] ?? null) ? $_GET['year'] : '',
            'dest'    => is_string($_GET['dest'] ?? null) ? $_GET['dest'] : '',
            'camp'    => is_string($_GET['camp'] ?? null) ? $_GET['camp'] : '',
        ];

        // 2. Récupération de tous les dossiers
        $folders = FolderAdmin::getAll();
        if (!is_array($folders)) {
            $folders = [];
        }

        // 3. Logique métier : Filtrage, calculs et séparation
        $outgoing = [];
        $incoming = [];

        foreach ($folders as $d) {
            $nom        = strval($d['Nom'] ?? '');
            $prenom     = strval($d['Prenom'] ?? '');
            $numEtu     = strval($d['NumEtu'] ?? '');
            $dept       = strval($d['CodeDepartement'] ?? '');
            $type       = strval($d['Type'] ?? '');
            $zone       = strval($d['Zone'] ?? '');
            $annee      = strval($d['Annee'] ?? '2024-2025');
            $campagne   = strval($d['Campagne'] ?? 'Automne 2024');
            $isComplete = intval($d['IsComplete'] ?? 0);

            // Application des filtres
            if ($filters['student'] !== '') {
                $fullName = strtolower("$nom $prenom $numEtu");
                if (strpos($fullName, $filters['student']) === false) {
                    continue; // On ignore ce dossier s'il ne correspond pas
                }
            }
            if ($filters['dept'] !== '' && $dept !== $filters['dept']) continue;
            if ($filters['type'] !== '' && $type !== $filters['type']) continue;
            if ($filters['year'] !== '' && $annee !== $filters['year']) continue;
            if ($filters['dest'] !== '' && strpos(strtolower($zone), strtolower($filters['dest'])) === false) continue;
            if ($filters['camp'] !== '' && $campagne !== $filters['camp']) continue;

            // Calcul du pourcentage de complétion
            $piecesJson = strval($d['PiecesJustificatives'] ?? '');
            $pieces = (!empty($piecesJson)) ? json_decode($piecesJson, true) : [];
            $countProvided = (is_array($pieces)) ? count($pieces) : 0;
            $totalRequired = 4;

            if ($isComplete === 1) {
                $percentage = 100;
            } else {
                $percentage = (int)round(($countProvided / $totalRequired) * 100);
                if ($percentage > 100) {
                    $percentage = 100;
                }
            }

            // Ajout des calculs dans le tableau du dossier
            $d['calc_percentage'] = $percentage;
            $d['calc_annee']      = $annee;
            $d['calc_camp']       = $campagne;

            // Tri entre Entrants et Sortants
            if (stripos($type, 'incoming') !== false || stripos($type, 'entrant') !== false) {
                $incoming[] = $d;
            } else {
                $outgoing[] = $d;
            }
        }

        // 4. On passe les données préparées à la vue
        $page = new DashboardPageAdmin($incoming, $outgoing, $filters, $lang);
        $page->render();
    }

    private function showStudentDashboard(): void
    {
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'student') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = is_string($_GET['lang'] ?? null) ? $_GET['lang'] : 'fr';

        if (!isset($_SESSION['numetu'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $numetu = $_SESSION['numetu'];
        $folder = FolderStudent::getStudentDetails($numetu);

        if (!is_array($folder)) {
            $folder = [];
        }

        $page = new DashboardPageStudent($folder, $lang);
        $page->render();
    }
}
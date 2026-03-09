<?php

namespace Controllers;

use Model\UseCase\ManageFolderUseCase;
use Core\View;

class DashboardController implements ControllerInterface
{
    private ManageFolderUseCase $folderUseCase;

    public function __construct()
    {
        $this->folderUseCase = new ManageFolderUseCase();
    }

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
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = is_string($_GET['lang'] ?? null) ? $_GET['lang'] : 'fr';

        $filters = [
            'student' => strtolower(trim(is_string($_GET['student'] ?? null) ? $_GET['student'] : '')),
            'dept'    => is_string($_GET['dept'] ?? null) ? $_GET['dept'] : '',
            'year'    => is_string($_GET['year'] ?? null) ? $_GET['year'] : '',
            'dest'    => strtolower(trim(is_string($_GET['dest'] ?? null) ? $_GET['dest'] : '')),
            'camp'    => is_string($_GET['camp'] ?? null) ? $_GET['camp'] : '',
            'cadre'   => is_string($_GET['cadre'] ?? null) ? $_GET['cadre'] : '',
        ];

        $folders = $this->folderUseCase->getAllFolders();
        if (!is_array($folders)) {
            $folders = [];
        }

        $outgoing = [];
        $incoming = [];

        foreach ($folders as $d) {
            $nom        = strval($d['Nom'] ?? '');
            $prenom     = strval($d['Prenom'] ?? '');
            $numEtu     = strval($d['NumEtu'] ?? '');
            $dept       = strval($d['CodeDepartement'] ?? '');
            $type       = strval($d['Type'] ?? '');
            $annee      = strval($d['Annee'] ?? '2024-2025');
            $campagne   = strval($d['Campagne'] ?? 'Automne 2024');
            $isComplete = intval($d['IsComplete'] ?? 0);

            $composante  = strval($d['Composante'] ?? '');
            $accord      = strval($d['Accord'] ?? '');
            $destination = strval($d['Destination'] ?? '');

            if ($filters['student'] !== '') {
                $fullName = strtolower("$nom $prenom $numEtu");
                if (strpos($fullName, $filters['student']) === false) continue;
            }
            if ($filters['dept'] !== '' && $dept !== $filters['dept']) continue;
            if ($filters['year'] !== '' && $annee !== $filters['year']) continue;
            if ($filters['camp'] !== '' && $campagne !== $filters['camp']) continue;

            if ($filters['dest'] !== '') {
                if (strpos(strtolower($destination), $filters['dest']) === false) continue;
            }

            if ($filters['cadre'] !== '') {
                $cadreRecherche = $filters['cadre'];
                if (stripos($composante, $cadreRecherche) === false && stripos($accord, $cadreRecherche) === false) continue;
            }

            $piecesJson    = strval($d['PiecesJustificatives'] ?? '');
            $pieces        = (!empty($piecesJson)) ? json_decode($piecesJson, true) : [];
            $countProvided = (is_array($pieces)) ? count($pieces) : 0;
            $totalRequired = 4;

            if ($isComplete === 1) {
                $percentage = 100;
            } else {
                $percentage = (int)round(($countProvided / $totalRequired) * 100);
                if ($percentage > 100) $percentage = 100;
            }

            $d['calc_percentage'] = $percentage;
            $d['calc_annee']      = $annee;
            $d['calc_camp']       = $campagne;

            if (stripos($type, 'incoming') !== false || stripos($type, 'entrant') !== false) {
                $incoming[] = $d;
            } else {
                $outgoing[] = $d;
            }
        }

        $t = function (array $frEn) use ($lang): string {
            return ($lang === 'en') ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path) use ($lang): string {
            $separator = (strpos($path, '?') !== false) ? '&' : '?';
            return $path . $separator . 'lang=' . urlencode($lang);
        };

        View::render('Dashboard/dashboard_admin', [
            'incoming' => $incoming,
            'outgoing' => $outgoing,
            'filters'  => $filters,
            'lang'     => $lang,
            't'        => $t,
            'buildUrl' => $buildUrl
        ]);
    }

    private function showStudentDashboard(): void
    {
        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'student') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = is_string($_GET['lang'] ?? null) ? $_GET['lang'] : 'fr';

        if (!isset($_SESSION['numetu'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $numetu = $_SESSION['numetu'];

        $folder = $this->folderUseCase->getStudentDetails($numetu);

        if (!is_array($folder)) {
            $folder = [];
        }

        $status = strval($folder['status'] ?? 'depot');

        // 'accepte' et 'refuse' sont tous les deux l'étape finale visuellement
        $steps = ['depot', 'instruction', 'accepte'];
        $statusForProgress = in_array($status, ['accepte', 'refuse'], true) ? 'accepte' : $status;
        $currentStepIndex = array_search($statusForProgress, $steps, true);
        if ($currentStepIndex === false) $currentStepIndex = 0;

        $totalSteps     = count($steps);
        $currentStepInt = (int)$currentStepIndex;
        $progressPercentage = ($currentStepInt / ($totalSteps - 1)) * 100;

        // Petit décalage visuel pour "depot" (évite une barre à 0%)
        if ($progressPercentage == 0) $progressPercentage = 8;

        $progressStyle = "width: {$progressPercentage}%;";

        $t = function (array $frEn) use ($lang): string {
            return ($lang === 'en') ? $frEn['en'] : $frEn['fr'];
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        View::render('Dashboard/dashboard_student', [
            'folder'        => $folder,
            'lang'          => $lang,
            'status'        => $status,
            'progressStyle' => $progressStyle,
            't'             => $t,
            'buildUrl'      => $buildUrl
        ]);
    }
}
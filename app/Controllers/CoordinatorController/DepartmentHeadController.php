<?php

namespace Controllers\CoordinatorController;

use Controllers\ControllerInterface;
use Model\UseCase\ManageFolderUseCase;
use Core\View;

class DepartmentHeadController implements ControllerInterface
{
    private ManageFolderUseCase $folderUseCase;

    public function __construct()
    {
        $this->folderUseCase = new ManageFolderUseCase();
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'chef-departement';
    }

    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * @param array<string, mixed> $data
     */
    protected function renderView(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    public function control(): void
    {
        $this->startSession();

        $allowedRoles = ['coordinateur', 'chef_departement', 'admin'];

        if (
            empty($_SESSION['role']) ||
            !in_array($_SESSION['role'], $allowedRoles, true)
        ) {
            $this->redirect('index.php?page=login');
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $lang   = $_SESSION['lang'] ?? 'fr';
        $action = $_GET['action']   ?? 'list';
        $role   = $_SESSION['role'];

        $userDepartement = null;
        $rolesAvecDepartement = ['chef_departement', 'coordinateur', 'coordinateur_etude'];
        if (in_array($role, $rolesAvecDepartement, true) && !empty($_SESSION['departement'])) {
            $userDepartement = trim((string) $_SESSION['departement']);
        }

        $t = function (array $translations) use ($lang): string {
            return $translations[$lang] ?? $translations['fr'] ?? '';
        };

        $buildUrl = function (string $url, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        $isLoggedIn = isset($_SESSION['user_id']);

        // ── GESTION POST : avis chef de département ─────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['set_avis_chef'])) {
            $numetu = trim(strval($_POST['numetu'] ?? ''));
            $avis   = trim(strval($_POST['avis']   ?? ''));

            if ($numetu !== '' && in_array($avis, ['accepte', 'refuse'], true)) {
                $this->folderUseCase->setAvisChef($numetu, $avis);
            }

            $this->redirect('index.php?page=chef-departement&action=view&numetu=' . urlencode($numetu) . '&lang=' . urlencode($lang));
        }
        // ────────────────────────────────────────────────────────────

        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = $this->folderUseCase->getStudentDetails($_GET['numetu']);
        }

        $filters = [
            'type'        => $_GET['type']    ?? 'all',
            'zone'        => $_GET['zone']    ?? 'all',
            'complet'     => $_GET['complet'] ?? 'all',
            'search'      => $_GET['search']  ?? '',
            'mobilite'    => 'all',
            'departement' => $userDepartement ?? ($_GET['departement'] ?? 'all'),
        ];

        $currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $perPage     = 10;

        $result = $this->folderUseCase->rechercherAvecPagination($filters, $currentPage, $perPage);

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);

        $this->renderView('Coordinator/department_head', [
            'action'          => $action,
            'filters'         => $filters,
            'page'            => $currentPage,
            'message'         => $message,
            'lang'            => $lang,
            'userRole'        => $role,
            'userDepartement' => $userDepartement,
            'studentData'     => $studentData,
            'paginatedData'   => $result['data'],
            'totalCount'      => $result['total'],
            'totalPages'      => $result['totalPages'],
            'isLoggedIn'      => $isLoggedIn,
            't'               => $t,
            'buildUrl'        => $buildUrl,
        ]);
    }
}
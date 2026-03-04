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

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $allowedRoles = ['coordinateur', 'chef_departement', 'admin'];

        if (
            empty($_SESSION['role']) ||
            !in_array($_SESSION['role'], $allowedRoles, true)
        ) {
            header('Location: index.php?page=login');
            exit;
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $lang   = $_SESSION['lang'] ?? 'fr';
        $action = $_GET['action']   ?? 'list';

        $t = function (array $translations) use ($lang): string {
            return $translations[$lang] ?? $translations['fr'] ?? '';
        };

        $buildUrl = function (string $url, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        $isLoggedIn = isset($_SESSION['user_id']);

        $studentData = null;
        if ($action === 'view' && !empty($_GET['numetu'])) {
            $studentData = $this->folderUseCase->getStudentDetails($_GET['numetu']);
        }

        $filters = [
            'type'        => $_GET['type']        ?? 'all',
            'zone'        => $_GET['zone']        ?? 'all',
            'complet'     => $_GET['complet']     ?? 'all',
            'search'      => $_GET['search']      ?? '',
            'departement' => $_GET['departement'] ?? 'all',
            'mobilite'    => 'all',
        ];

        $currentPage = isset($_GET['p']) ? max(1, intval($_GET['p'])) : 1;
        $perPage     = 10;

        $result = $this->folderUseCase->rechercherAvecPagination($filters, $currentPage, $perPage);

        $message = $_SESSION['message'] ?? '';
        unset($_SESSION['message']);


        View::render('Coordinator/department_head', [
            'action'        => $action,
            'filters'       => $filters,
            'page'          => $currentPage,
            'message'       => $message,
            'lang'          => $lang,
            'studentData'   => $studentData,
            'paginatedData' => $result['data'],
            'totalCount'    => $result['total'],
            'totalPages'    => $result['totalPages'],
            'isLoggedIn'    => $isLoggedIn,
            't'             => $t,
            'buildUrl'      => $buildUrl,
        ]);
    }
}
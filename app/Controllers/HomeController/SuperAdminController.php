<?php

namespace Controllers\HomeController;

use Controllers\ControllerInterface;
use Service\SuperAdminService;
use Model\Persistence\UserRepositoryPDO;
use Core\View;

/**
 * Class SuperAdminController
 *
 * Handles the Super Administrator interface:
 * - Account creation (Secretary / Coordinator)
 * - Account deletion
 * - Language switching
 * - Tritanopia accessibility mode persistence
 *
 * Only accessible to users with role "super_admin".
 */
class SuperAdminController implements ControllerInterface
{
    /**
     * Determines if this controller supports the requested page.
     *
     * @param string $page
     * @param string $method
     * @return bool
     */
    public static function support(string $page, string $method): bool
    {
        return $page === 'super-admin';
    }

    /**
     * Main controller logic.
     *
     * Handles:
     * - Session initialization
     * - Role verification
     * - Language switching
     * - Tritanopia mode persistence
     * - Account creation and deletion
     */
    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Restrict access to super admin only
        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'super_admin') {
            header('Location: index.php?page=login');
            exit;
        }

        /**
         * ==========================
         * Language Management
         * ==========================
         */
        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $lang = $_SESSION['lang'] ?? 'fr';

        /**
         * ==========================
         * Tritanopia Mode Management
         * ==========================
         */
        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = $_GET['tritanopia'] === '1';
        }

        $isTritanopia = $_SESSION['tritanopia'] ?? false;

        $service = new SuperAdminService(new UserRepositoryPDO());

        $success = null;
        $error   = null;

        /**
         * ==========================
         * Account Deletion
         * ==========================
         */
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $loginToDelete = trim((string) ($_POST['login'] ?? ''));

            if ($loginToDelete !== '') {
                $result = $service->deleteAccount($loginToDelete);

                $success = $result
                    ? ($lang === 'fr'
                        ? 'Compte supprimé avec succès.'
                        : 'Account deleted successfully.')
                    : ($lang === 'fr'
                        ? 'Erreur lors de la suppression.'
                        : 'Error while deleting account.');
            }
        }

        /**
         * ==========================
         * Account Creation
         * ==========================
         */
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {

            $login    = trim((string) ($_POST['login'] ?? ''));
            $password = trim((string) ($_POST['password'] ?? ''));
            $role     = trim((string) ($_POST['role'] ?? ''));

            if ($login === '' || $password === '' || !in_array($role, ['admin', 'coordinateur'], true)) {
                $error = $lang === 'fr'
                    ? 'Veuillez remplir tous les champs correctement.'
                    : 'Please fill in all fields correctly.';

            } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $error = $lang === 'fr'
                    ? 'Le login doit être une adresse email valide.'
                    : 'Login must be a valid email address.';

            } else {
                try {
                    $service->createAccount($login, $password, $role);

                    $success = $lang === 'fr'
                        ? "Compte créé avec succès. Un email a été envoyé à $login."
                        : "Account created successfully. An email was sent to $login.";

                } catch (\RuntimeException $e) {
                    $error = $e->getMessage();
                }
            }
        }

        /**
         * Translation helper
         */
        $t = function (array $translations) use ($lang): string {
            return $lang === 'en'
                ? ($translations['en'] ?? '')
                : ($translations['fr'] ?? '');
        };

        /**
         * URL builder with persistent language
         */
        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        $accounts = $service->getAllAccounts();

        View::render('HomePage/super_admin', [
            'lang'       => $lang,
            't'          => $t,
            'buildUrl'   => $buildUrl,
            'accounts'   => $accounts,
            'success'    => $success,
            'error'      => $error,
            'tritanopia' => $isTritanopia
        ]);
    }
}
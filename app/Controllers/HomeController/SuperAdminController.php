<?php

namespace Controllers\HomeController;

use Controllers\ControllerInterface;
use Service\SuperAdminService;
use Model\Persistence\UserRepositoryPDO;
use Core\View;

class SuperAdminController implements ControllerInterface
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'super-admin';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
            header('Location: index.php?page=login');
            exit;
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $lang = $_SESSION['lang'] ?? 'fr';

        if (isset($_GET['tritanopia'])) {
            $_SESSION['tritanopia'] = $_GET['tritanopia'] === '1';
        }

        $isTritanopia = $_SESSION['tritanopia'] ?? false;

        $service = new SuperAdminService(new UserRepositoryPDO());

        $success = null;
        $error   = null;

        $departments = $service->getAvailableDepartments();
        $sites       = $service->getAvailableSites();

        // ── Ajout d'un nouveau département ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_department') {
            $newDept = strtoupper(trim((string) ($_POST['new_department'] ?? '')));
            if ($newDept !== '' && !in_array($newDept, $departments, true)) {
                $service->addDepartment($newDept);
                $departments[] = $newDept;
                sort($departments);
                $success = $lang === 'fr' ? "Département « $newDept » ajouté." : "Department « $newDept » added.";
            } else {
                $error = $lang === 'fr' ? 'Département invalide ou déjà existant.' : 'Invalid or already existing department.';
            }
        }

        // ── Ajout d'un nouveau site ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add_site') {
            $newSite = trim((string) ($_POST['new_site'] ?? ''));
            if ($newSite !== '' && !in_array($newSite, $sites, true)) {
                $service->addSite($newSite);
                $sites[] = $newSite;
                sort($sites);
                $success = $lang === 'fr' ? "Site « $newSite » ajouté." : "Site « $newSite » added.";
            } else {
                $error = $lang === 'fr' ? 'Site invalide ou déjà existant.' : 'Invalid or already existing site.';
            }
        }

        // ── Suppression d'un compte ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
            $loginToDelete = trim((string) ($_POST['login'] ?? ''));
            if ($loginToDelete !== '') {
                $result  = $service->deleteAccount($loginToDelete);
                $success = $result
                    ? ($lang === 'fr' ? 'Compte supprimé avec succès.' : 'Account deleted successfully.')
                    : ($lang === 'fr' ? 'Erreur lors de la suppression.' : 'Error while deleting account.');
            }
        }

        // ── Création d'un compte ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'create') {
            $login       = trim((string) ($_POST['login']       ?? ''));
            $password    = trim((string) ($_POST['password']    ?? ''));
            $role        = trim((string) ($_POST['role']        ?? ''));
            $departement = trim((string) ($_POST['departement'] ?? ''));
            $site        = trim((string) ($_POST['site']        ?? ''));

            $coordRoles = ['coordinateur', 'coordinateur_etude', 'coordinateur_stage', 'chef_departement'];
            $allRoles   = array_merge(['admin'], $coordRoles);
            $isCoord    = in_array($role, $coordRoles, true);

            if ($login === '' || $password === '' || !in_array($role, $allRoles, true)) {
                $error = $lang === 'fr'
                    ? 'Veuillez remplir tous les champs correctement.'
                    : 'Please fill in all fields correctly.';

            } elseif (!preg_match('/^(?=.*[A-Z])(?=.*[^a-zA-Z0-9]).{12,}$/', $password)) {
                $error = $lang === 'fr'
                    ? 'Le mot de passe doit contenir au moins 12 caractères, dont une majuscule et un caractère spécial.'
                    : 'Password must contain at least 12 characters, including one uppercase letter and one special character.';

            } elseif ($isCoord && $departement === '') {
                $error = $lang === 'fr'
                    ? 'Veuillez sélectionner un département pour le coordinateur.'
                    : 'Please select a department for the coordinator.';

            } elseif ($role === 'admin' && $site === '') {
                $error = $lang === 'fr'
                    ? 'Veuillez sélectionner un site pour le secrétaire.'
                    : 'Please select a site for the secretary.';

            } elseif (!filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $error = $lang === 'fr'
                    ? 'Le login doit être une adresse email valide.'
                    : 'Login must be a valid email address.';

            } else {
                try {
                    $service->createAccount(
                        $login,
                        $password,
                        $role,
                        $isCoord          ? $departement : null,
                        $role === 'admin' ? $site        : null
                    );
                    $success = $lang === 'fr'
                        ? "Compte créé avec succès. Un email a été envoyé à $login."
                        : "Account created successfully. An email was sent to $login.";
                } catch (\RuntimeException $e) {
                    $error = $e->getMessage();
                }
            }
        }

        $t = function (array $translations) use ($lang): string {
            return $lang === 'en' ? ($translations['en'] ?? '') : ($translations['fr'] ?? '');
        };

        $buildUrl = function (string $path, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            $separator = (strpos($path, '?') === false) ? '?' : '&';
            return $path . $separator . http_build_query($params);
        };

        $accounts = $service->getAllAccounts();

        View::render('HomePage/super_admin', [
            'lang'        => $lang,
            't'           => $t,
            'buildUrl'    => $buildUrl,
            'accounts'    => $accounts,
            'success'     => $success,
            'error'       => $error,
            'tritanopia'  => $isTritanopia,
            'departments' => $departments,
            'sites'       => $sites,
        ]);
    }
}
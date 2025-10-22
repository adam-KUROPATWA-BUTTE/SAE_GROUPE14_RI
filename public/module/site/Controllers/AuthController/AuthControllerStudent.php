<?php
namespace Controllers\site\AuthController;

use Controllers\ControllerInterface;
use Controllers\Auth_Guard;
use Model\User\UserStudent;

class AuthControllerStudent implements ControllerInterface
{
    public function control(): void
    {
        // Si déjà connecté en tant qu'étudiant, rediriger
        if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'student') {
            $hasDossier = User::checkDossierExists($_SESSION['etudiant_id']);
            if ($hasDossier) {
                header('Location: /dashboard-etudiant');
            } else {
                header('Location: /create-dossier');
            }
            exit;
        }

        $message = '';
        $lang = $_GET['lang'] ?? 'fr';
        $page = $_GET['page'] ?? 'login-student';

        // Traitement du formulaire POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? 'login';

            if ($action === 'login') {
                $identifier = $_POST['identifier'] ?? '';
                $password = $_POST['password'] ?? '';

                $result = User::login($identifier, $password);

                if ($result['success'] && $result['role'] === 'student') {
                    if ($result['has_dossier']) {
                        header('Location: /dashboard-etudiant');
                    } else {
                        header('Location: /create-dossier');
                    }
                    exit;
                } else {
                    $message = "Identifiant ou mot de passe incorrect.";
                }
            } elseif ($action === 'register_etudiant') {
                $nom = $_POST['nom'] ?? '';
                $prenom = $_POST['prenom'] ?? '';
                $email = $_POST['email'] ?? '';
                $password = $_POST['password'] ?? '';
                $typeEtudiant = $_POST['type_etudiant'] ?? '';

                // Validations
                if (empty($nom) || empty($prenom) || empty($email) || empty($password) || empty($typeEtudiant)) {
                    $message = "Tous les champs sont obligatoires.";
                } elseif (!in_array($typeEtudiant, ['entrant', 'sortant'])) {
                    $message = "Type d'étudiant invalide.";
                } elseif (strlen($password) < 8) {
                    $message = "Le mot de passe doit contenir au moins 8 caractères.";
                } else {
                    if (User::registerEtudiant($email, $password, $nom, $prenom, $typeEtudiant)) {
                        $message = "Compte créé avec succès ! Vous pouvez maintenant vous connecter.";
                        $page = 'login-student';
                    } else {
                        $message = "Erreur lors de la création du compte. L'email existe peut-être déjà.";
                    }
                }
            } elseif ($action === 'reset') {
                $email = $_POST['email'] ?? '';
                
                if (User::resetPassword($email)) {
                    $message = "Si cet email existe, un lien de réinitialisation a été envoyé.";
                } else {
                    $message = "Erreur lors de l'envoi du lien.";
                }
            } elseif ($action === 'token_reset') {
                $token = $_POST['token'] ?? '';
                $newPassword = $_POST['new_password'] ?? '';
                
                if (strlen($newPassword) < 8) {
                    $message = "Le mot de passe doit contenir au moins 8 caractères.";
                } else {
                    if (User::updatePasswordWithToken($token, $newPassword)) {
                        $message = "Mot de passe modifié avec succès ! Vous pouvez vous connecter.";
                        $page = 'login-student';
                    } else {
                        $message = "Erreur : token invalide ou expiré.";
                    }
                }
            }
        }

        // Charger la vue appropriée
        $isLogin = ($page === 'login-student' || $page === 'login');
        $isReset = ($page === 'reset');
        $isTokenReset = isset($_GET['token']) && !empty($_GET['token']);
        $token = $_GET['token'] ?? '';
        $isRegisterEtudiant = ($page === 'register-etudiant' || $page === 'register-student');
        $isRegisterAdmin = false;

        require __DIR__ . '/../../View/Login.php';
    }

    public static function support(string $page, string $method): bool
    {
        return in_array($page, ['login', 'login-student', 'register-student', 'register-etudiant', 'reset']);
    }
}

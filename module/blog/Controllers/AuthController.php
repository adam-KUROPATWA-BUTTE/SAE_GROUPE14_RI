<?php

require_once __DIR__ . '/../Model/User.php';

class AuthController
{
    public function index()
    {
        $message = '';
        $isLogin = true;
        $isReset = false;

        if (isset($_GET['register'])) {
            $isLogin = false;
        }
        if (isset($_GET['reset'])) {
            $isReset = true;
            $isLogin = false;
        }
        if (isset($_GET['login'])) {
            $isLogin = true;
            $isReset = false;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $action = $_POST['action'] ?? '';
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            if ($action === 'login') {
                if (User::login($email, $password)) {
                    $message = "Connexion réussie !";
                } else {
                    $message = "Email ou mot de passe incorrect.";
                }
            } elseif ($action === 'register') {
                if (User::register($email, $password)) {
                    $message = "Compte créé avec succès !";
                    $isLogin = true;
                } else {
                    $message = "Erreur lors de la création du compte.";
                }
            } elseif ($action === 'reset') {
                if (User::resetPassword($email)) {
                    $message = "Si cet email existe, un lien de réinitialisation a été envoyé.";
                    $isReset = false;
                    $isLogin = true;
                } else {
                    $message = "Erreur lors de la demande de réinitialisation.";
                }
            }
        }

        require __DIR__ . '/../View/login.php';
    }
}
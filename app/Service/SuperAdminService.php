<?php

namespace Service;

use Model\Persistence\UserRepositoryPDO;

class SuperAdminService
{
    private UserRepositoryPDO $repository;

    public function __construct(UserRepositoryPDO $repository)
    {
        $this->repository = $repository;
    }

    /** @throws \RuntimeException */
    public function createAccount(string $login, string $password, string $role): void
    {
        if ($this->repository->loginExists($login)) {
            throw new \RuntimeException("Ce login existe déjà.");
        }

        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        $created = $this->repository->createAdmin($login, $hashedPassword, $role);

        if (!$created) {
            throw new \RuntimeException("Erreur lors de la création du compte.");
        }

        $this->sendCredentialsMail($login, $password, $role);
    }

    public function deleteAccount(string $login): bool
    {
        return $this->repository->deleteAdminByEmail($login);
    }

    /** @return array<int, array{login: string, role: string, created_at: string}> */
    public function getAllAccounts(): array
    {
        return $this->repository->getAllAdmins();
    }

    private function sendCredentialsMail(string $login, string $password, string $role): void
    {
        $roleLabel = match($role) {
            'admin'        => 'Secrétaire / Administrateur',
            'coordinateur' => 'Coordinateur',
            default        => $role,
        };

        $subject = "=?UTF-8?B?" . base64_encode("Vos identifiants - Service Relations Internationales AMU") . "?=";
        $body    = "Bonjour,\r\n\r\n"
            . "Un compte a été créé pour vous sur la plateforme Relations Internationales AMU.\r\n\r\n"
            . "Vos identifiants :\r\n"
            . "  Login        : $login\r\n"
            . "  Mot de passe : $password\r\n"
            . "  Rôle         : $roleLabel\r\n\r\n"
            . "Veuillez changer votre mot de passe dès la première connexion.\r\n\r\n"
            . "Cordialement,\r\nService des Relations Internationales — AMU\r\n";

        $headers  = "From: noreply@univ-amu.fr\r\n";
        $headers .= "Reply-To: relations.internationales@univ-amu.fr\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        mail($login, $subject, $body, $headers);
    }
}
<?php

namespace Service;

use Model\Persistence\UserRepositoryPDO;
use Service\Email\EmailReminderService;

class SuperAdminService
{
    private UserRepositoryPDO $userRepo;

    public function __construct(UserRepositoryPDO $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /** @return array<int, string> */
    public function getAvailableDepartments(): array
    {
        return $this->userRepo->getDistinctDepartments();
    }

    public function addDepartment(string $code): void
    {
        $this->userRepo->addCustomDepartment($code);
    }

    /** @return array<int, string> */
    public function getAvailableSites(): array
    {
        return $this->userRepo->getDistinctSites();
    }

    public function addSite(string $name): void
    {
        $this->userRepo->addCustomSite($name);
    }

    /** @return array<int, array{login: string, role: string, departement: string|null, site: string|null, nom: string|null, prenom: string|null, created_at: string}> */
    public function getAllAccounts(): array
    {
        return $this->userRepo->findAll();
    }

    public function createAccount(
        string $email,
        string $password,
        string $role,
        ?string $departement = null,
        ?string $site = null,
        ?string $nom = null,
        ?string $prenom = null
    ): void {
        if ($this->userRepo->findByLogin($email)) {
            throw new \RuntimeException("Ce compte existe déjà.");
        }
        
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $this->userRepo->create($email, $hashed, $role, $departement, $site, $nom, $prenom);
        
        EmailReminderService::sendWelcomeEmail($email, $password, $role, $departement, $site, $nom, $prenom);
    }

    public function deleteAccount(string $email): bool
    {
        return $this->userRepo->deleteByLogin($email);
    }
    
}
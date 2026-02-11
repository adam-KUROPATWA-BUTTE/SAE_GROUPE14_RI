<?php

namespace Site\UseCase;

use Model\Repository\UserRepositoryInterface;

class LoginUserUseCase
{
    private UserRepositoryInterface $adminRepo;
    private UserRepositoryInterface $studentRepo;

    public function __construct(UserRepositoryInterface $adminRepo, UserRepositoryInterface $studentRepo)
    {
        $this->adminRepo = $adminRepo;
        $this->studentRepo = $studentRepo;
    }

    public function execute(string $identifier, string $password): array
    {
        if (filter_var($identifier, FILTER_VALIDATE_EMAIL)) {
            return $this->adminRepo->login($identifier, $password);
        } else {
            return $this->studentRepo->login($identifier, $password);
        }
    }
}

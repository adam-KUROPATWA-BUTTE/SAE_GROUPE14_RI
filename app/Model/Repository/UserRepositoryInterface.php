<?php

namespace Model\Repository;

use Model\Entity\User;

interface UserRepositoryInterface
{
    public function findByEmail(string $email): ?User;
    public function findByStudentNumber(string $numetu): ?User;
    public function save(User $user): bool;
    public function updatePassword(string $email, string $hashedPassword): bool;

    /**
     * @return array<string, mixed>
     */
    public function login(string $identifier, string $password): array;

    public function register(User $user): bool;
    public function resetPassword(string $email): bool;
}

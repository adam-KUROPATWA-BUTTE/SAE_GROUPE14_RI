<?php

namespace Model\Persistence;

use Model\Repository\UserRepositoryInterface;
use Model\Entity\User;
use Database;
use PDO;
use PDOException;

class UserRepositoryPDO implements UserRepositoryInterface
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function findByEmail(string $email): ?User
    {
        // Chercher d'abord dans admins
        $sql = "SELECT *, 'admin' as role FROM admins WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $this->mapToUser($data, 'admin');
        }

        // Sinon chercher dans etudiants
        $sql = "SELECT *, 'student' as role FROM etudiants WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return $this->mapToUser($data, 'student');
        }

        return null;
    }

    public function findByStudentNumber(string $numetu): ?User
    {
        // Chercher uniquement dans etudiants
        $sql = "SELECT *, 'student' as role FROM etudiants WHERE numetu = :numetu";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['numetu' => $numetu]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return null;
        }

        return $this->mapToUser($data, 'student');
    }

    public function save(User $user): bool
    {
        try {
            if ($user->getRole() === 'admin') {
                $sql = "INSERT INTO admins (email, password, role) 
                    VALUES (:email, :password, :role)";

                $stmt = $this->pdo->prepare($sql);

                return $stmt->execute([
                    'email' => $user->getEmail(),
                    'password' => $user->getPassword(),
                    'role' => $user->getRole()
                ]);
            } else {
                $sql = "INSERT INTO etudiants (email, password, numetu) 
                    VALUES (:email, :password, :numetu)";

                $stmt = $this->pdo->prepare($sql);

                return $stmt->execute([
                    'email' => $user->getEmail(),
                    'password' => $user->getPassword(),
                    'numetu' => $user->getNumetu()
                ]);
            }
        } catch (PDOException $e) {
            error_log("Error saving user: " . $e->getMessage());
            return false;
        }
    }

    public function updatePassword(string $email, string $hashedPassword): bool
    {
        try {
            // Essayer d'abord dans admins
            $sql = "UPDATE admins SET password = :password WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            $result = $stmt->execute(['password' => $hashedPassword, 'email' => $email]);

            if ($stmt->rowCount() > 0) {
                return true;
            }

            // Sinon dans etudiants
            $sql = "UPDATE etudiants SET password = :password WHERE email = :email";
            $stmt = $this->pdo->prepare($sql);
            return $stmt->execute(['password' => $hashedPassword, 'email' => $email]);

        } catch (PDOException $e) {
            error_log("Error updating password: " . $e->getMessage());
            return false;
        }
    }

    public function login(string $identifier, string $password): array
    {
        $user = filter_var($identifier, FILTER_VALIDATE_EMAIL)
            ? $this->findByEmail($identifier)
            : $this->findByStudentNumber($identifier);

        if ($user && password_verify($password, $user->getPassword())) {
            return [
                'success' => true,
                'role' => $user->getRole(),
                'numetu' => $user->getNumetu()
            ];
        }

        return ['success' => false];
    }

    public function register(User $user): bool
    {
        $existing = $user->getNumetu()
            ? $this->findByStudentNumber($user->getNumetu())
            : $this->findByEmail($user->getEmail());

        if ($existing) {
            return false;
        }

        $user->setPassword(password_hash($user->getPassword(), PASSWORD_DEFAULT));
        return $this->save($user);
    }

    public function resetPassword(string $email): bool
    {
        $user = $this->findByEmail($email);
        if (!$user) {
            return false;
        }

        $newPassword = bin2hex(random_bytes(4));
        $hashed = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->updatePassword($email, $hashed);

        // TODO: Envoyer l'email avec $newPassword
        return true;
    }

    private function mapToUser(array $data, string $role): User
    {
        return new User(
            $data['id'] ?? null,
            $data['email'] ?? '',
            $data['numetu'] ?? null,
            $data['password'] ?? '',
            $role
        );
    }
}
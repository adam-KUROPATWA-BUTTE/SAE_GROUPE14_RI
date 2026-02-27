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
        // Chercher d'abord dans admins avec le VRAI rôle
        $sql = "SELECT * FROM admins WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($data)) {
            $realRole = is_string($data['role']) ? $data['role'] : 'admin';
            return $this->mapToUser($data, $realRole);
        }

        // Sinon chercher dans etudiants
        $sql = "SELECT * FROM etudiants WHERE email = :email";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':email' => $email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (is_array($data)) {
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

        if (!is_array($data)) {
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

    /**
     * @return array<string, mixed>
     */
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

    /**
     * @param array<string, mixed> $data
     * @param string $role
     * @return User
     */
    private function mapToUser(array $data, string $role): User
    {
        // Fix: Explicit check for scalar/numeric types before casting mixed
        $id = (isset($data['id']) && is_numeric($data['id'])) ? (int)$data['id'] : null;
        $email = (isset($data['email']) && is_scalar($data['email'])) ? (string)$data['email'] : '';
        $numetu = (isset($data['numetu']) && is_scalar($data['numetu'])) ? (string)$data['numetu'] : null;
        $password = (isset($data['password']) && is_scalar($data['password'])) ? (string)$data['password'] : '';

        return new User(
            $id,
            $email,
            $numetu,
            $password,
            $role
        );
    }
    // Ajoute ces méthodes dans ton UserRepositoryPDO existant

    public function loginExists(string $email): bool
    {
        try {
            $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM admins WHERE email = :email");
            $stmt->execute([':email' => $email]);
            return (int) $stmt->fetchColumn() > 0;
        } catch (PDOException $e) {
            error_log("loginExists Error: " . $e->getMessage());
            return false;
        }
    }

    public function createAdmin(string $email, string $hashedPassword, string $role): bool
    {
        try {
            $stmt = $this->pdo->prepare("
            INSERT INTO admins (email, password, role, created_at)
            VALUES (:email, :password, :role, NOW())
        ");
            return $stmt->execute([
                ':email'    => $email,
                ':password' => $hashedPassword,
                ':role'     => $role,
            ]);
        } catch (PDOException $e) {
            error_log("createAdmin Error: " . $e->getMessage());
            return false;
        }
    }

    public function deleteAdminByEmail(string $email): bool
    {
        try {
            $stmt = $this->pdo->prepare("
            DELETE FROM admins WHERE email = :email AND role != 'super_admin'
        ");
            return $stmt->execute([':email' => $email]);
        } catch (PDOException $e) {
            error_log("deleteAdminByEmail Error: " . $e->getMessage());
            return false;
        }
    }

    /** @return array<int, array{login: string, role: string, created_at: string}> */
    public function getAllAdmins(): array
    {
        try {
            $stmt = $this->pdo->query("
            SELECT email as login, role, created_at
            FROM admins
            WHERE role != 'super_admin'
            ORDER BY created_at DESC
        ");
            if ($stmt === false) return [];
            $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return is_array($results) ? array_map(fn($r) => [
                'login'      => is_string($r['login'])      ? $r['login']      : '',
                'role'       => is_string($r['role'])        ? $r['role']       : '',
                'created_at' => is_string($r['created_at']) ? $r['created_at'] : '',
            ], $results) : [];
        } catch (PDOException $e) {
            error_log("getAllAdmins Error: " . $e->getMessage());
            return [];
        }
    }
}

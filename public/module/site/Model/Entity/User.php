<?php
namespace Model\Entity;

class User
{
    private ?int $id;
    private string $email;
    private ?string $numetu;
    private string $password;
    private string $role;

    public function __construct(?int $id, string $email, ?string $numetu, string $password, string $role)
    {
        $this->id = $id;
        $this->email = $email;
        $this->numetu = $numetu;
        $this->password = $password;
        $this->role = $role;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getEmail(): string { return $this->email; }
    public function getNumetu(): ?string { return $this->numetu; }
    public function getPassword(): string { return $this->password; }
    public function getRole(): string { return $this->role; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setEmail(string $email): void { $this->email = $email; }
    public function setNumetu(?string $numetu): void { $this->numetu = $numetu; }
    public function setPassword(string $password): void { $this->password = $password; }
    public function setRole(string $role): void { $this->role = $role; }
}
<?php

namespace Model\Entity;

class Folder
{
    /**
     * @param array<string, mixed> $pieces
     */
    public function __construct(
        private string $numEtu,
        private ?string $nom = null,
        private ?string $prenom = null,
        private ?string $emailPersonnel = null,
        private ?string $telephone = null,
        private bool $isComplete = false,
        private array $pieces = []
    ) {}

    public function getNumEtu(): string
    {
        return $this->numEtu;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function getEmailPersonnel(): ?string
    {
        return $this->emailPersonnel;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function isComplete(): bool
    {
        return $this->isComplete;
    }

    public function setComplete(bool $value): void
    {
        $this->isComplete = $value;
    }

    /**
     * @return array<string, mixed>
     */
    public function getPieces(): array
    {
        return $this->pieces;
    }

    /**
     * @param array<string, mixed> $pieces
     */
    public function setPieces(array $pieces): void
    {
        $this->pieces = $pieces;
    }
}
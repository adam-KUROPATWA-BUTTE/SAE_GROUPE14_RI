<?php

namespace Model\Entity;

class Folder
{
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

    public function isComplete(): bool
    {
        return $this->isComplete;
    }

    public function setComplete(bool $value): void
    {
        $this->isComplete = $value;
    }

    public function getPieces(): array
    {
        return $this->pieces;
    }

    public function setPieces(array $pieces): void
    {
        $this->pieces = $pieces;
    }
}

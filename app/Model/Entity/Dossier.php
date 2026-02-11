<?php

namespace Model\Entity;

class Dossier
{
    private int $id;
    private bool $isComplete;

    public function __construct(int $id, bool $isComplete)
    {
        $this->id = $id;
        $this->isComplete = $isComplete;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isComplete(): bool
    {
        return $this->isComplete;
    }
}

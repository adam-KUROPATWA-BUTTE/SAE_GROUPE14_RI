<?php

namespace Model\Entity;

class FolderStats
{
    private int $total;
    private int $completed;

    public function __construct(int $total, int $completed)
    {
        $this->total = $total;
        $this->completed = $completed;
    }

    public function getCompletionPercentage(): float
    {
        if ($this->total === 0) {
            return 0;
        }

        return ($this->completed / $this->total) * 100;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getCompleted(): int
    {
        return $this->completed;
    }
}

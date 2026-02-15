<?php

namespace Model\Entity;

class GenderStats
{
    private int $male;
    private int $female;

    public function __construct(int $male, int $female)
    {
        $this->male = $male;
        $this->female = $female;
    }

    public function getMale(): int
    {
        return $this->male;
    }

    public function getFemale(): int
    {
        return $this->female;
    }

    public function getTotal(): int
    {
        return $this->male + $this->female;
    }

    public function getMalePercentage(): float
    {
        $total = $this->getTotal();
        return $total > 0 ? ($this->male / $total) * 100 : 0;
    }

    public function getFemalePercentage(): float
    {
        $total = $this->getTotal();
        return $total > 0 ? ($this->female / $total) * 100 : 0;
    }

    public function toArray(): array
    {
        return [
            'male' => $this->male,
            'female' => $this->female
        ];
    }
}
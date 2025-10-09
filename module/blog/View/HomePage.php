<?php
namespace View;

class HomePage extends AbstractView
{
    public function __construct(
        private int $completePercentage = 0
    ) {
    }

    function path(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'HomePage.html';
    }

    function templateValues(): array
    {
        // Calcul pour le donut chart SVG
        $circumference = 817; // 2 * π * 130
        $completeDash = ($this->completePercentage / 100) * $circumference;
        
        return [
            'PERCENTAGE' => $this->completePercentage,
            'COMPLETE_DASH' => $completeDash . ' ' . $circumference
        ];
    }
}
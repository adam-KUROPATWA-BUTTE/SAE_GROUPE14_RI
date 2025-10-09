<?php
namespace Views;

class HomePage extends AbstractView
{
    public function __construct(
        private bool $isLoggedIn = false,
        private int $completePercentage = 0
    ) {}
    
    function path(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'HomePage.html';
    }

    function templateValues(): array
    {
        $circumference = 817; // 2 * π * 130
        $completeDash = ($this->completePercentage / 100) * $circumference;

        return [
            'IS_LOGGED_IN' => $this->isLoggedIn ? 'true' : 'false',
            'PERCENTAGE' => $this->completePercentage,
            'COMPLETE_DASH' => $completeDash . ' ' . $circumference
        ];
    }
}

<?php
namespace Controllers;
use Controllers\ControllerInterface;

interface ControllerInterface
{
    function control();
    static function support(string $chemin, string $method): bool;
}   

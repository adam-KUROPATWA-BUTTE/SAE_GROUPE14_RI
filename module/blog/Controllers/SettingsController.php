<?php

require_once __DIR__ . '/../Model/Universite.php';

class SettingsController
{
    public function index()
    {
        $universites = Universite::getAll();
        require __DIR__ . '/../View/settings.php';
    }
}
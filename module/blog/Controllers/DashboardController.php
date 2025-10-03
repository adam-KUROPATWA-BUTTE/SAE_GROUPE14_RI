<?php

require_once __DIR__ . '/../Model/dashboard.php';

class DashboardController
{
    public function index()
    {
        $dossiers = Dossier::getDossiersIncomplets();
        require __DIR__ . '/../View/dashboard.php';
    }
}
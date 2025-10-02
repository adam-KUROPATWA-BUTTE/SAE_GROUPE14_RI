<?php

require_once __DIR__ . '/../models/Dossier.php';

class DashboardController
{
    public function index()
    {
        $dossiers = Dossier::getDossiersIncomplets();
        require __DIR__ . '/../views/dashboard.php';
    }
}
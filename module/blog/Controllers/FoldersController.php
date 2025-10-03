<?php

require_once __DIR__ . '/../Model/Folder.php';

class FoldersController
{
    public function index()
    {
        $dossiers = Dossier::getAll();
        require __DIR__ . '/../View/folders.php';
    }
}
<?php
session_start();

require_once "Autoloader.php";
Autoloader::register();

use Model\Dossier;
use View\FoldersPage;

$dossiers = Dossier::getAll();
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$view = new FoldersPage($dossiers, $message);
$view->render();

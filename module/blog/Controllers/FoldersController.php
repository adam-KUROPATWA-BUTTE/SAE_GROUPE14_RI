use View\FoldersPage;

$dossiers = Dossier::getAll();
$message = $_SESSION['message'] ?? '';
unset($_SESSION['message']);

$view = new FoldersPage($dossiers, $message);
$view->render();



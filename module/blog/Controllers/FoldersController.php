<?php
// Ne pas redémarrer la session, elle est déjà active
require_once __DIR__ . '/../Model/Folder.php';

class FoldersController
{
    public function index()
    {
        // Vérification simple de connexion
        if (!isset($_SESSION['admin_id'])) {
            $_SESSION['message'] = "Vous devez être connecté pour accéder à cette page.";
            header('Location: index.php?page=login');
            exit;
        }
        
        $isLoggedIn = true;
        
        // Traitement des actions POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
            $action = $_POST['action'];
            $adminId = $_SESSION['admin_id'];
            
            if ($action === 'valider' && isset($_POST['numetu'])) {
                $numetu = $_POST['numetu'];
                
                if (Dossier::valider($numetu, $adminId)) {
                    $_SESSION['message'] = "Dossier validé avec succès !";
                } else {
                    $_SESSION['message'] = "Erreur : Le dossier ne peut pas être validé.";
                }
                
                header('Location: index.php?page=folders');
                exit;
            }
            
            if ($action === 'relancer' && isset($_POST['dossier_id'])) {
                $dossierId = $_POST['dossier_id'];
                $message = $_POST['message'] ?? 'Relance automatique';
                
                if (Dossier::ajouterRelance($dossierId, $message, $adminId)) {
                    $_SESSION['message'] = "Relance envoyée avec succès !";
                } else {
                    $_SESSION['message'] = "Erreur lors de l'envoi de la relance.";
                }
                
                header('Location: index.php?page=folders');
                exit;
            }
            
            if ($action === 'ajouter_etudiant') {
                $numetu = $_POST['numetu'] ?? '';
                $nom = $_POST['nom'] ?? '';
                $prenom = $_POST['prenom'] ?? '';
                $email = $_POST['email'] ?? '';
                $telephone = $_POST['telephone'] ?? null;
                
                if (Dossier::ajouterEtudiant($numetu, $nom, $prenom, $email, $telephone)) {
                    $_SESSION['message'] = "Étudiant ajouté avec succès !";
                } else {
                    $_SESSION['message'] = "Erreur lors de l'ajout de l'étudiant.";
                }
                
                header('Location: index.php?page=folders');
                exit;
            }
            
            if ($action === 'supprimer' && isset($_POST['numetu'])) {
                $numetu = $_POST['numetu'];
                
                if (Dossier::supprimerDossier($numetu)) {
                    $_SESSION['message'] = "Dossier supprimé avec succès !";
                } else {
                    $_SESSION['message'] = "Erreur lors de la suppression du dossier.";
                }
                
                header('Location: index.php?page=folders');
                exit;
            }
        }
        
        // Récupération des données
        $dossiers = Dossier::getAll();
        $message = $_SESSION['message'] ?? '';
        if ($message) {
            unset($_SESSION['message']);
        }
        
        require __DIR__ . '/../View/folders.php';
    }
}
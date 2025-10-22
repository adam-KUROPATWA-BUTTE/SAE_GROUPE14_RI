<?php
namespace Controllers\site;

use Model\Folder;

class SaveStudentController
{
    public static function support(string $page, string $method): bool
    {
        return $page === 'save_student' && $method === 'POST';
    }

    public function control(): void
    {
        // Démarrer la session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // Récupérer la langue
        $lang = $_GET['lang'] ?? 'fr';

        // Préparer les données de l'étudiant
        $data = [
            'numetu' => $_POST['numetu'] ?? '',
            'nom' => $_POST['nom'] ?? '',
            'prenom' => $_POST['prenom'] ?? '',
            'email' => $_POST['email_perso'] ?? '', // Email personnel utilisé comme email principal
            'telephone' => $_POST['telephone'] ?? '',
            'type' => $_POST['type'] ?? null,
            'password' => 'default123' // Mot de passe par défaut
        ];

        // Validation basique
        $errors = [];
        if (empty($data['numetu'])) {
            $errors[] = $lang === 'fr' ? 'Le numéro étudiant est requis' : 'Student ID is required';
        }
        if (empty($data['nom'])) {
            $errors[] = $lang === 'fr' ? 'Le nom est requis' : 'Last name is required';
        }
        if (empty($data['prenom'])) {
            $errors[] = $lang === 'fr' ? 'Le prénom est requis' : 'First name is required';
        }
        if (empty($data['email'])) {
            $errors[] = $lang === 'fr' ? 'L\'email est requis' : 'Email is required';
        }
        if (empty($data['telephone'])) {
            $errors[] = $lang === 'fr' ? 'Le téléphone est requis' : 'Phone is required';
        }

        // Si des erreurs, rediriger avec message d'erreur
        if (!empty($errors)) {
            $_SESSION['message'] = implode(', ', $errors);
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Vérifier si l'étudiant existe déjà
        $existing = Folder::getByNumetu($data['numetu']);
        if ($existing) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec ce numéro existe déjà'
                : 'A student with this ID already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Vérifier si l'email existe déjà
        $existingEmail = Folder::getByEmail($data['email']);
        if ($existingEmail) {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Un étudiant avec cet email existe déjà'
                : 'A student with this email already exists';
            header('Location: index.php?page=folders&action=create&lang=' . $lang);
            exit;
        }

        // Créer le dossier
        $success = Folder::creerDossier($data);

        if ($success) {
            // Gérer les fichiers uploadés (photo et CV)
            if (isset($_FILES['photo']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
                Folder::uploadPhoto($data['numetu'], $_FILES['photo']);
            }

            if (isset($_FILES['cv']) && $_FILES['cv']['error'] === UPLOAD_ERR_OK) {
                Folder::uploadCV($data['numetu'], $_FILES['cv']);
            }

            $_SESSION['message'] = $lang === 'fr'
                ? 'Dossier créé avec succès'
                : 'Folder created successfully';
        } else {
            $_SESSION['message'] = $lang === 'fr'
                ? 'Erreur lors de la création du dossier'
                : 'Error creating folder';
        }

        // Rediriger vers la liste des dossiers
        header('Location: index.php?page=folders&lang=' . $lang);
        exit;
    }
}
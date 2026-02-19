<?php

namespace Controllers\ContactController;

use Controllers\ControllerInterface;
use Model\Persistence\ContactMessageRepository;
use Service\ContactService;

class ContactControllerStudent implements ControllerInterface
{
    private ContactService $contactService;

    public function __construct()
    {
        $repository = new ContactMessageRepository();
        $this->contactService = new ContactService($repository);
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'contact-student';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['numetu'])) {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = $_SESSION['lang'] ?? 'fr';
        $numEtu = $_SESSION['numetu'];

        $messageSent = false;
        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name = trim($_POST['name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $subject = trim($_POST['subject'] ?? '');
                $message = trim($_POST['message'] ?? '');

                $this->contactService->sendMessage(
                    studentNumEtu: $numEtu,
                    name: $name,
                    email: $email,
                    subject: $subject,
                    messageContent: $message
                );

                $messageSent = true;
                $_SESSION['message'] = $lang === 'fr'
                    ? 'Votre message a été envoyé avec succès !'
                    : 'Your message has been sent successfully!';

            } catch (\InvalidArgumentException $e) {
                $error = $lang === 'fr'
                    ? 'Veuillez remplir tous les champs correctement.'
                    : 'Please fill in all fields correctly.';
            } catch (\Exception $e) {
                $error = $lang === 'fr'
                    ? 'Une erreur est survenue. Veuillez réessayer.'
                    : 'An error occurred. Please try again.';
            }
        }

        $t = function(array $translations) use ($lang) {
            return $translations[$lang] ?? $translations['fr'] ?? '';
        };

        $buildUrl = function(string $url, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            $queryString = http_build_query($params);
            return $url . ($queryString ? '?' . $queryString : '');
        };

        $contactInfo = [
            'email' => 'relations.internationales@univ-amu.fr',
            'phone' => '+33 4 13 55 00 00',
            'address' => [
                'fr' => 'Aix-Marseille Université<br>58 Boulevard Charles Livon<br>13007 Marseille, France',
                'en' => 'Aix-Marseille University<br>58 Boulevard Charles Livon<br>13007 Marseille, France'
            ],
            'hours' => [
                'fr' => 'Lundi - Vendredi : 9h00 - 17h00',
                'en' => 'Monday - Friday: 9:00 AM - 5:00 PM'
            ]
        ];

        require_once __DIR__ . '/../../View/Contact/contact_student.php';
    }
}
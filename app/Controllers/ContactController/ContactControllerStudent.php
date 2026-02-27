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

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $lang   = $_SESSION['lang'] ?? 'fr';
        $numEtu = $_SESSION['numetu'];
        $action = $_GET['action'] ?? 'form';

        $t = function (array $translations) use ($lang): string {
            return $translations[$lang] ?? $translations['fr'] ?? '';
        };

        $buildUrl = function (string $url, array $params = []) use ($lang): string {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        // ── Action : réponse étudiant à la réponse admin ──
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'reply') {
            $messageId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            $reply     = trim($_POST['student_reply'] ?? '');

            if ($messageId > 0 && !empty($reply)) {
                $this->contactService->replyToAdminResponse($messageId, $reply);
                $_SESSION['message'] = $lang === 'fr'
                    ? 'Votre réponse a été envoyée !'
                    : 'Your reply has been sent!';
            }

            header('Location: index.php?page=contact-student&lang=' . $lang . '#messagesPanel');
            exit;
        }

        // ── Action : envoi nouveau message ──
        $messageSent = false;
        $error       = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $name    = trim($_POST['name']    ?? '');
                $email   = trim($_POST['email']   ?? '');
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

        $contactInfo = [
            'email' => 'relations.internationales@univ-amu.fr',
            'phone' => '+33 4 13 55 00 00',
            'address' => [
                'fr' => 'Aix-Marseille Université<br>58 Boulevard Charles Livon<br>13007 Marseille, France',
                'en' => 'Aix-Marseille University<br>58 Boulevard Charles Livon<br>13007 Marseille, France',
            ],
            'hours' => [
                'fr' => 'Lundi - Vendredi : 9h00 - 17h00',
                'en' => 'Monday - Friday: 9:00 AM - 5:00 PM',
            ],
        ];

        $studentMessages = $this->contactService->getStudentMessages($numEtu);

        require_once ROOT_PATH . '/app/View/Contact/contact_student.php';
    }
}
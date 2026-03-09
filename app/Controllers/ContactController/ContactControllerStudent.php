<?php

namespace Controllers\ContactController;

use Controllers\ControllerInterface;
use Model\Persistence\ConversationPDO;
use Service\ContactService;
use Core\View; 

class ContactControllerStudent implements ControllerInterface
{
    private ContactService $contactService;

    public function __construct()
    {
        $repository = new ConversationPDO();
        $this->contactService = new ContactService($repository);
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'contact-student';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

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

        $t = fn(array $translations) => $translations[$lang] ?? $translations['fr'] ?? '';
        $buildUrl = function(string $url, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'reply') {
            $conversationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            $reply          = trim($_POST['student_reply'] ?? '');

            if ($conversationId > 0 && !empty($reply)) {
                $this->contactService->addMessage($conversationId, 'student', $reply);
                $_SESSION['message'] = $lang === 'fr' ? 'Votre message a été envoyé !' : 'Message sent!';
            }
            header('Location: index.php?page=contact-student&lang=' . $lang . '#messagesPanel');
            exit;
        }

        $messageSent = false;
        $error       = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'form') {
            try {
                $this->contactService->createConversation(
                    studentNumEtu: $numEtu,
                    name: trim($_POST['name'] ?? ''),
                    email: trim($_POST['email'] ?? ''),
                    subject: trim($_POST['subject'] ?? ''),
                    initialMessage: trim($_POST['message'] ?? '')
                );
                $messageSent = true;
            } catch (\Exception $e) {
                $error = $lang === 'fr' ? 'Erreur lors de l\'envoi.' : 'Error sending message.';
            }
        }

        $studentConversations = $this->contactService->getStudentConversations($numEtu);

        $contactInfo = [
            'email' => 'relations.internationales@univ-amu.fr',
            'phone' => '+33 4 13 55 00 00',
            'address' => [
                'fr' => 'Aix-Marseille Université<br>13007 Marseille, France',
                'en' => 'Aix-Marseille University<br>13007 Marseille, France',
            ],
            'hours' => [
                'fr' => 'Lundi - Vendredi : 9h00 - 17h00',
                'en' => 'Monday - Friday: 9:00 AM - 5:00 PM',
            ],
        ];

        View::render('Contact/contact_student', [
            'lang'                 => $lang,
            'numEtu'               => $numEtu,
            'action'               => $action,
            't'                    => $t,
            'buildUrl'             => $buildUrl,
            'messageSent'          => $messageSent,
            'error'                => $error,
            'contactInfo'          => $contactInfo,
            'studentConversations' => $studentConversations
        ]);
    }
}
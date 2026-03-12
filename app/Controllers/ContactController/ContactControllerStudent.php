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

    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Rend une vue.
     *
     * @param array<string, mixed> $data
     */
    protected function renderView(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    public function control(): void
    {
        $this->startSession();

        if (!isset($_SESSION['numetu'])) {
            $this->redirect('index.php?page=login');
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }

        $lang   = $_SESSION['lang'] ?? 'fr';
        $numEtu = $_SESSION['numetu'];
        $action = $_GET['action'] ?? 'form';

        $t = fn(array $translations) => $translations[$lang] ?? $translations['fr'] ?? '';

        $buildUrl = function (string $url, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'reply') {
            $conversationId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
            $reply = trim($_POST['student_reply'] ?? '');

            if ($conversationId > 0 && !empty($reply)) {
                $this->contactService->addMessage($conversationId, 'student', $reply);
                $_SESSION['message'] = $lang === 'fr' ? 'Votre message a été envoyé !' : 'Message sent!';
            }

            $this->redirect('index.php?page=contact-student&lang=' . $lang . '#messagesPanel');
        }

        $messageSent = false;
        $error = null;

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
                $error = $lang === 'fr'
                    ? "Erreur lors de l'envoi."
                    : "Error sending message.";
            }
        }

        $studentConversations = $this->contactService->getStudentConversations($numEtu);

        $contactInfo = [
            'email' => 'jocelyne.vial@univ-amu.fr',
            'phone' => ' +33 4 13 94 65 02',
            'address' => [
                'fr' => '413 Avenue Gaston Berger<br>13625 Aix-en-Provence',
                'en' => '413 Avenue Gaston Berger<br>13625 Aix-en-Provence',
            ],
            'hours' => [
                'fr' => 'Lundi - Vendredi : 9h00 - 17h00',
                'en' => 'Monday - Friday: 9:00 AM - 5:00 PM',
            ],
        ];

        $this->renderView('Contact/contact_student', [
            'lang' => $lang,
            'numEtu' => $numEtu,
            'action' => $action,
            't' => $t,
            'buildUrl' => $buildUrl,
            'messageSent' => $messageSent,
            'error' => $error,
            'contactInfo' => $contactInfo,
            'studentConversations' => $studentConversations,
        ]);
    }
}
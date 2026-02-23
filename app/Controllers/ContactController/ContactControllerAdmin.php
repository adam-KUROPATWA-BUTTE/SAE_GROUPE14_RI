<?php

namespace Controllers\ContactController;

use Controllers\ControllerInterface;
use Model\Persistence\ContactMessageRepository;
use Service\ContactService;

class ContactControllerAdmin implements ControllerInterface
{
    private ContactService $contactService;

    public function __construct()
    {
        $repository = new ContactMessageRepository();
        $this->contactService = new ContactService($repository);
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'messages-admin';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }

        $lang = $_SESSION['lang'] ?? 'fr';
        $action = $_GET['action'] ?? 'list';
        $messageId = isset($_GET['id']) ? (int)$_GET['id'] : null;

        // Gestion des actions
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($action === 'respond' && $messageId) {
                $response = trim($_POST['response'] ?? '');
                if (!empty($response)) {
                    $this->contactService->respondToMessage($messageId, $response);
                    $_SESSION['message'] = $lang === 'fr'
                        ? 'Réponse envoyée avec succès !'
                        : 'Response sent successfully!';
                    header('Location: index.php?page=messages-admin&action=view&id=' . $messageId);
                    exit;
                }
            } elseif ($action === 'mark-read' && $messageId) {
                $this->contactService->markAsRead($messageId);
                header('Location: index.php?page=messages-admin');
                exit;
            } elseif ($action === 'delete' && $messageId) {
                $this->contactService->deleteMessage($messageId);
                $_SESSION['message'] = $lang === 'fr'
                    ? 'Message supprimé.'
                    : 'Message deleted.';
                header('Location: index.php?page=messages-admin');
                exit;
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

        // Afficher un message spécifique
        if ($action === 'view' && $messageId) {
            $message = $this->contactService->getMessageById($messageId);

            if (!$message) {
                header('Location: index.php?page=messages-admin');
                exit;
            }

            if (!$message->isRead()) {
                $this->contactService->markAsRead($messageId);
            }

            require_once ROOT_PATH . '/app/View/Contact/messages_admin_view.php';
        } else {
            // Liste de tous les messages
            $filter = $_GET['filter'] ?? 'all';

            if ($filter === 'unread') {
                $messages = $this->contactService->getUnreadMessages();
            } else {
                $messages = $this->contactService->getAllMessages();
            }

            require_once ROOT_PATH . '/app/View/Contact/messages_admin_list.php';
        }
    }
}
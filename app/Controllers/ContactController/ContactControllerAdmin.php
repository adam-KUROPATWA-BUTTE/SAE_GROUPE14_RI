<?php

namespace Controllers\ContactController;

use Controllers\ControllerInterface;
use Model\Persistence\ConversationPDO;
use Service\ContactService;
use Core\View;

class ContactControllerAdmin implements ControllerInterface
{
    private ContactService $contactService;

    public function __construct()
    {
        $repository = new ConversationPDO();
        $this->contactService = new ContactService($repository);
    }

    public static function support(string $page, string $method): bool
    {
        return $page === 'messages-admin';
    }

    public function control(): void
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            header('Location: index.php?page=login');
            exit;
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $lang = $_SESSION['lang'] ?? 'fr';
        $action = $_POST['action'] ?? $_GET['action'] ?? 'list';        
        $conversationId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);  
        // --- GESTION DES ACTIONS POST (Formulaires) ---
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            
            if ($action === 'respond' && $conversationId) {
                $response = trim($_POST['response'] ?? '');
                if (!empty($response)) {
                    $this->contactService->addMessage($conversationId, 'admin', $response);
                    $_SESSION['message'] = $lang === 'fr' ? 'Réponse envoyée !' : 'Response sent!';
                    header('Location: index.php?page=messages-admin&action=view&id=' . $conversationId);
                    exit;
                }
            } 
            
            elseif ($action === 'mark-read' && $conversationId) {
                $this->contactService->markConversationAsRead($conversationId, 'admin');
                header('Location: index.php?page=messages-admin');
                exit;
            } 
            
            // C'est ici que se trouvait l'erreur : cette action doit être DANS le bloc POST
            elseif ($action === 'delete') {
                $idToDelete = $_POST['id'] ?? $_GET['id'] ?? null;
                if ($idToDelete) {
                    $this->contactService->deleteConversation((int)$idToDelete);
                    $_SESSION['message'] = $lang === 'fr' ? 'Conversation supprimée.' : 'Conversation deleted.';
                    header('Location: index.php?page=messages-admin&lang=' . $lang);
                    exit;
                }
            }
        }

        $t = fn(array $translations) => $translations[$lang] ?? $translations['fr'] ?? '';
        $buildUrl = function(string $url, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        if ($action === 'view' && $conversationId) {
            $conversation = $this->contactService->getConversationById($conversationId);
            if (!$conversation) {
                header('Location: index.php?page=messages-admin');
                exit;
            }

            $this->contactService->markConversationAsRead($conversationId, 'admin');

            View::render('Contact/messages_admin_view', [
                'conversation'   => $conversation,
                'lang'           => $lang,
                't'              => $t,
                'buildUrl'       => $buildUrl,
                'action'         => $action,
                'conversationId' => $conversationId
            ]);
        } else {
            $filter = $_GET['filter'] ?? 'all';
            $conversations = $filter === 'unread' 
                ? $this->contactService->getUnreadConversations('admin') 
                : $this->contactService->getAllConversations();

            View::render('Contact/messages_admin_list', [
                'conversations' => $conversations,
                'filter'        => $filter,
                'lang'          => $lang,
                't'             => $t,
                'buildUrl'      => $buildUrl,
                'action'        => $action
            ]);
        }
    }
}
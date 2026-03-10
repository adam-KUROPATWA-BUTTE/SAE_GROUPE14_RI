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

    /**
     * Démarre la session. Méthode protégée pour pouvoir être neutralisée en test.
     */
    protected function startSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
     * Redirige vers une URL. Méthode protégée pour pouvoir être mockée en test.
     */
    protected function redirect(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    /**
     * Rend une vue. Méthode protégée pour pouvoir être mockée en test.
     */
    protected function renderView(string $view, array $data = []): void
    {
        View::render($view, $data);
    }

    public function control(): void
    {
        $this->startSession();

        if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
            $this->redirect('index.php?page=login');
        }

        if (isset($_GET['lang']) && in_array($_GET['lang'], ['fr', 'en'], true)) {
            $_SESSION['lang'] = $_GET['lang'];
        }
        $lang           = $_SESSION['lang'] ?? 'fr';
        $action         = $_POST['action'] ?? $_GET['action'] ?? 'list';
        $conversationId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            if ($action === 'respond' && $conversationId) {
                $response = trim($_POST['response'] ?? '');
                if (!empty($response)) {
                    $this->contactService->addMessage($conversationId, 'admin', $response);
                    $_SESSION['message'] = $lang === 'fr' ? 'Réponse envoyée !' : 'Response sent!';
                    $this->redirect('index.php?page=messages-admin&action=view&id=' . $conversationId);
                }
            }

            elseif ($action === 'mark-read' && $conversationId) {
                $this->contactService->markConversationAsRead($conversationId, 'admin');
                $this->redirect('index.php?page=messages-admin');
            }

            elseif ($action === 'delete') {
                $idToDelete = $_POST['id'] ?? $_GET['id'] ?? null;
                if ($idToDelete) {
                    $this->contactService->deleteConversation((int)$idToDelete);
                    $_SESSION['message'] = $lang === 'fr' ? 'Conversation supprimée.' : 'Conversation deleted.';
                    $this->redirect('index.php?page=messages-admin&lang=' . $lang);
                }
            }
        }

        $t        = fn(array $translations) => $translations[$lang] ?? $translations['fr'] ?? '';
        $buildUrl = function (string $url, array $params = []) use ($lang) {
            $params['lang'] = $lang;
            return $url . '?' . http_build_query($params);
        };

        if ($action === 'view' && $conversationId) {
            $conversation = $this->contactService->getConversationById($conversationId);
            if (!$conversation) {
                $this->redirect('index.php?page=messages-admin');
            }

            $this->contactService->markConversationAsRead($conversationId, 'admin');

            $this->renderView('Contact/messages_admin_view', [
                'conversation'   => $conversation,
                'lang'           => $lang,
                't'              => $t,
                'buildUrl'       => $buildUrl,
                'action'         => $action,
                'conversationId' => $conversationId,
            ]);
        } else {
            $filter        = $_GET['filter'] ?? 'all';
            $conversations = $filter === 'unread'
                ? $this->contactService->getUnreadConversations('admin')
                : $this->contactService->getAllConversations();

            $this->renderView('Contact/messages_admin_list', [
                'conversations' => $conversations,
                'filter'        => $filter,
                'lang'          => $lang,
                't'             => $t,
                'buildUrl'      => $buildUrl,
                'action'        => $action,
            ]);
        }
    }
}
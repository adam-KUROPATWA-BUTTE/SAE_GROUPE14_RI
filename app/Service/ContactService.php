<?php

namespace Service;

use Model\Entity\Conversation;
use Model\Persistence\ConversationPDO;

class ContactService
{
    private ConversationPDO $repository;

    public function __construct(ConversationPDO $repository)
    {
        $this->repository = $repository;
    }

    public function createConversation(string $studentNumEtu, string $name, string $email, string $subject, string $initialMessage): int 
    {
        $this->validateEmail($email);
        $this->validateRequired($name, 'name');
        $this->validateRequired($subject, 'subject');
        $this->validateRequired($initialMessage, 'message');

        return $this->repository->create($studentNumEtu, $name, $email, $subject, $initialMessage);
    }

    public function addMessage(int $conversationId, string $senderType, string $content): bool
    {
        $this->validateRequired($content, 'content');
        $conversation = $this->repository->findById($conversationId);
        if (!$conversation) return false;

        $saved = $this->repository->addMessage($conversationId, $senderType, $content);

        if ($saved && $senderType === 'admin') {
            $this->sendResponseMail($conversation, $content);
        }

        return $saved;
    }

    public function getStudentConversations(string $numEtu): array
    {
        return $this->repository->findByStudentNumEtu($numEtu);
    }

    public function getAllConversations(): array
    {
        return $this->repository->findAll();
    }

    public function getUnreadConversations(string $role): array
    {
        return $this->repository->findUnreadByRole($role);
    }

    public function getConversationById(int $id): ?Conversation
    {
        return $this->repository->findById($id);
    }

    public function markConversationAsRead(int $conversationId, string $readerRole): bool
    {
        return $this->repository->markAsRead($conversationId, $readerRole);
    }

    public function deleteConversation(int $id): bool
    {
        return $this->repository->delete($id);
    }

    private function sendResponseMail(Conversation $conversation, string $responseContent): void
    {
        $studentEmail = $conversation->getEmail();
        $studentName  = $conversation->getName();
        $firstMessage = $conversation->getFirstMessage()?->getContent() ?? '';

        $subject = "=?UTF-8?B?" . base64_encode("Réponse à votre message - Service Relations Internationales AMU") . "?=";
        $body  = "Bonjour $studentName,\r\n\r\n"
            . "Le Service des Relations Internationales a répondu à votre demande.\r\n\r\n"
            . "Votre message :\r\n$firstMessage\r\n\r\n"
            . "Notre réponse :\r\n$responseContent\r\n\r\n"
            . "Cordialement,\r\nService des Relations Internationales — AMU\r\n";

        $headers  = "From: relations.internationales@univ-amu.fr\r\n";
        $headers .= "Reply-To: relations.internationales@univ-amu.fr\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        try {
            mail($studentEmail, $subject, $body, $headers);
        } catch (\Exception $e) {
            error_log("Mail non envoyé : " . $e->getMessage());
        }
    }

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException('Invalid email');
    }

    private function validateRequired(string $value, string $fieldName): void
    {
        if (empty(trim($value))) throw new \InvalidArgumentException("Field '$fieldName' is required");
    }
}
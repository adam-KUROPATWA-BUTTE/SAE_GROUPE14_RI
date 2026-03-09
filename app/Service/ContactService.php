<?php

namespace Service;

use Model\Entity\Conversation;
use Model\Persistence\ConversationPDO;
use Service\Email\EmailReminderService;

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

        // Send email notification to the recipient
        if ($saved) {
            error_log("📧 DEBUG: Message saved, sending notification. Sender: {$senderType}");
            
            if ($senderType === 'admin') {
                // Admin sent message → notify student
                $recipientEmail = $conversation->getEmail();
                $recipientName = $conversation->getName();
                $senderName = 'Service Relations Internationales';
                $platformLink = 'https://ri-amu.app/index.php?page=contact-student';
                
                error_log("📧 DEBUG: Notifying student {$recipientEmail}");
                
                $emailSent = EmailReminderService::sendMessageNotification(
                    $recipientEmail,
                    $recipientName,
                    $senderName,
                    $content,
                    $platformLink
                );
                
                if ($emailSent) {
                    error_log("✅ Email notification sent to student: {$recipientEmail}");
                } else {
                    error_log("❌ Failed to send email notification to student: {$recipientEmail}");
                }
                
            } elseif ($senderType === 'student') {
                // Student sent message → notify admin
                $adminEmail = 'relations.internationales@univ-amu.fr';
                $recipientName = 'Administrateur';
                $senderName = $conversation->getName();
                $platformLink = 'https://ri-amu.app/index.php?page=messages-admin';
                
                error_log("📧 DEBUG: Notifying admin {$adminEmail}");
                
                $emailSent = EmailReminderService::sendMessageNotification(
                    $adminEmail,
                    $recipientName,
                    $senderName,
                    $content,
                    $platformLink
                );
                
                if ($emailSent) {
                    error_log("✅ Email notification sent to admin: {$adminEmail}");
                } else {
                    error_log("❌ Failed to send email notification to admin: {$adminEmail}");
                }
            }
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 second
        }

        return $saved;
    }

    /**
     * @return array<int, Conversation>
     */
    public function getStudentConversations(string $numEtu): array
    {
        return $this->repository->findByStudentNumEtu($numEtu);
    }

    /**
     * @return array<int, Conversation>
     */
    public function getAllConversations(): array
    {
        return $this->repository->findAll();
    }

    /**
     * @return array<int, Conversation>
     */
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



    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) throw new \InvalidArgumentException('Invalid email');
    }

    private function validateRequired(string $value, string $fieldName): void
    {
        if (empty(trim($value))) throw new \InvalidArgumentException("Field '$fieldName' is required");
    }
}
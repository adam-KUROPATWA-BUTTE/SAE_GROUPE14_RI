<?php

namespace Service;

use Model\Entity\ContactMessage;
use Model\Persistence\ContactMessageRepository;

class ContactService
{
    private ContactMessageRepository $repository;

    public function __construct(ContactMessageRepository $repository)
    {
        $this->repository = $repository;
    }

    public function sendMessage(
        string $studentNumEtu,
        string $name,
        string $email,
        string $subject,
        string $messageContent
    ): bool {
        $this->validateEmail($email);
        $this->validateRequired($name, 'name');
        $this->validateRequired($subject, 'subject');
        $this->validateRequired($messageContent, 'message');

        $message = new ContactMessage(
            studentNumEtu: $studentNumEtu,
            name: $name,
            email: $email,
            subject: $subject,
            message: $messageContent
        );

        return $this->repository->save($message);
    }

    /** @return array<int, ContactMessage> */
    public function getStudentMessages(string $numEtu): array
    {
        return $this->repository->findByStudentNumEtu($numEtu);
    }

    /** @return array<int, ContactMessage> */
    public function getAllMessages(): array
    {
        return $this->repository->findAll();
    }

    /** @return array<int, ContactMessage> */
    public function getUnreadMessages(): array
    {
        return $this->repository->findUnread();
    }

    public function getMessageById(int $id): ?ContactMessage
    {
        return $this->repository->findById($id);
    }

    public function markAsRead(int $id): bool
    {
        return $this->repository->markAsRead($id);
    }

    public function respondToMessage(int $id, string $response): bool
    {
        $this->validateRequired($response, 'response');

        $message = $this->repository->findById($id);
        if (!$message) return false;

        $saved = $this->repository->addAdminResponse($id, $response);

        if ($saved) {
            $this->sendResponseMail($message, $response);
        }

        return $saved;
    }

    public function replyToAdminResponse(int $id, string $reply): bool
    {
        $this->validateRequired($reply, 'reply');
        return $this->repository->addStudentReply($id, $reply);
    }

    public function deleteMessage(int $id): bool
    {
        return $this->repository->delete($id);
    }

    private function sendResponseMail(ContactMessage $message, string $response): void
    {
        $studentEmail = $message->getEmail();
        $studentName  = $message->getName();

        $subject = "=?UTF-8?B?" . base64_encode("Réponse à votre message - Service Relations Internationales AMU") . "?=";

        $body  = "Bonjour $studentName,\r\n\r\n"
            . "Le Service des Relations Internationales d'AMU a répondu à votre message.\r\n\r\n"
            . "─────────────────────────────────\r\n"
            . "Votre message :\r\n"
            . $message->getMessage() . "\r\n\r\n"
            . "─────────────────────────────────\r\n"
            . "Notre réponse :\r\n"
            . $response . "\r\n\r\n"
            . "─────────────────────────────────\r\n\r\n"
            . "Cordialement,\r\n"
            . "Service des Relations Internationales — AMU\r\n";

        $headers  = "From: relations.internationales@univ-amu.fr\r\n";
        $headers .= "Reply-To: relations.internationales@univ-amu.fr\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
        $headers .= "MIME-Version: 1.0\r\n";

        try {
            mail($studentEmail, $subject, $body, $headers);
        } catch (\Exception $e) {
            error_log("Mail non envoyé : " . $e->getMessage());
        }
    }

    private function validateEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email address');
        }
    }

    private function validateRequired(string $value, string $fieldName): void
    {
        if (empty(trim($value))) {
            throw new \InvalidArgumentException("Field '$fieldName' is required");
        }
    }
}
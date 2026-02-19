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
        return $this->repository->addAdminResponse($id, $response);
    }

    public function deleteMessage(int $id): bool
    {
        return $this->repository->delete($id);
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
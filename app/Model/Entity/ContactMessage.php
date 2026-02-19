<?php

namespace Model\Entity;

class ContactMessage
{
    private ?int $id;
    private string $studentNumEtu;
    private string $name;
    private string $email;
    private string $subject;
    private string $message;
    private \DateTime $createdAt;
    private bool $isRead;
    private ?string $adminResponse;
    private ?\DateTime $respondedAt;

    public function __construct(
        string $studentNumEtu,
        string $name,
        string $email,
        string $subject,
        string $message,
        ?int $id = null,
        ?\DateTime $createdAt = null,
        bool $isRead = false,
        ?string $adminResponse = null,
        ?\DateTime $respondedAt = null
    ) {
        $this->id = $id;
        $this->studentNumEtu = $studentNumEtu;
        $this->name = $name;
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
        $this->createdAt = $createdAt ?? new \DateTime();
        $this->isRead = $isRead;
        $this->adminResponse = $adminResponse;
        $this->respondedAt = $respondedAt;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStudentNumEtu(): string
    {
        return $this->studentNumEtu;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getSubject(): string
    {
        return $this->subject;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    public function isRead(): bool
    {
        return $this->isRead;
    }

    public function markAsRead(): void
    {
        $this->isRead = true;
    }

    public function getAdminResponse(): ?string
    {
        return $this->adminResponse;
    }

    public function setAdminResponse(string $response): void
    {
        $this->adminResponse = $response;
        $this->respondedAt = new \DateTime();
    }

    public function getRespondedAt(): ?\DateTime
    {
        return $this->respondedAt;
    }
}
<?php

namespace Model\Entity;

class Message
{
    private ?int $id;
    private int $conversationId;
    private string $senderType; // 'student' ou 'admin'
    private string $content;
    private bool $isRead;
    private \DateTime $createdAt;

    public function __construct(
        int $conversationId,
        string $senderType,
        string $content,
        bool $isRead = false,
        ?int $id = null,
        ?\DateTime $createdAt = null
    ) {
        $this->id             = $id;
        $this->conversationId = $conversationId;
        $this->senderType     = $senderType;
        $this->content        = $content;
        $this->isRead         = $isRead;
        $this->createdAt      = $createdAt ?? new \DateTime();
    }

    public function getId(): ?int { return $this->id; }
    public function getConversationId(): int { return $this->conversationId; }
    public function getSenderType(): string { return $this->senderType; }
    public function getContent(): string { return $this->content; }
    public function isRead(): bool { return $this->isRead; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    public function markAsRead(): void 
    {
        $this->isRead = true;
    }
}
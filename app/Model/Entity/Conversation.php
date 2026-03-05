<?php

namespace Model\Entity;

class Conversation
{
    private ?int $id;
    private string $studentNumEtu;
    private string $name;
    private string $email;
    private string $subject;
    private string $status;
    private \DateTime $createdAt;
    
    /** @var array<int, Message> */
    private array $messages;

    public function __construct(
        string $studentNumEtu,
        string $name,
        string $email,
        string $subject,
        string $status = 'open',
        ?int $id = null,
        ?\DateTime $createdAt = null
    ) {
        $this->id            = $id;
        $this->studentNumEtu = $studentNumEtu;
        $this->name          = $name;
        $this->email         = $email;
        $this->subject       = $subject;
        $this->status        = $status;
        $this->createdAt     = $createdAt ?? new \DateTime();
        $this->messages      = [];
    }

    // ─── Getters Basiques ──────────────────────────────────────────────

    public function getId(): ?int { return $this->id; }
    public function getStudentNumEtu(): string { return $this->studentNumEtu; }
    public function getName(): string { return $this->name; }
    public function getEmail(): string { return $this->email; }
    public function getSubject(): string { return $this->subject; }
    public function getStatus(): string { return $this->status; }
    public function getCreatedAt(): \DateTime { return $this->createdAt; }

    public function setStatus(string $status): void { $this->status = $status; }

    // ─── Gestion des Messages (La relation 1 -> N) ─────────────────────

    public function addMessage(Message $message): void 
    {
        $this->messages[] = $message;
    }

    /** @return array<int, Message> */
    public function getMessages(): array 
    {
        return $this->messages;
    }

    public function getFirstMessage(): ?Message 
    {
        return $this->messages[array_key_first($this->messages)] ?? null;
    }

    public function getLastMessage(): ?Message 
    {
        if (empty($this->messages)) return null;
        return end($this->messages);
    }

    /**
     * Vérifie s'il y a des messages non lus POUR un rôle spécifique
     * Ex: Si l'admin demande, on cherche les messages non lus envoyés par l'étudiant
     */
    public function hasUnreadMessagesFor(string $role): bool 
    {
        $targetSender = $role === 'admin' ? 'student' : 'admin';
        
        foreach ($this->messages as $msg) {
            if (!$msg->isRead() && $msg->getSenderType() === $targetSender) {
                return true;
            }
        }
        return false;
    }
}
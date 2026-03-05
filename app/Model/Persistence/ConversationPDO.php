<?php

namespace Model\Persistence;

use Model\Entity\Conversation;
use Model\Entity\Message;
use Database;
use PDO;

class ConversationPDO
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function create(string $numEtu, string $name, string $email, string $subject, string $messageContent): int
    {
        $this->pdo->beginTransaction();
        try {
            $sqlConv = "INSERT INTO conversations (student_numetu, name, email, subject, status, created_at) 
                        VALUES (:numetu, :name, :email, :subject, 'open', NOW())";
            $stmtConv = $this->pdo->prepare($sqlConv);
            $stmtConv->execute([
                ':numetu'  => $numEtu,
                ':name'    => $name,
                ':email'   => $email,
                ':subject' => $subject
            ]);

            $conversationId = (int) $this->pdo->lastInsertId();

            $sqlMsg = "INSERT INTO messages (conversation_id, sender_type, content, is_read, created_at) 
                       VALUES (:conv_id, 'student', :content, 0, NOW())";
            $stmtMsg = $this->pdo->prepare($sqlMsg);
            $stmtMsg->execute([
                ':conv_id' => $conversationId,
                ':content' => $messageContent
            ]);

            $this->pdo->commit();
            return $conversationId;

        } catch (\Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    public function addMessage(int $conversationId, string $senderType, string $content): bool
    {
        $sql = "INSERT INTO messages (conversation_id, sender_type, content, is_read, created_at) 
                VALUES (:conv_id, :sender_type, :content, 0, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':conv_id'     => $conversationId,
            ':sender_type' => $senderType,
            ':content'     => $content
        ]);
    }

    public function findById(int $id): ?Conversation
    {
        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) return null;

        $conversation = $this->hydrateConversation($data);
        $this->attachMessagesToConversation($conversation);
        return $conversation;
    }

    public function findByStudentNumEtu(string $numEtu): array
    {
        $stmt = $this->pdo->prepare("SELECT * FROM conversations WHERE student_numetu = :numetu ORDER BY created_at DESC");
        $stmt->execute([':numetu' => $numEtu]);
        return $this->fetchAndHydrateList($stmt);
    }

    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM conversations ORDER BY created_at DESC");
        return $this->fetchAndHydrateList($stmt);
    }

    public function findUnreadByRole(string $role): array
    {
        $targetSender = $role === 'admin' ? 'student' : 'admin';
        $sql = "SELECT DISTINCT c.* FROM conversations c
                JOIN messages m ON c.id = m.conversation_id
                WHERE m.is_read = 0 AND m.sender_type = :sender
                ORDER BY c.created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':sender' => $targetSender]);
        return $this->fetchAndHydrateList($stmt);
    }

    public function markAsRead(int $conversationId, string $readerRole): bool
    {
        $senderToMark = $readerRole === 'admin' ? 'student' : 'admin';
        $stmt = $this->pdo->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = :cid AND sender_type = :sender");
        return $stmt->execute([
            ':cid'    => $conversationId,
            ':sender' => $senderToMark
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM conversations WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    private function fetchAndHydrateList(\PDOStatement $stmt): array
    {
        $conversations = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $conv = $this->hydrateConversation($data);
            $this->attachMessagesToConversation($conv);
            $conversations[] = $conv;
        }
        return $conversations;
    }

    private function hydrateConversation(array $data): Conversation
    {
        return new Conversation(
            studentNumEtu: (string) $data['student_numetu'],
            name:          (string) $data['name'],
            email:         (string) $data['email'],
            subject:       (string) $data['subject'],
            status:        (string) $data['status'],
            id:            (int) $data['id'],
            createdAt:     new \DateTime($data['created_at'])
        );
    }

    private function attachMessagesToConversation(Conversation $conversation): void
    {
        $stmt = $this->pdo->prepare("SELECT * FROM messages WHERE conversation_id = :cid ORDER BY created_at ASC");
        $stmt->execute([':cid' => $conversation->getId()]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $msg = new Message(
                conversationId: (int) $row['conversation_id'],
                senderType:     (string) $row['sender_type'],
                content:        (string) $row['content'],
                isRead:         (bool) $row['is_read'],
                id:             (int) $row['id'],
                createdAt:      new \DateTime($row['created_at'])
            );
            $conversation->addMessage($msg);
        }
    }
}
<?php

namespace Model\Persistence;

use Model\Entity\ContactMessage;
use Database;
use PDO;

class ContactMessageRepository
{
    private PDO $pdo;

    public function __construct()
    {
        $this->pdo = Database::getInstance()->getConnection();
    }

    public function save(ContactMessage $message): bool
    {
        $sql = "INSERT INTO contact_messages 
                (student_numetu, name, email, subject, message, created_at, is_read) 
                VALUES (:numetu, :name, :email, :subject, :message, :created_at, :is_read)";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':numetu'     => $message->getStudentNumEtu(),
            ':name'       => $message->getName(),
            ':email'      => $message->getEmail(),
            ':subject'    => $message->getSubject(),
            ':message'    => $message->getMessage(),
            ':created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            ':is_read'    => $message->isRead() ? 1 : 0,
        ]);
    }

    public function findById(int $id): ?ContactMessage
    {
        $stmt = $this->pdo->prepare("SELECT * FROM contact_messages WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($data) ? $this->hydrate($data) : null;
    }

    /** @return array<int, ContactMessage> */
    public function findByStudentNumEtu(string $numEtu): array
    {
        $stmt = $this->pdo->prepare(
            "SELECT * FROM contact_messages WHERE student_numetu = :numetu ORDER BY created_at DESC"
        );
        $stmt->execute([':numetu' => $numEtu]);

        $messages = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_array($data)) $messages[] = $this->hydrate($data);
        }
        return $messages;
    }

    /** @return array<int, ContactMessage> */
    public function findAll(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM contact_messages ORDER BY created_at DESC");
        if ($stmt === false) return [];

        $messages = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_array($data)) $messages[] = $this->hydrate($data);
        }
        return $messages;
    }

    /** @return array<int, ContactMessage> */
    public function findUnread(): array
    {
        $stmt = $this->pdo->query(
            "SELECT * FROM contact_messages WHERE is_read = 0 ORDER BY created_at DESC"
        );
        if ($stmt === false) return [];

        $messages = [];
        while ($data = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if (is_array($data)) $messages[] = $this->hydrate($data);
        }
        return $messages;
    }

    public function markAsRead(int $id): bool
    {
        $stmt = $this->pdo->prepare("UPDATE contact_messages SET is_read = 1 WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function addAdminResponse(int $id, string $response): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE contact_messages 
            SET admin_response = :response, responded_at = :responded_at, is_read = 1
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'           => $id,
            ':response'     => $response,
            ':responded_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    public function addStudentReply(int $id, string $reply): bool
    {
        $stmt = $this->pdo->prepare("
            UPDATE contact_messages 
            SET student_reply = :reply, student_replied_at = :replied_at, is_read = 0
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'         => $id,
            ':reply'      => $reply,
            ':replied_at' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->pdo->prepare("DELETE FROM contact_messages WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** @param array<string, mixed> $data */
    private function hydrate(array $data): ContactMessage
    {
        $studentReply     = is_string($data['student_reply'] ?? null)     ? $data['student_reply']     : null;
        $studentRepliedAt = is_string($data['student_replied_at'] ?? null) ? $data['student_replied_at'] : null;

        return new ContactMessage(
            studentNumEtu:    strval($data['student_numetu']),
            name:             is_string($data['name'])    ? $data['name']    : '',
            email:            is_string($data['email'])   ? $data['email']   : '',
            subject:          is_string($data['subject']) ? $data['subject'] : '',
            message:          is_string($data['message']) ? $data['message'] : '',
            id:               intval($data['id']),
            createdAt:        new \DateTime(is_string($data['created_at']) ? $data['created_at'] : 'now'),
            isRead:           (bool) $data['is_read'],
            adminResponse:    is_string($data['admin_response'] ?? null) ? $data['admin_response'] : null,
            respondedAt:      is_string($data['responded_at'] ?? null)   ? new \DateTime($data['responded_at'])   : null,
            studentReply:     $studentReply,
            studentRepliedAt: $studentRepliedAt !== null ? new \DateTime($studentRepliedAt) : null,
        );
    }
}
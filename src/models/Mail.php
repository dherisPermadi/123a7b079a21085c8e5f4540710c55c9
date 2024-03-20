<?php
namespace App\Models;

use PDO;
use Db;
use PDOException;
use Predis\Client;

class Mail {
    private $conn;

    public function __construct() {
        $this->conn = new PDO("pgsql:host=" . Db::$dbHost . ";dbname=" . Db::$dbName, Db::$dbUser, Db::$dbPass);
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public function validate($recipient, $mail_subject, $mail_body) {
        $errors = [];

        // Validate recipient
        if (empty($recipient)) {
            $errors[] = "Recipient is required.";
        }

        // Validate mail subject
        if (empty($mail_subject)) {
            $errors[] = "Mail subject is required.";
        }

        // Validate mail body
        if (empty($mail_body)) {
            $errors[] = "Mail body is required.";
        }

        return $errors;
    }

    public function validateStatus($status) {
        $errors = [];

        // Validate status
        if (!in_array($status, ['pending', 'sent', 'failed'])) {
            $errors[] = "Status value is invalid.";
        }

        return $errors;
    }

    public function getAllMails() {
        $stmt = $this->conn->prepare("SELECT * FROM mails");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getMailById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM mails WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createMail($recipient, $mail_subject, $mail_body) {
        $errors = $this->validate($recipient, $mail_subject, $mail_body);

        if (!empty($errors)) {
            return ['error' => $errors];
        }

        try {
            $stmt = $this->conn->prepare("INSERT INTO mails (recipient, mail_subject, mail_body) VALUES (:recipient, :mail_subject, :mail_body)");
            $stmt->bindParam(':recipient', $recipient);
            $stmt->bindParam(':mail_subject', $mail_subject);
            $stmt->bindParam(':mail_body', $mail_body);
            if ($stmt->execute()) {
                $lastInsertId = $this->conn->lastInsertId();

                $this->sendMail($lastInsertId); // Enqueueing mail

                return $this->getMailById($lastInsertId);
            } else {
                return ["error" => $stmt->errorInfo()]; // Return SQL error information
            }
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()]; // Return PDO exception message
        }
    }

    public function updateStatus($id, $status) {
        $errors = $this->validateStatus($status);

        if (!empty($errors)) {
            return ['error' => $errors];
        }

        try {
            $stmt = $this->conn->prepare("UPDATE mails SET status = :status WHERE id = :id");
            $stmt->bindParam(':id', $id);
            $stmt->bindParam(':status', $status);
            if ($stmt->execute()) {
                return $this->getMailById($id);
            } else {
                return ["error" => $stmt->errorInfo()];
            }
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function deleteMail($id) {
        try {
            $stmt = $this->conn->prepare("DELETE FROM mails WHERE id = :id");
            $stmt->bindParam(':id', $id);
            if ($stmt->execute()) {
                return ["message" => 'Mail successfully deleted!'];
            } else {
                return ["error" => $stmt->errorInfo()];
            }
        } catch (PDOException $e) {
            return ["error" => $e->getMessage()];
        }
    }

    public function sendMail($mailId) {
        $redis = new Client([
            'scheme' => 'tcp',
            'host'   => 'redis',
            'port'   => 6379,
        ]);
        $redis->rpush('email_queue', json_encode(['id' => $mailId]));
    }
}

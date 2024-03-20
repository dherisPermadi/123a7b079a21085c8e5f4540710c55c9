<?php
namespace App\Controllers;

use App\Models\Mail;

class MailController {
    private $mail;

    public function __construct() {
        $this->mail = new Mail();
    }

    public function getAllMails() {
        return $this->mail->getAllMails();
    }

    public function getMailById($id) {
        return $this->mail->getMailById($id);
    }

    public function createMail($recipient, $mail_subject, $mail_body) {
        return $this->mail->createMail($recipient, $mail_subject, $mail_body);
    }

    public function updateStatus($id, $status) {
        return $this->mail->updateStatus($id, $status);
    }

    public function deleteMail($id) {
        return $this->mail->deleteMail($id);
    }
}

<?php

require_once 'vendor/autoload.php';
require_once 'src/controllers/MailController.php';
require_once 'src/models/Mail.php';
require_once 'config/mailer.php';
require_once 'config/database.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use App\Controllers\MailController;
use Predis\Client;

$redis = new Client([
  'scheme' => 'tcp',
  'host'   => 'redis',
  'port'   => 6379,
]);

while (true) {
    // Dequeue message from Redis queue
    $message = $redis->blpop('email_queue', 0)[1];

    // Process message and send email
    $data = json_decode($message, true);
    $emailId = $data['id'];

    // Retrieve email data from the database
    $emailData = getEmailData($emailId);

    // Send email using PHPMailer
    try {
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host        = Mailer::$mailerHost;
        $mail->SMTPAuth    = true;
        $mail->Username    = Mailer::$mailerUser;
        $mail->Password    = Mailer::$mailerPass;
        $mail->SMTPSecure  = 'tls';
        $mail->Port        = Mailer::$mailerPort;
        $mail->Subject     = $emailData['mail_subject'];
        $mail->Body        = $emailData['mail_body'];

        $mail->setFrom(Mailer::$mailerFrom, 'Mailer Station');
        $mail->addAddress($emailData['recipient']);
        $mail->isHTML(true);
        $mail->send();

        updateEmailStatus($emailId, 'sent');
        echo "Email sent successfully\n";
    } catch (Exception $e) {
        updateEmailStatus($emailId, 'failed');
        echo "Error: " . $mail->ErrorInfo . "\n";
    }
}

function getEmailData($emailId) {
    $controller = new MailController();

    return $controller->getMailById($emailId);
}

function updateEmailStatus($emailId, $status) {
    $controller = new MailController();

    return $controller->updateStatus($emailId, $status);
}

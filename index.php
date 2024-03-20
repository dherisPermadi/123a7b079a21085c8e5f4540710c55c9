<?php

require __DIR__ . '/vendor/autoload.php';
require_once __DIR__. '/config/database.php';
require_once __DIR__ . '/src/controllers/MailController.php';
require_once __DIR__ . '/src/models/Mail.php';
require_once __DIR__ . '/src/models/Auth.php';

use App\Controllers\MailController;
use App\Models\Auth;

header('Content-Type: application/json');

//validate auth
$authorizationHeader = isset($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : '';
$token               = substr($authorizationHeader, strpos($authorizationHeader, ' ') + 1);
$authentication      = new Auth();

if (!$authentication->validateBearerToken($token)) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$parts      = explode('/', $requestUri);

// Assuming the API namespace is 'api'
if ($parts[1] === 'api' && isset($parts[2])) {
    if ($parts[2] === 'mails') {
        $controller = new MailController();

        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                if (isset($parts[3]) && is_numeric($parts[3])) {
                    $response = $controller->getMailById($parts[3]);
                } else {
                    $response = $controller->getAllMails();
                }
                break;
            case 'POST':
                $data = json_decode(file_get_contents("php://input"), true);
                $response = $controller->createMail($data['recipient'], $data['mail_subject'], $data['mail_body']);
                break;
            case 'PUT':
                if (isset($parts[3]) && is_numeric($parts[3])) {
                    $data     = json_decode(file_get_contents("php://input"), true);
                    $response = $controller->updateStatus($parts[3], $data['status']);
                } else {
                    $response = ['error' => 'Invalid endpoint'];
                }
                break;
            case 'DELETE':
                if (isset($parts[3]) && is_numeric($parts[3])) {
                    $response = $controller->deleteMail($parts[3]);
                } else {
                    $response = ['error' => 'Invalid endpoint'];
                }
                break;
            default:
                $response = ['error' => 'Invalid Request Method'];
        }
    } else {
        $response = ['error' => 'Invalid endpoint'];
    }

    if (isset($response['error'])) {
        http_response_code(403);
    }

    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Not Found']);
}

<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

function respond($data) {
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['user_id'])) {
    respond(['success' => false, 'error' => 'Brak sesji']);
}

if (!getUserByLogin($pdo, (string)$_SESSION['user_id'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    respond(['success' => false, 'error' => 'Sesja wygasla']);
}

$_SESSION['user_email'] = (string)$_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $action = $_GET['action'] ?? 'get_messages';

    switch ($action) {
        case 'get_chats':
            respond(getActiveChats($pdo, $_SESSION['user_email']));

        case 'get_messages':
            $chatId = (int)($_GET['chat_id'] ?? 0);
            respond(getMessages($pdo, $_SESSION['user_email'], $chatId));

        default:
            respond(['success' => false, 'error' => 'Nieznana akcja']);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['success' => false, 'error' => 'Nieobslugiwany request']);
}

$action = $_POST['action'] ?? '';

switch ($action) {
    case 'delete_message':
        $messageId = (int)($_POST['message_id'] ?? 0);
        if ($messageId <= 0) {
            respond(['success' => false, 'error' => 'Zle ID']);
        }

        respond(['success' => deleteMessage($pdo, $messageId, $_SESSION['user_email'])]);

    case 'send_message':
        $chatId = (int)($_POST['chat_id'] ?? 0);
        if ($chatId <= 0) {
            respond(['success' => false, 'error' => 'Zle ID czatu']);
        }

        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            respond(['success' => false, 'error' => 'Pusta wiadomosc']);
        }

        respond(['success' => saveMessage($pdo, $_SESSION['user_email'], $content, $chatId)]);

    default:
        respond(['success' => false, 'error' => 'Nieznana akcja']);
}
?>

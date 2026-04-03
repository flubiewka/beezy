<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

function respond($data) {
    echo json_encode($data);
    exit;
}

function clearSession() {
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }

    session_destroy();
}

function handleGet($pdo, $userLogin) {
    $action = $_GET['action'] ?? 'get_messages';

    if ($action === 'get_or_create_direct_chat') {
        $recipient = trim((string)($_GET['recipient'] ?? ''));
        $chatId = getOrCreateDirectChat($pdo, $userLogin, $recipient);
        respond(['success' => $chatId > 0, 'chat_id' => $chatId]);
    }

    if ($action === 'get_chats') {
        respond(getActiveChats($pdo, $userLogin));
    }

    if ($action === 'get_messages') {
        $chatId = (int)($_GET['chat_id'] ?? 0);
        respond(getMessages($pdo, $userLogin, $chatId));
    }

    respond(['success' => false, 'error' => 'Nieznana akcja']);
}

function handlePost($pdo, $userLogin) {
    $action = $_POST['action'] ?? '';

    if ($action === 'delete_message') {
        $messageId = (int)($_POST['message_id'] ?? 0);
        if ($messageId <= 0) {
            respond(['success' => false, 'error' => 'Zle ID']);
        }

        respond(['success' => deleteMessage($pdo, $messageId, $userLogin)]);
    }

    if ($action === 'send_message') {
        $chatId = (int)($_POST['chat_id'] ?? 0);
        if ($chatId <= 0) {
            respond(['success' => false, 'error' => 'Zle ID czatu']);
        }

        $content = trim((string)($_POST['content'] ?? ''));
        if ($content === '') {
            respond(['success' => false, 'error' => 'Pusta wiadomosc']);
        }

        respond(['success' => saveMessage($pdo, $userLogin, $content, $chatId)]);
    }

    respond(['success' => false, 'error' => 'Nieznana akcja']);
}

if (!isset($_SESSION['user_id'])) {
    respond(['success' => false, 'error' => 'Brak sesji']);
}

if (!getUserByLogin($pdo, (string)$_SESSION['user_id'])) {
    clearSession();
    respond(['success' => false, 'error' => 'Sesja wygasla']);
}

$_SESSION['user_email'] = (string)$_SESSION['user_id'];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        handleGet($pdo, $_SESSION['user_email']);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        handlePost($pdo, $_SESSION['user_email']);
    }

    respond(['success' => false, 'error' => 'Nieobslugiwany request']);
} catch (Throwable $e) {
    respond(['success' => false, 'error' => 'Blad serwera']);
}

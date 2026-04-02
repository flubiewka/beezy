<?php
session_start();
require_once 'db.php';

header('Content-Type: application/json');

function respond($data) {
    echo json_encode($data);
    exit;
}

if (!isset($_SESSION['user_email'])) {
    respond(['success' => false, 'error' => 'Brak sesji']);
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    respond(getMessages($pdo));
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
        $content = trim($_POST['content'] ?? '');
        if ($content === '') {
            respond(['success' => false, 'error' => 'Pusta wiadomosc']);
        }

        respond(['success' => saveMessage($pdo, $_SESSION['user_email'], $content)]);

    default:
        respond(['success' => false, 'error' => 'Nieznana akcja']);
}
?>

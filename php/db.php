<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'beezy');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Błąd połączenia z bazą danych: ' . $e->getMessage());
}


function loginUser($pdo, $login, $password) {
    if (empty($login) || empty($password)) {
        return 'Wypełnij oba pola';
    }

    try {
        $stmt = $pdo->prepare('SELECT LOGIN, PASSWORD, IMIE, NAZWISKO FROM USERS WHERE LOGIN = ?');
        $stmt->execute([$login]);
        $user = $stmt->fetch();

        if (!$user) {
            return 'Użytkownik nie znaleziony';
        }

        if (!password_verify($password, $user['PASSWORD']) && $user['PASSWORD'] !== $password) {
            return 'Błędny login lub hasło';
        }

        return $user;
    } catch (PDOException $e) {
        return 'Błąd bazy danych: ' . $e->getMessage();
    }
}

function getOrCreateGeneralChatId($pdo) {
    $chatId = (int)$pdo->query('SELECT ID_CHAT FROM CHAT ORDER BY ID_CHAT ASC LIMIT 1')->fetchColumn();
    if ($chatId > 0) {
        return $chatId;
    }

    $createChat = $pdo->prepare('INSERT INTO CHAT (CHAT_NAME, IS_GROUP) VALUES (?, ?)');
    $createChat->execute(['General', 0]);

    return (int)$pdo->lastInsertId();
}

function saveMessage($pdo, $sender_login, $content) {
    try {
        $chatId = getOrCreateGeneralChatId($pdo);

        $stmt = $pdo->prepare("INSERT INTO MESSAGES (ID_CHAT, SENDER_LOGIN, CONTENT, SENT_AT)
                              VALUES (?, ?, ?, NOW())");
        return $stmt->execute([$chatId, $sender_login, $content]);
    } catch (PDOException $e) {
        return false;
    }
}

function getMessages($pdo) {
    try {
        $chatId = getOrCreateGeneralChatId($pdo);

        $stmt = $pdo->prepare("SELECT m.*, u.IMIE, u.NAZWISKO FROM MESSAGES m
                                  JOIN USERS u ON m.SENDER_LOGIN = u.LOGIN
                                  WHERE m.ID_CHAT = ?
                                  ORDER BY m.SENT_AT ASC");
        $stmt->execute([$chatId]);

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function deleteMessage($pdo, $messageId, $senderLogin) {
    try {
        $stmt = $pdo->prepare('DELETE FROM MESSAGES WHERE ID_MESSAGE = ? AND SENDER_LOGIN = ?');
        $stmt->execute([(int)$messageId, $senderLogin]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}
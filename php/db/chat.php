<?php

function isUserInChat($pdo, $chatId, $login) {
    $stmt = $pdo->prepare('SELECT 1 FROM CHAT_MEMBERS WHERE ID_CHAT = ? AND LOGIN = ? LIMIT 1');
    $stmt->execute([(int)$chatId, $login]);
    return (bool)$stmt->fetchColumn();
}

function getActiveChats($pdo, $login) {
    try {
        $stmt = $pdo->prepare(
            'SELECT c.ID_CHAT,
                    u.LOGIN AS OTHER_LOGIN,
                    u.IMIE AS OTHER_IMIE,
                    u.NAZWISKO AS OTHER_NAZWISKO,
                    r.ROLE_NAME AS OTHER_ROLE_NAME,
                    MAX(m.SENT_AT) AS LAST_MESSAGE_AT
             FROM CHAT_MEMBERS me
             JOIN CHAT c ON c.ID_CHAT = me.ID_CHAT
             JOIN CHAT_MEMBERS other ON other.ID_CHAT = me.ID_CHAT AND other.LOGIN <> me.LOGIN
             JOIN USERS u ON u.LOGIN = other.LOGIN
             LEFT JOIN ROLES r ON r.ROLE_ID = u.ROLE_ID
             LEFT JOIN MESSAGES m ON m.ID_CHAT = c.ID_CHAT
             WHERE me.LOGIN = ?
             GROUP BY c.ID_CHAT, u.LOGIN, u.IMIE, u.NAZWISKO, r.ROLE_NAME
             ORDER BY LAST_MESSAGE_AT DESC, c.ID_CHAT DESC'
        );
        $stmt->execute([$login]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function saveMessage($pdo, $senderLogin, $content, $chatId) {
    try {
        if ($chatId <= 0 || !isUserInChat($pdo, $chatId, $senderLogin)) {
            return false;
        }

        $stmt = $pdo->prepare('INSERT INTO MESSAGES (ID_CHAT, SENDER_LOGIN, CONTENT, SENT_AT) VALUES (?, ?, ?, NOW())');
        return $stmt->execute([$chatId, $senderLogin, $content]);
    } catch (PDOException $e) {
        return false;
    }
}

function getMessages($pdo, $login, $chatId) {
    try {
        if ($chatId <= 0 || !isUserInChat($pdo, $chatId, $login)) {
            return [];
        }

        $stmt = $pdo->prepare('SELECT m.*, u.IMIE, u.NAZWISKO FROM MESSAGES m JOIN USERS u ON m.SENDER_LOGIN = u.LOGIN WHERE m.ID_CHAT = ? ORDER BY m.SENT_AT ASC');
        $stmt->execute([(int)$chatId]);

        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function deleteMessage($pdo, $messageId, $senderLogin) {
    try {
        $stmt = $pdo->prepare('UPDATE MESSAGES SET IS_DELETED = TRUE, CONTENT = NULL WHERE ID_MESSAGE = ? AND SENDER_LOGIN = ?');
        $stmt->execute([(int)$messageId, $senderLogin]);
        return $stmt->rowCount() > 0;
    } catch (PDOException $e) {
        return false;
    }
}

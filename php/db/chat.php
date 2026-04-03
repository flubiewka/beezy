<?php

function isUserInChat($pdo, $chatId, $login) {
    $stmt = $pdo->prepare('SELECT 1 FROM CHAT_MEMBERS WHERE ID_CHAT = ? AND LOGIN = ? LIMIT 1');
    $stmt->execute([(int)$chatId, $login]);
    return (bool)$stmt->fetchColumn();
}

function getActiveChats($pdo, $login) {
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
}

function saveMessage($pdo, $senderLogin, $content, $chatId) {
    if ($chatId <= 0 || !isUserInChat($pdo, $chatId, $senderLogin)) {
        return false;
    }

    $stmt = $pdo->prepare('INSERT INTO MESSAGES (ID_CHAT, SENDER_LOGIN, CONTENT, SENT_AT) VALUES (?, ?, ?, NOW())');
    return $stmt->execute([$chatId, $senderLogin, $content]);
}

function getMessages($pdo, $login, $chatId) {
    if ($chatId <= 0 || !isUserInChat($pdo, $chatId, $login)) {
        return [];
    }

    $stmt = $pdo->prepare('SELECT m.*, u.IMIE, u.NAZWISKO FROM MESSAGES m JOIN USERS u ON m.SENDER_LOGIN = u.LOGIN WHERE m.ID_CHAT = ? ORDER BY m.SENT_AT ASC');
    $stmt->execute([(int)$chatId]);

    return $stmt->fetchAll();
}

function deleteMessage($pdo, $messageId, $senderLogin) {
    $stmt = $pdo->prepare('UPDATE MESSAGES SET IS_DELETED = TRUE, CONTENT = NULL WHERE ID_MESSAGE = ? AND SENDER_LOGIN = ?');
    $stmt->execute([(int)$messageId, $senderLogin]);
    return $stmt->rowCount() > 0;
}

function getOrCreateDirectChat($pdo, $myLogin, $otherLogin) {
    if ($myLogin === '' || $otherLogin === '' || $myLogin === $otherLogin) {
        return 0;
    }

    $userStmt = $pdo->prepare('SELECT 1 FROM USERS WHERE LOGIN = ? LIMIT 1');
    $userStmt->execute([$otherLogin]);
    if (!$userStmt->fetchColumn()) {
        return 0;
    }

    $findStmt = $pdo->prepare(
        'SELECT c.ID_CHAT
         FROM CHAT c
         JOIN CHAT_MEMBERS m1 ON m1.ID_CHAT = c.ID_CHAT AND m1.LOGIN = ?
         JOIN CHAT_MEMBERS m2 ON m2.ID_CHAT = c.ID_CHAT AND m2.LOGIN = ?
         WHERE c.IS_GROUP = 0
           AND (SELECT COUNT(*) FROM CHAT_MEMBERS cm WHERE cm.ID_CHAT = c.ID_CHAT) = 2
         LIMIT 1'
    );
    $findStmt->execute([$myLogin, $otherLogin]);
    $existingId = (int)$findStmt->fetchColumn();
    if ($existingId > 0) {
        return $existingId;
    }

    $pdo->beginTransaction();

    $createChatStmt = $pdo->prepare('INSERT INTO CHAT (CHAT_NAME, IS_GROUP) VALUES (NULL, 0)');
    $createChatStmt->execute();
    $chatId = (int)$pdo->lastInsertId();

    if ($chatId <= 0) {
        $pdo->rollBack();
        return 0;
    }

    $memberStmt = $pdo->prepare('INSERT INTO CHAT_MEMBERS (ID_CHAT, LOGIN) VALUES (?, ?)');
    $memberStmt->execute([$chatId, $myLogin]);
    $memberStmt->execute([$chatId, $otherLogin]);

    $pdo->commit();
    return $chatId;
}

<?php

function loginUser($pdo, $login, $password) {
    if (empty($login) || empty($password)) {
        return 'Wypelnij oba pola';
    }

    $stmt = $pdo->prepare('SELECT LOGIN, PASSWORD, IMIE, NAZWISKO, ROLE_ID FROM USERS WHERE LOGIN = ?');
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if (!$user) {
        return 'Uzytkownik nie znaleziony';
    }

    if (!password_verify($password, $user['PASSWORD']) && $user['PASSWORD'] !== $password) {
        return 'Bledny login lub haslo';
    }

    return $user;
}

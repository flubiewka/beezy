<?php

function loginUser($pdo, $login, $password) {
    if (empty($login) || empty($password)) {
        return 'Wypelnij oba pola';
    }

    $stmt = $pdo->prepare('SELECT LOGIN, PASSWORD, IMIE, NAZWISKO, ROLE_ID FROM USERS WHERE LOGIN = ?');
    $stmt->execute([$login]);
    $user = $stmt->fetch();

    if ((!password_verify($password, $user['PASSWORD']) && $user['PASSWORD'] !== $password) || !$user) {
        return 'Błędny login lub hasło.';
    }

    return $user;
}

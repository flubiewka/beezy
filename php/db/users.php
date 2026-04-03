<?php

function getUsersByRole($pdo, $role) {
    $stmt = $pdo->prepare('SELECT LOGIN, IMIE, NAZWISKO FROM USERS WHERE ROLE_ID = ?');
    $stmt->execute([$role]);
    return $stmt->fetchAll();
}

function addUser($pdo, $imie, $nazwisko, $login, $password, $roleId) {
    $stmt = $pdo->prepare('INSERT INTO USERS (IMIE, NAZWISKO, LOGIN, PASSWORD, ROLE_ID) VALUES (?, ?, ?, ?, ?)');
    return $stmt->execute([$imie, $nazwisko, $login, password_hash($password, PASSWORD_DEFAULT), $roleId]);
}

function deleteUser($pdo, $login) {
    $stmt = $pdo->prepare('DELETE FROM USERS WHERE LOGIN = ?');
    return $stmt->execute([$login]);
}

function getUserByLogin($pdo, $login) {
    $stmt = $pdo->prepare('SELECT LOGIN, IMIE, NAZWISKO, ROLE_ID FROM USERS WHERE LOGIN = ?');
    $stmt->execute([$login]);
    return $stmt->fetch();
}

function updateUser($pdo, $originalLogin, $imie, $nazwisko, $login, $roleId, $password = '') {
    if ($password !== '') {
        $stmt = $pdo->prepare('UPDATE USERS SET IMIE = ?, NAZWISKO = ?, LOGIN = ?, ROLE_ID = ?, PASSWORD = ? WHERE LOGIN = ?');
        return $stmt->execute([$imie, $nazwisko, $login, $roleId, password_hash($password, PASSWORD_DEFAULT), $originalLogin]);
    }

    $stmt = $pdo->prepare('UPDATE USERS SET IMIE = ?, NAZWISKO = ?, LOGIN = ?, ROLE_ID = ? WHERE LOGIN = ?');
    return $stmt->execute([$imie, $nazwisko, $login, $roleId, $originalLogin]);
}

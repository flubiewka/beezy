<?php

function getUsersByRole($pdo, $role) {
    try {
        $stmt = $pdo->prepare('SELECT LOGIN, IMIE, NAZWISKO FROM USERS WHERE ROLE_ID = ?');
        $stmt->execute([$role]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        return [];
    }
}

function addUser($pdo, $imie, $nazwisko, $login, $password, $roleId) {
    try {
        $stmt = $pdo->prepare('INSERT INTO USERS (IMIE, NAZWISKO, LOGIN, PASSWORD, ROLE_ID) VALUES (?, ?, ?, ?, ?)');
        return $stmt->execute([$imie, $nazwisko, $login, password_hash($password, PASSWORD_DEFAULT), $roleId]);
    } catch (PDOException $e) {
        return false;
    }
}

function deleteUser($pdo, $login) {
    try {
        $stmt = $pdo->prepare('DELETE FROM USERS WHERE LOGIN = ?');
        return $stmt->execute([$login]);
    } catch (PDOException $e) {
        return false;
    }
}

function getUserByLogin($pdo, $login) {
    try {
        $stmt = $pdo->prepare('SELECT LOGIN, IMIE, NAZWISKO, ROLE_ID FROM USERS WHERE LOGIN = ?');
        $stmt->execute([$login]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        return false;
    }
}

function updateUser($pdo, $originalLogin, $imie, $nazwisko, $login, $roleId, $password = '') {
    try {
        if ($password !== '') {
            $stmt = $pdo->prepare('UPDATE USERS SET IMIE = ?, NAZWISKO = ?, LOGIN = ?, ROLE_ID = ?, PASSWORD = ? WHERE LOGIN = ?');
            return $stmt->execute([$imie, $nazwisko, $login, $roleId, password_hash($password, PASSWORD_DEFAULT), $originalLogin]);
        }

        $stmt = $pdo->prepare('UPDATE USERS SET IMIE = ?, NAZWISKO = ?, LOGIN = ?, ROLE_ID = ? WHERE LOGIN = ?');
        return $stmt->execute([$imie, $nazwisko, $login, $roleId, $originalLogin]);
    } catch (PDOException $e) {
        return false;
    }
}

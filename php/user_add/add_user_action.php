<?php
session_start();
require_once __DIR__ . '/../db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	header('Location: add_user.php');
	exit;
}

$imie = trim((string)($_POST['imie'] ?? $_POST['first_name'] ?? ''));
$nazwisko = trim((string)($_POST['nazwisko'] ?? $_POST['last_name'] ?? ''));
$login = trim((string)($_POST['login'] ?? ''));
$password = (string)($_POST['password'] ?? '');
$roleId = (int)($_POST['role'] ?? $_POST['role_id'] ?? 0);

if ($imie === '' || $nazwisko === '' || $login === '' || $password === '' || $roleId <= 0) {
	header('Location: add_user.php?error=missing_fields');
	exit;
}

$created = addUser($pdo, $imie, $nazwisko, $login, $password, $roleId);

if (!$created) {
	header('Location: add_user.php?error=create_failed');
	exit;
}

header('Location: ../app.php?page=users');
exit;
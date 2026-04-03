<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['user_id'])) {
	endWorkSession($pdo, (string)$_SESSION['user_id']);
}

session_destroy();
header('Location: ../index.php');
exit;

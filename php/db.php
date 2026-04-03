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

require_once __DIR__ . '/db/auth.php';
require_once __DIR__ . '/db/chat.php';
require_once __DIR__ . '/db/users.php';
require_once __DIR__ . '/db/sessions.php';
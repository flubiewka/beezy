<?php
session_start();

// Если пользователь нажал "Выйти" или еще не залогинился
if (!isset($_SESSION['user_id'])) {
    // Показываем только страницу логина
    include 'pages/login.php';
    exit; // Остальной код (меню, футер) не загрузится
}

// Если мы тут — значит пользователь залогинен. Показываем каркас сайта:
include 'includes/header.php'; // Тут ваше меню
include 'pages/' . ($_GET['page'] ?? 'home') . ".php";
include 'includes/footer.php';
?>
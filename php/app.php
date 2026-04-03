<?php
session_start();

require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

if (!getUserByLogin($pdo, (string)$_SESSION['user_id'])) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$_SESSION['user_email'] = (string)$_SESSION['user_id'];

startWorkSession($pdo, (string)$_SESSION['user_id']);

$page = $_GET['page'] ?? 'messages';
$allowed_pages = ['messages', 'users', 'report', 'calendar', 'notifications', 'settings'];

if (!in_array($page, $allowed_pages, true)) {
    $page = 'messages';
}

$page_file = __DIR__ . '/content/' . $page . '.php';

if (!file_exists($page_file)) {
    $page_file = __DIR__ . '/content/messages.php';
}

ob_start();
include $page_file;
$page_content = ob_get_clean();
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <?php include __DIR__ . '/partials/head.php'; ?>
</head>
<body>
    <div id="app-background"></div>

    <div id="app-container">
        <?php include __DIR__ . '/partials/header.php'; ?>

        <main id="app-main">
            <?php include __DIR__ . '/partials/sidebar.php'; ?>

            <div id="app-content">
                <?php echo $page_content; ?>
            </div>
        </main>
    </div>

</body>
</html>


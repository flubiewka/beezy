<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

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


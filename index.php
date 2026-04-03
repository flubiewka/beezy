<?php
session_start();

require_once 'php/db.php';

if (isset($_SESSION['user_id'])) {
    header('Location: php/app.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    $result = loginUser($pdo, $login, $password);

    if (is_array($result)) {
        $_SESSION['user_id'] = $result['LOGIN'];
        $_SESSION['user_email'] = $result['LOGIN'];
        $_SESSION['imie'] = $result['IMIE'];
        $_SESSION['nazwisko'] = $result['NAZWISKO'];
        $_SESSION['role_id'] = $result['ROLE_ID'];
        startWorkSession($pdo, $result['LOGIN']);
        header('Location: php/app.php');
        exit;
    } else {
        $error = $result;
    }
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beezy - Logowanie</title>
    <link rel="icon" type="image/png" href="images/color-logo.png">

    <link href="css/common.css" rel="stylesheet">
    <link href="css/login.css" rel="stylesheet">

    <link id="theme-colors" href="css/theme-light.css" rel="stylesheet">
    <script src="js/theme.js"></script>
</head>
<body>
    <div id="app-background"></div>

    <div id="login-container">
        <div class="login-box">
            <h1 class="login-title">Beezy</h1>

            <?php if ($error): ?>
                <div class="login-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="login-inputs">
                <input type="email"
                       name="email"
                       class="login-input"
                       placeholder="Email"
                       required>
                <input type="password"
                       name="password"
                       class="login-input"
                       placeholder="Hasło"
                       required>
                <button type="submit" class="login-button">Zaloguj się</button>
            </form>
        </div>
    </div>
</body>
</html>

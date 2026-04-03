<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/../db.php';

$loginParam = trim((string)($_GET['login'] ?? $_POST['original_login'] ?? ''));
if ($loginParam === '') {
    header('Location: ../app.php?page=users');
    exit;
}

$user = getUserByLogin($pdo, $loginParam);
if (!$user) {
    header('Location: ../app.php?page=users');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imie = trim((string)($_POST['imie'] ?? ''));
    $nazwisko = trim((string)($_POST['nazwisko'] ?? ''));
    $login = trim((string)($_POST['login'] ?? ''));
    $roleId = (int)($_POST['role'] ?? 0);
    $password = (string)($_POST['password'] ?? '');

    if ($imie === '' || $nazwisko === '' || $login === '' || $roleId <= 0) {
        $error = 'Uzupelnij wymagane pola.';
    } else {
        $updated = updateUser($pdo, (string)$_POST['original_login'], $imie, $nazwisko, $login, $roleId, $password);
        if ($updated) {
            header('Location: ../app.php?page=users');
            exit;
        }
        $error = 'Nie udalo sie zapisac zmian.';
    }

    $user['IMIE'] = $imie;
    $user['NAZWISKO'] = $nazwisko;
    $user['LOGIN'] = $login;
    $user['ROLE_ID'] = $roleId;
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beezy - Edytuj uzytkownika</title>
    <link href="../../css/common.css" rel="stylesheet">
    <link href="../../css/users/add-user.css" rel="stylesheet">
    <link id="theme-colors" href="/me-u/css/theme-light.css" rel="stylesheet">
    <script src="../../js/theme.js"></script>
</head>
<body>
    <div class="add-user-page">
        <div class="ambient-shape ambient-shape-a" aria-hidden="true"></div>
        <div class="ambient-shape ambient-shape-b" aria-hidden="true"></div>

        <main class="add-user-shell">
            <header class="add-user-header">
                <a class="back-link" href="../app.php?page=users">Wroc do listy</a>
                <h1>Edycja pracownika</h1>
                <p>Zmodyfikuj dane uzytkownika i zapisz zmiany.</p>
            </header>

            <?php if ($error !== ''): ?>
                <div class="login-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form id="edit-user-form" class="add-user-form" action="" method="POST" novalidate>
                <input type="hidden" name="original_login" value="<?php echo htmlspecialchars((string)$user['LOGIN']); ?>">

                <div class="field-row">
                    <label for="first_name">Imie</label>
                    <input type="text" id="first_name" name="imie" value="<?php echo htmlspecialchars((string)$user['IMIE']); ?>" autocomplete="given-name" required>
                </div>

                <div class="field-row">
                    <label for="last_name">Nazwisko</label>
                    <input type="text" id="last_name" name="nazwisko" value="<?php echo htmlspecialchars((string)$user['NAZWISKO']); ?>" autocomplete="family-name" required>
                </div>

                <div class="field-row">
                    <label for="login">Login</label>
                    <input type="email" id="login" name="login" value="<?php echo htmlspecialchars((string)$user['LOGIN']); ?>" autocomplete="username" required>
                </div>

                <div class="field-row">
                    <label for="password">Nowe haslo (opcjonalnie)</label>
                    <input type="password" id="password" name="password" autocomplete="new-password">
                </div>

                <div class="field-row">
                    <label for="role">Rola</label>
                    <select id="role" name="role" required>
                        <option value="1" <?php echo ((int)$user['ROLE_ID'] === 1) ? 'selected' : ''; ?>>Administrator</option>
                        <option value="2" <?php echo ((int)$user['ROLE_ID'] === 2) ? 'selected' : ''; ?>>Recepcjonista</option>
                        <option value="3" <?php echo ((int)$user['ROLE_ID'] === 3) ? 'selected' : ''; ?>>Pracownik</option>
                    </select>
                </div>

                <div class="actions">
                    <button type="submit" class="primary-btn">Zapisz zmiany</button>
                    <a class="ghost-btn" href="../app.php?page=users">Anuluj</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>

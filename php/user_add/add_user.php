<?php
declare(strict_types=1);

$role = (string)($_GET['role'] ?? 'worker');
$allowedRoles = ['admin', 'secretary', 'worker'];

if (!in_array($role, $allowedRoles, true)) {
    $role = 'worker';
}
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beezy - Dodaj uzytkownika</title>
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
                <h1>Nowy pracownik</h1>
                <p>Uzupelnij dane i wybierz role.</p>
            </header>

            <form id="add-user-form" class="add-user-form" action="add_user_action.php" method="POST" novalidate>
                <div class="field-row">
                    <label for="first_name">Imie</label>
                    <input type="text" id="first_name" name="imie" autocomplete="given-name" required>
                </div>

                <div class="field-row">
                    <label for="last_name">Nazwisko</label>
                    <input type="text" id="last_name" name="nazwisko" autocomplete="family-name" required>
                </div>

                <div class="field-row">
                    <label for="login">Login</label>
                    <input type="email" id="login" name="login" autocomplete="username" required>
                </div>

                <div class="field-row">
                    <label for="password">Haslo</label>
                    <input type="password" id="password" name="password" autocomplete="new-password" required>
                </div>

                <div class="field-row">
                    <label for="role">Rola</label>
                    <select id="role" name="role" required>
                        <option value="1" <?php echo $role === 'admin' ? 'selected' : ''; ?>>Administrator</option>
                        <option value="2" <?php echo $role === 'secretary' ? 'selected' : ''; ?>>Recepcjonista</option>
                        <option value="3" <?php echo $role === 'worker' ? 'selected' : ''; ?>>Pracownik</option>
                    </select>
                </div>

                <div class="actions">
                    <button type="submit" class="primary-btn">Dodaj uzytkownika</button>
                    <a class="ghost-btn" href="../app.php?page=users">Anuluj</a>
                </div>
            </form>
        </main>
    </div>
</body>
</html>
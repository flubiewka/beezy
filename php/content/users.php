<?php
if (isset($_GET['delete_login']) && $_GET['delete_login'] !== '') {
    deleteUser($pdo, (string)$_GET['delete_login']);
    header('Location: ?page=users');
    exit;
}
?>

<div id="content-users" class="content-section">
    <main>
        <section class="admins">
            <h1>Administratorzy</h1>
            <?php
            $admins = getUsersByRole($pdo, 1);
            foreach ($admins as $admin) {
                echo '<div class="user-card">';
                echo '<div class="chat-item-avatar color_placeholder"></div>';
                echo '<div class="user-info">';
                echo '<h3>' . htmlspecialchars($admin['IMIE'] . ' ' . $admin['NAZWISKO']) . '</h3>';
                echo '<p>Login: ' . htmlspecialchars($admin['LOGIN']) . '</p>';
                echo '</div>';
                echo '<div class="user-actions">';
                echo '<button type="button" class="write" onclick="window.location.href=\'?page=messages\'""><img src="../images/icons/wiadomosci.svg" alt="Napisz"></button>';
                echo '<button type="button" class="edit" onclick="window.location.href=\'./user_edit/edit_user.php?login=' . urlencode($admin['LOGIN']) . '\'""><img src="../images/icons/edit.svg" alt="Edytuj"></button>';
                echo '<button type="button" class="delete" onclick="if(confirm(\'Usunac uzytkownika?\')){window.location.href=\'?page=users&delete_login=' . urlencode($admin['LOGIN']) . '\';}"><img src="../images/icons/delete.svg" alt="Usun"></button>';
                echo '</div>';
                echo '</div>';
            }
            ?>
            <button type="button" class="add" onclick="window.location.href='./user_add/add_user.php?role=admin'">
                +
            </button>
        </section>

        <section class="secretaries">
            <h1>Recepcjonisty</h1>
            <?php
            $secretaries = getUsersByRole($pdo, 2);
            foreach ($secretaries as $secretary) {
                echo '<div class="user-card">';
                echo '<div class="chat-item-avatar color_placeholder"></div>';
                echo '<div class="user-info">';
                echo '<h3>' . htmlspecialchars($secretary['IMIE'] . ' ' . $secretary['NAZWISKO']) . '</h3>';
                echo '<p>Login: ' . htmlspecialchars($secretary['LOGIN']) . '</p>';
                echo '</div>';
                echo '<div class="user-actions">';
                echo '<button type="button" class="write" onclick="window.location.href=\'?page=messages\'""><img src="../images/icons/wiadomosci.svg" alt="Napisz"></button>';
                echo '<button type="button" class="edit" onclick="window.location.href=\'./user_edit/edit_user.php?login=' . urlencode($secretary['LOGIN']) . '\'""><img src="../images/icons/edit.svg" alt="Edytuj"></button>';
                echo '<button type="button" class="delete" onclick="if(confirm(\'Usunac uzytkownika?\')){window.location.href=\'?page=users&delete_login=' . urlencode($secretary['LOGIN']) . '\';}"><img src="../images/icons/delete.svg" alt="Usun"></button>';
                echo '</div>';
                echo '</div>';
            }
            ?>
            <button type="button" class="add" onclick="window.location.href='./user_add/add_user.php?role=secretary'">
                +
            </button>
        </section>

        <section class="workers">
            <h1>Pracownicy</h1>
            <?php
            $workers = getUsersByRole($pdo, 3);
            foreach ($workers as $worker) {
                echo '<div class="user-card">';
                echo '<div class="chat-item-avatar color_placeholder"></div>';
                echo '<div class="user-info">';
                echo '<h3>' . htmlspecialchars($worker['IMIE'] . ' ' . $worker['NAZWISKO']) . '</h3>';
                echo '<p>Login: ' . htmlspecialchars($worker['LOGIN']) . '</p>';
                echo '</div>';
                echo '<div class="user-actions">';
                echo '<button type="button" class="write" onclick="window.location.href=\'?page=messages\'""><img src="../images/icons/wiadomosci.svg" alt="Napisz"></button>';
                echo '<button type="button" class="edit" onclick="window.location.href=\'./user_edit/edit_user.php?login=' . urlencode($worker['LOGIN']) . '\'""><img src="../images/icons/edit.svg" alt="Edytuj"></button>';
                echo '<button type="button" class="delete" onclick="if(confirm(\'Usunac uzytkownika?\')){window.location.href=\'?page=users&delete_login=' . urlencode($worker['LOGIN']) . '\';}"><img src="../images/icons/delete.svg" alt="Usun"></button>';
                echo '</div>';
                echo '</div>';
            }
            ?>
            <button type="button" class="add" onclick="window.location.href='./user_add/add_user.php?role=worker'">
                +
            </button>
        </section>
    </main>
</div>

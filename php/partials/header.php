<header id="app-header" class="color_1 outline">
    <img src="../images/color-logo.png" alt="Logo" id="app-logo" />
    <div id="app-account" class="color_3">
        <?php
        $fullName = trim((string)($_SESSION['imie'] ?? '') . ' ' . (string)($_SESSION['nazwisko'] ?? ''));
        $displayName = $fullName !== '' ? $fullName : 'konto';
        $login = (string)($_SESSION['user_email'] ?? $displayName);
        ?>
        <a href="logout.php" class="nav-link logout-link" title="Wyloguj">
            <span class="logout-primary"><?php echo htmlspecialchars($displayName); ?></span>
            <span class="logout-hover">
                <span class="logout-login"><?php echo htmlspecialchars($login); ?></span>
                <span class="logout-icon-slot" aria-hidden="true"></span>
            </span>
        </a>
    </div>
</header>

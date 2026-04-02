<header id="app-header" class="color_1 outline">
    <img src="../images/color-logo.png" alt="Logo" id="app-logo" />
    <div id="app-account" class="color_3">
        <a href="logout.php" class="nav-link">
            <?php echo htmlspecialchars($_SESSION['user_email'] ?? 'konto'); ?>
        </a>
    </div>
</header>

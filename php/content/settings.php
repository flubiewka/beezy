<div id="content-settings" class="content-section">
    <div>
        <h2>Ustawienia</h2>

        <div class="settings-card">
            <h3>Motyw</h3>
            <p>Przelacz miedzy light i dark. Zmiana zapisuje sie automatycznie.</p>

            <div class="settings-row">
                <div class="theme-toggle-row">
                    <span>Light</span>
                    <label class="theme-switch" for="theme-toggle">
                        <input type="checkbox" id="theme-toggle">
                        <span class="theme-switch-slider"></span>
                    </label>
                    <span>Dark</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    var toggle = document.getElementById('theme-toggle');
    if (!toggle || !window.BeezyTheme) {
        return;
    }

    toggle.checked = window.BeezyTheme.get() === 'dark';

    toggle.addEventListener('change', function () {
        window.BeezyTheme.set(toggle.checked ? 'dark' : 'light');
    });
})();
</script>


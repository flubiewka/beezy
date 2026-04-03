(function () {
    const STORAGE_KEY = "beezy_theme";
    const LINK_ID = "theme-colors";

    function normalize(theme) {
        return theme === "dark" ? "dark" : "light";
    }

    function apply(theme) {
        const safeTheme = normalize(theme);
        const link = document.getElementById(LINK_ID);

        if (link) {
            link.href = "/me-u/css/theme-" + safeTheme + ".css";
        }

        document.documentElement.setAttribute("data-theme", safeTheme);
        return safeTheme;
    }

    function getTheme() {
        try {
            return normalize(localStorage.getItem(STORAGE_KEY) || "light");
        } catch (e) {
            return "light";
        }
    }

    function setTheme(theme) {
        const safeTheme = normalize(theme);
        try {
            localStorage.setItem(STORAGE_KEY, safeTheme);
        } catch (e) {}
        apply(safeTheme);
        return safeTheme;
    }

    apply(getTheme());

    window.BeezyTheme = {
        get: getTheme,
        set: setTheme,
    };
})();

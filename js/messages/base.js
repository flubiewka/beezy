(function (global) {
    const app = global.BeezyMessages || {};

    app.area = document.getElementById("messages-area");
    app.input = document.getElementById("message-input");
    app.form = document.getElementById("message-form");
    app.chatList = document.getElementById("chat-list");
    app.headerUser = document.getElementById("chat-header-user");

    app.me = app.area ? app.area.dataset.userLogin || "" : "";
    app.apiUrl = app.area ? app.area.dataset.apiUrl || "api.php" : "api.php";
    app.currentChatId = 0;
    app.chats = [];

    app.isReady = function () {
        return !!(
            app.area &&
            app.input &&
            app.form &&
            app.chatList &&
            app.headerUser
        );
    };

    app.escapeHtml = function (text) {
        return String(text)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    };

    app.avatarUrl = function (seed) {
        return (
            "https://api.dicebear.com/9.x/bottts/svg?seed=" +
            encodeURIComponent(String(seed || "user"))
        );
    };

    app.scrollToBottom = function () {
        app.area.scrollTop = app.area.scrollHeight;
    };

    app.get = async function (action, paramsObj) {
        const params = new URLSearchParams({ action: action });
        if (paramsObj) {
            Object.keys(paramsObj).forEach(function (key) {
                params.set(key, paramsObj[key]);
            });
        }
        return fetch(app.apiUrl + "?" + params.toString()).then(function (r) {
            return r.json();
        });
    };

    app.post = async function (action, fieldsObj) {
        const data = new FormData();
        data.append("action", action);
        if (fieldsObj) {
            Object.keys(fieldsObj).forEach(function (key) {
                data.append(key, fieldsObj[key]);
            });
        }
        return fetch(app.apiUrl, { method: "POST", body: data }).then(
            function (r) {
                return r.json();
            },
        );
    };

    global.BeezyMessages = app;
})(window);

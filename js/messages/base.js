(function (global) {
    const App = global.BeezyMessages || {};

    App.initContext = function () {
        const area = document.getElementById("messages-area");
        const input = document.getElementById("message-input");
        const form = document.getElementById("message-form");
        const chatList = document.getElementById("chat-list");
        const headerUser = document.getElementById("chat-header-user");

        if (!area || !input || !form || !chatList || !headerUser) {
            return null;
        }

        return {
            area: area,
            input: input,
            form: form,
            chatList: chatList,
            headerUser: headerUser,
            me: area.dataset.userLogin || "",
            currentChatId: 0,
            chats: [],
        };
    };

    App.esc = function (text) {
        return String(text)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    };

    App.scrollToBottom = function (ctx) {
        ctx.area.scrollTop = ctx.area.scrollHeight;
    };

    App.request = function (action, fields) {
        const data = new FormData();
        data.append("action", action);

        if (fields) {
            for (const key in fields) {
                data.append(key, fields[key]);
            }
        }

        return fetch("api.php", { method: "POST", body: data }).then(
            function (res) {
                return res.json();
            },
        );
    };

    App.requestGet = function (action, fields) {
        const params = new URLSearchParams();
        params.set("action", action);

        if (fields) {
            for (const key in fields) {
                params.set(key, fields[key]);
            }
        }

        return fetch("api.php?" + params.toString()).then(function (res) {
            return res.json();
        });
    };

    global.BeezyMessages = App;
})(window);

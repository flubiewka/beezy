(function (global) {
    const App = global.BeezyMessages || {};

    App.roleLabel = function (roleName) {
        switch ((roleName || "").toUpperCase()) {
            case "ADMIN":
                return "administrator";
            case "SEKRETARZ":
                return "sekretarz";
            case "PRACOWNIK":
                return "pracownik";
            default:
                return "uzytkownik";
        }
    };

    App.buildChatHtml = function (chat, isActive) {
        return (
            '<div class="chat-item color_4' +
            (isActive ? " active" : "") +
            '" data-chat-id="' +
            Number(chat.ID_CHAT) +
            '">' +
            '<div class="chat-item-avatar color_placeholder"></div>' +
            '<div class="chat-item-info">' +
            '<div class="chat-item-name">' +
            App.esc(chat.OTHER_IMIE || "") +
            " " +
            App.esc(chat.OTHER_NAZWISKO || "") +
            "</div>" +
            '<div class="chat-item-role">' +
            App.esc(App.roleLabel(chat.OTHER_ROLE_NAME)) +
            "</div>" +
            "</div>" +
            "</div>"
        );
    };

    App.renderChats = function (ctx) {
        if (!ctx.chats.length) {
            ctx.chatList.innerHTML =
                '<div class="chat-list-empty">Brak aktywnych chatow</div>';
            ctx.headerUser.textContent = "Brak wybranego chatu";
            ctx.area.innerHTML = "";
            return;
        }

        let html = "";
        for (const chat of ctx.chats) {
            html += App.buildChatHtml(
                chat,
                Number(chat.ID_CHAT) === Number(ctx.currentChatId),
            );
        }

        ctx.chatList.innerHTML = html;
    };

    App.updateHeaderUser = function (ctx) {
        const selected = ctx.chats.find(function (chat) {
            return Number(chat.ID_CHAT) === Number(ctx.currentChatId);
        });

        if (!selected) {
            ctx.headerUser.textContent = "Wybierz chat";
            return;
        }

        ctx.headerUser.textContent =
            (selected.OTHER_IMIE || "") + " " + (selected.OTHER_NAZWISKO || "");
    };

    App.buildMessageHtml = function (ctx, msg) {
        const mine = (msg.SENDER_LOGIN || "") === ctx.me;
        const isDeleted = msg.IS_DELETED == 1 || msg.IS_DELETED === true;

        const deleteBtn =
            mine && !isDeleted
                ? '<button type="button" class="message-delete-btn color_3" data-delete-id="' +
                  Number(msg.ID_MESSAGE) +
                  '">&#128465;</button>'
                : "";

        const contentHtml = isDeleted
            ? '<em class="message-deleted-info">Wiadomosc zostala usunieta</em>'
            : App.esc(msg.CONTENT);

        const cssClass =
            (mine ? "sent" : "received") + (isDeleted ? " deleted" : "");

        const headerHtml = mine
            ? '<span class="message-author">' +
              App.esc(msg.IMIE) +
              " " +
              App.esc(msg.NAZWISKO) +
              "</span>" +
              '<div class="message-avatar color_placeholder"></div>' +
              deleteBtn
            : '<div class="message-avatar color_placeholder"></div>' +
              '<span class="message-author">' +
              App.esc(msg.IMIE) +
              " " +
              App.esc(msg.NAZWISKO) +
              "</span>" +
              deleteBtn;

        return (
            '<div class="message ' +
            cssClass +
            '">' +
            '<div class="message-header-info">' +
            headerHtml +
            "</div>" +
            '<div class="message-content">' +
            contentHtml +
            "</div>" +
            "</div>"
        );
    };

    App.refreshMessages = function (ctx) {
        if (!ctx.currentChatId) {
            ctx.area.innerHTML = "";
            return Promise.resolve();
        }

        return App.requestGet("get_messages", {
            chat_id: String(ctx.currentChatId),
        })
            .then(function (messages) {
                let html = "";

                for (const msg of messages) {
                    html += App.buildMessageHtml(ctx, msg);
                }

                ctx.area.innerHTML = html;
                App.scrollToBottom(ctx);
            })
            .catch(function () {});
    };

    App.loadChats = function (ctx) {
        return App.requestGet("get_chats")
            .then(function (chats) {
                ctx.chats = Array.isArray(chats) ? chats : [];

                if (!ctx.currentChatId && ctx.chats.length) {
                    ctx.currentChatId = Number(ctx.chats[0].ID_CHAT);
                }

                App.renderChats(ctx);
                App.updateHeaderUser(ctx);
            })
            .catch(function () {
                ctx.chats = [];
                ctx.currentChatId = 0;
                App.renderChats(ctx);
                App.updateHeaderUser(ctx);
            });
    };

    global.BeezyMessages = App;
})(window);

(function (global) {
    const app = global.BeezyMessages || {};

    const roleMap = {
        ADMIN: "administrator",
        SEKRETARZ: "sekretarz",
        PRACOWNIK: "pracownik",
    };

    function roleLabel(roleName) {
        return roleMap[String(roleName || "").toUpperCase()] || "uzytkownik";
    }

    app.renderChats = function () {
        if (!app.chats.length) {
            app.chatList.innerHTML =
                '<div class="chat-list-empty">Brak aktywnych chatow</div>';
            app.headerUser.textContent = "Brak wybranego chatu";
            app.area.innerHTML = "";
            return;
        }

        const html = app.chats
            .map(function (chat) {
                const isActive =
                    Number(chat.ID_CHAT) === Number(app.currentChatId);
                return (
                    '<div class="chat-item color_4' +
                    (isActive ? " active" : "") +
                    '" data-chat-id="' +
                    Number(chat.ID_CHAT) +
                    '">' +
                    '<img class="chat-item-avatar" src="' +
                    app.avatarUrl(chat.OTHER_LOGIN) +
                    '" alt="Avatar" loading="lazy">' +
                    '<div class="chat-item-info">' +
                    '<div class="chat-item-name">' +
                    app.escapeHtml(chat.OTHER_IMIE || "") +
                    " " +
                    app.escapeHtml(chat.OTHER_NAZWISKO || "") +
                    "</div>" +
                    '<div class="chat-item-role">' +
                    app.escapeHtml(roleLabel(chat.OTHER_ROLE_NAME)) +
                    "</div>" +
                    "</div>" +
                    "</div>"
                );
            })
            .join("");

        app.chatList.innerHTML = html;
    };

    app.renderHeader = function () {
        const selected = app.chats.find(function (chat) {
            return Number(chat.ID_CHAT) === Number(app.currentChatId);
        });

        if (!selected) {
            app.headerUser.textContent = "Wybierz chat";
            return;
        }

        app.headerUser.textContent =
            (selected.OTHER_IMIE || "") + " " + (selected.OTHER_NAZWISKO || "");
    };

    app.loadChats = async function () {
        try {
            const chats = await app.get("get_chats");
            app.chats = Array.isArray(chats) ? chats : [];

            if (!app.currentChatId && app.chats.length > 0) {
                app.currentChatId = Number(app.chats[0].ID_CHAT);
            }
        } catch (e) {
            app.chats = [];
            app.currentChatId = 0;
        }

        app.renderChats();
        app.renderHeader();
    };

    app.loadMessages = async function () {
        if (!app.currentChatId) {
            app.area.innerHTML = "";
            return;
        }

        try {
            const messages = await app.get("get_messages", {
                chat_id: String(app.currentChatId),
            });

            const html = messages
                .map(function (msg) {
                    const mine = (msg.SENDER_LOGIN || "") === app.me;
                    const isDeleted =
                        msg.IS_DELETED == 1 || msg.IS_DELETED === true;
                    const cssClass =
                        (mine ? "sent" : "received") +
                        (isDeleted ? " deleted" : "");

                    let deleteBtn = "";
                    if (mine && !isDeleted) {
                        deleteBtn =
                            '<button type="button" class="message-delete-btn" data-delete-id="' +
                            Number(msg.ID_MESSAGE) +
                            '"><img src="../images/icons/delete.svg" alt="Usun"></button>';
                    }

                    const contentHtml = isDeleted
                        ? '<em class="message-deleted-info">Wiadomosc zostala usunieta</em>'
                        : app.escapeHtml(msg.CONTENT);

                    const avatar =
                        '<img class="message-avatar" src="' +
                        app.avatarUrl(msg.SENDER_LOGIN) +
                        '" alt="Avatar" loading="lazy">';

                    let headerHtml = "";
                    if (mine) {
                        headerHtml =
                            '<span class="message-author">' +
                            app.escapeHtml(msg.IMIE) +
                            " " +
                            app.escapeHtml(msg.NAZWISKO) +
                            "</span>" +
                            avatar +
                            deleteBtn;
                    } else {
                        headerHtml =
                            avatar +
                            '<span class="message-author">' +
                            app.escapeHtml(msg.IMIE) +
                            " " +
                            app.escapeHtml(msg.NAZWISKO) +
                            "</span>" +
                            deleteBtn;
                    }

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
                })
                .join("");

            app.area.innerHTML = html;
            app.scrollToBottom();
        } catch (e) {
            app.area.innerHTML = "";
        }
    };

    global.BeezyMessages = app;
})(window);

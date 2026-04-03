(function (global) {
    const app = global.BeezyMessages || {};

    app.bindEvents = function () {
        async function refreshMessages() {
            await app.loadMessages();
        }

        app.chatList.addEventListener("click", async function (e) {
            const item = e.target.closest("[data-chat-id]");
            if (!item) {
                return;
            }

            const chatId = Number(item.getAttribute("data-chat-id"));
            if (!chatId) {
                return;
            }

            if (chatId === Number(app.currentChatId)) {
                return;
            }

            app.currentChatId = chatId;
            app.renderChats();
            app.renderHeader();
            await refreshMessages();
        });

        app.form.addEventListener("submit", async function (e) {
            e.preventDefault();

            if (!app.currentChatId) {
                return;
            }

            const content = app.input.value.trim();
            if (!content) {
                return;
            }

            try {
                await app.post("send_message", {
                    chat_id: String(app.currentChatId),
                    content: content,
                });
                app.input.value = "";
                await refreshMessages();
            } catch (e2) {}
        });

        app.area.addEventListener("click", async function (e) {
            const btn = e.target.closest("[data-delete-id]");
            if (!btn) {
                return;
            }

            const messageId = Number(btn.getAttribute("data-delete-id"));
            if (!messageId) {
                return;
            }

            try {
                await app.post("delete_message", {
                    message_id: String(messageId),
                });
                await refreshMessages();
            } catch (e2) {}
        });
    };

    global.BeezyMessages = app;
})(window);

(function (global) {
    const App = global.BeezyMessages || {};

    App.bindEvents = function (ctx) {
        ctx.chatList.addEventListener("click", function (e) {
            const item = e.target.closest("[data-chat-id]");
            if (!item) {
                return;
            }

            const chatId = Number(item.getAttribute("data-chat-id"));
            if (!chatId || chatId === Number(ctx.currentChatId)) {
                return;
            }

            ctx.currentChatId = chatId;
            App.renderChats(ctx);
            App.updateHeaderUser(ctx);
            App.refreshMessages(ctx);
        });

        ctx.form.addEventListener("submit", function (e) {
            e.preventDefault();

            if (!ctx.currentChatId) {
                return;
            }

            const content = ctx.input.value.trim();
            if (!content) {
                return;
            }

            App.request("send_message", {
                chat_id: String(ctx.currentChatId),
                content: content,
            })
                .then(function () {
                    ctx.input.value = "";
                    App.refreshMessages(ctx);
                })
                .catch(function () {});
        });

        ctx.area.addEventListener("click", function (e) {
            const btn = e.target.closest("[data-delete-id]");
            if (!btn) {
                return;
            }

            const messageId = Number(btn.getAttribute("data-delete-id"));
            if (!messageId) {
                return;
            }

            App.request("delete_message", { message_id: String(messageId) })
                .then(function () {
                    App.refreshMessages(ctx);
                })
                .catch(function () {});
        });
    };

    global.BeezyMessages = App;
})(window);

(function (global) {
    const app = global.BeezyMessages || {};

    if (!app.isReady || !app.isReady()) {
        return;
    }

    app.bindEvents();

    async function start() {
        const recipient = (
            new URLSearchParams(window.location.search).get("recipient") || ""
        ).trim();

        if (recipient && recipient !== app.me) {
            try {
                const result = await app.get("get_or_create_direct_chat", {
                    recipient: recipient,
                });

                if (result && result.success && Number(result.chat_id) > 0) {
                    app.currentChatId = Number(result.chat_id);
                }
            } catch (e) {}
        }

        await app.loadChats();
        await app.loadMessages();
    }

    start();
})(window);

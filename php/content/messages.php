<div id="content-messages" class="content-section active">
    <div id="chat-list" class="chat-list box-TEMPLATE color_2"></div>

    <div class="message-container box-TEMPLATE color_2">
        <div class="message-header color_3">
            <div class="message-header-user">
                <button type="button" id="chat-header-user" class="message-header-button color_3">
                    Wybierz chat
                </button>
            </div>
        </div>

        <div
            id="messages-area"
            class="messages-area color_5"
            data-user-login="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
            data-api-url="<?php echo htmlspecialchars($appBaseUrl . '/php/api.php'); ?>"
        ></div>

        <form id="message-form" class="message-footer color_3">
            <input
                type="text"
                id="message-input"
                class="message-input"
                placeholder="Wpisz wiadomosc"
                autocomplete="off"
            >
            <button type="submit" class="message-send-btn color_3">Wyślij</button>
<?php if (($_SESSION['role_id'] ?? 0) == 2): ?>
    <button type="button" class="message-urgent-btn color_3" id="urgent-send-btn">
        &#9888; Pilne
    </button>
<?php endif; ?>
        </form>
    </div>
</div>

<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/base.js'); ?>"></script>
<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/render.js'); ?>"></script>
<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/events.js'); ?>"></script>
<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/main.js'); ?>"></script>

<script>
(function() {
    const urgentBtn = document.getElementById('urgent-send-btn');
    if (!urgentBtn) return;
    urgentBtn.addEventListener('click', function() {
        const app = window.BeezyMessages;
        if (!app || !app.currentChatId) return;
        const content = app.input.value.trim();
        if (!content) return;
        app.post('send_message', {
            chat_id: app.currentChatId,
            content: content,
            is_urgent: '1'
        }).then(function(res) {
            if (res.success) {
                app.input.value = '';
                app.get('get_messages', { chat_id: app.currentChatId })
                   .then(function(data) {
                       if (data && data.messages) {
                           app.area.innerHTML = '';
                           data.messages.forEach(app.renderMessage);
                           app.scrollToBottom();
                       }
                   });
            }
        });
    });
})();
</script>
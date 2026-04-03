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
            <button type="submit" class="message-send-btn color_3">Wyslij</button>
        </form>
    </div>
</div>

<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/base.js'); ?>"></script>
<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/render.js'); ?>"></script>
<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/events.js'); ?>"></script>
<script src="<?php echo htmlspecialchars($appBaseUrl . '/js/messages/main.js'); ?>"></script>
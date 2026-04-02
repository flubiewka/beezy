<div id="content-messages" class="content-section active">
    <div class="chat-list box-TEMPLATE color_2">
        <div class="chat-item color_4">
            <div class="chat-item-avatar color_placeholder"></div>
            <div class="chat-item-info">
                <div class="chat-item-name">
                    <?php echo htmlspecialchars(($_SESSION['imie'] ?? '') . ' ' . ($_SESSION['nazwisko'] ?? '')); ?>
                </div>
                <div class="chat-item-role">uzytkownik</div>
            </div>
        </div>
    </div>

    <div class="message-container box-TEMPLATE color_2">
        <div class="message-header color_3">
            <div class="message-header-user">
                <button type="button" class="message-header-button color_3">
                    <div class="chat-item-avatar color_placeholder" bis_skin_checked="1"></div>
                    <?php echo htmlspecialchars(($_SESSION['imie'] ?? '') . ' ' . ($_SESSION['nazwisko'] ?? '')); ?>
                </button>
            </div>
        </div>

        <div
            id="messages-area"
            class="messages-area color_5"
            data-user-login="<?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?>"
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

<script src="../js/messages.js"></script>
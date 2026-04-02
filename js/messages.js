(function () {
    const area = document.getElementById("messages-area");
    const input = document.getElementById("message-input");
    const form = document.getElementById("message-form");

    if (!area || !input || !form) {
        return;
    }

    const me = area.dataset.userLogin || "";

    function esc(text) {
        return String(text)
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }

    function scrollToBottom() {
        area.scrollTop = area.scrollHeight;
    }

    function request(action, fields) {
        if (!action) {
            return fetch("api.php").then(function (res) {
                return res.json();
            });
        }

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
    }

    function refreshMessages() {
        request()
            .then(function (messages) {
                let html = "";

                for (const msg of messages) {
                    const mine = (msg.SENDER_LOGIN || "") === me;
                    const isDeleted =
                        msg.IS_DELETED == 1 || msg.IS_DELETED === true;

                    const deleteBtn =
                        mine && !isDeleted
                            ? '<button type="button" class="message-delete-btn color_3" data-delete-id="' +
                              Number(msg.ID_MESSAGE) +
                              '">&#128465;</button>'
                            : "";

                    const contentHtml = isDeleted
                        ? '<em class="message-deleted-info">Wiadomość została usunięta</em>'
                        : esc(msg.CONTENT);

                    const cssClass =
                        (mine ? "sent" : "received") +
                        (isDeleted ? " deleted" : "");

                    const headerHtml = mine
                        ? '<span class="message-author">' +
                          esc(msg.IMIE) +
                          " " +
                          esc(msg.NAZWISKO) +
                          "</span>" +
                          '<div class="message-avatar color_placeholder"></div>' +
                          deleteBtn
                        : '<div class="message-avatar color_placeholder"></div>' +
                          '<span class="message-author">' +
                          esc(msg.IMIE) +
                          " " +
                          esc(msg.NAZWISKO) +
                          "</span>" +
                          deleteBtn;

                    html +=
                        '<div class="message ' +
                        cssClass +
                        '">' +
                        '<div class="message-header-info">' +
                        headerHtml +
                        "</div>" +
                        '<div class="message-content">' +
                        contentHtml +
                        "</div>" +
                        "</div>";
                }

                area.innerHTML = html;
                scrollToBottom();
            })
            .catch(function () {});
    }

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const content = input.value.trim();
        if (!content) {
            return;
        }

        request("send_message", { content: content })
            .then(function () {
                input.value = "";
                refreshMessages();
            })
            .catch(function () {});
    });

    area.addEventListener("click", function (e) {
        const btn = e.target.closest("[data-delete-id]");
        if (!btn) {
            return;
        }

        const messageId = Number(btn.getAttribute("data-delete-id"));
        if (!messageId) {
            return;
        }

        request("delete_message", { message_id: String(messageId) })
            .then(function () {
                refreshMessages();
            })
            .catch(function () {});
    });

    refreshMessages();
})();

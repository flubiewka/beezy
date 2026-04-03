(function () {
    const root = document.getElementById("content-raport");
    if (!root) {
        return;
    }

    const renderTs = Number(root.dataset.renderTs || 0);
    if (renderTs <= 0) {
        return;
    }

    const currentLogin = root.dataset.currentLogin || "";
    const dayRows = Array.from(root.querySelectorAll('tr[data-day-row="1"]'));
    const loadAllButtons = Array.from(
        root.querySelectorAll(".load-all-logs-btn"),
    );
    const myTotalNode = document.getElementById("my-total-time");
    const allTotalNode = document.getElementById("all-total-time");

    if (dayRows.length === 0) {
        return;
    }

    let cells = getDurationCells();

    function getDurationCells() {
        return Array.from(root.querySelectorAll('[data-duration-cell="1"]'));
    }

    function formatDuration(totalSeconds) {
        const safe = Math.max(0, Number(totalSeconds) || 0);
        const hours = Math.floor(safe / 3600);
        const minutes = Math.floor((safe % 3600) / 60);
        const seconds = safe % 60;

        return (
            String(hours).padStart(2, "0") +
            ":" +
            String(minutes).padStart(2, "0") +
            ":" +
            String(seconds).padStart(2, "0")
        );
    }

    function updateDurationCells(elapsed) {
        for (let i = 0; i < cells.length; i += 1) {
            const cell = cells[i];
            const baseSeconds = Number(cell.dataset.baseSeconds || 0);
            const isActive = cell.dataset.active === "1";
            const currentSeconds = baseSeconds + (isActive ? elapsed : 0);

            cell.dataset.currentSeconds = String(currentSeconds);
            cell.textContent = formatDuration(currentSeconds);
        }
    }

    function updateTotals() {
        let myTotal = 0;
        let allTotal = 0;

        for (let i = 0; i < dayRows.length; i += 1) {
            const row = dayRows[i];
            const rowLogin = row.dataset.login || "";
            const dayTotalNode = row.querySelector(".day-total-cell");
            const dayCells = row.querySelectorAll(".duration-cell");

            let dayTotal = 0;
            for (let j = 0; j < dayCells.length; j += 1) {
                const cell = dayCells[j];
                dayTotal += Number(
                    cell.dataset.currentSeconds ||
                        cell.dataset.baseSeconds ||
                        0,
                );
            }

            if (dayTotalNode) {
                dayTotalNode.textContent = formatDuration(dayTotal);
            }

            allTotal += dayTotal;
            if (rowLogin === currentLogin) {
                myTotal += dayTotal;
            }
        }

        if (myTotalNode) {
            myTotalNode.textContent = formatDuration(myTotal);
        }

        if (allTotalNode) {
            allTotalNode.textContent = formatDuration(allTotal);
        }
    }

    function tick() {
        const nowTs = Math.floor(Date.now() / 1000);
        const elapsed = Math.max(0, nowTs - renderTs);

        updateDurationCells(elapsed);
        updateTotals();
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#39;");
    }

    function buildAllLogsRows(logs) {
        let html = "";

        for (let i = 0; i < logs.length; i += 1) {
            const log = logs[i];
            const start = escapeHtml(log.START_TIME || "");
            const end = escapeHtml(log.END_TIME || "-");
            const baseSeconds = Number(log.DURATION_SECONDS || 0);
            const isActive = !log.END_TIME;

            html +=
                "<tr>" +
                "<td>" +
                start +
                "</td>" +
                "<td>" +
                end +
                "</td>" +
                '<td data-duration-cell="1" data-base-seconds="' +
                baseSeconds +
                '" data-active="' +
                (isActive ? "1" : "0") +
                '">' +
                formatDuration(baseSeconds) +
                "</td>" +
                "</tr>";
        }

        return html;
    }

    function renderAllLogs(container, logs) {
        if (!Array.isArray(logs) || logs.length === 0) {
            container.innerHTML = "<p>Brak historii logowan.</p>";
            return;
        }

        let isActiveNow = false;
        for (let i = 0; i < logs.length; i += 1) {
            if (!logs[i].END_TIME) {
                isActiveNow = true;
                break;
            }
        }

        const activityLabel = isActiveNow ? "Aktywny teraz" : "Nieaktywny";
        const activityClass = isActiveNow
            ? "report-all-activity is-active"
            : "report-all-activity";
        const rows = buildAllLogsRows(logs);

        container.innerHTML =
            '<div class="' +
            activityClass +
            '">Aktywnosc teraz: ' +
            activityLabel +
            "</div>" +
            '<div class="report-all-logs-wrap">' +
            '<table class="report-all-logs-table">' +
            "<thead>" +
            "<tr>" +
            "<th>Start</th>" +
            "<th>Koniec</th>" +
            "<th>Czas</th>" +
            "</tr>" +
            "</thead>" +
            "<tbody>" +
            rows +
            "</tbody>" +
            "</table>" +
            "</div>";
    }

    async function loadAllLogs(button, container, login) {
        button.disabled = true;
        button.textContent = "Ladowanie...";

        try {
            const response = await fetch(
                "api.php?action=get_work_logs&login=" +
                    encodeURIComponent(login),
                {
                    credentials: "same-origin",
                },
            );
            const data = await response.json();

            if (!data || data.success !== true || !Array.isArray(data.logs)) {
                container.innerHTML =
                    "<p>Nie udalo sie pobrac historii logowan.</p>";
                button.dataset.loaded = "0";
                button.textContent = "Sprobuj ponownie zaladowac historie";
            } else {
                renderAllLogs(container, data.logs);
                button.dataset.loaded = "1";
                button.textContent = "Ukryj cala historie tego pracownika";
            }

            container.hidden = false;
        } catch (e) {
            container.innerHTML =
                "<p>Blad podczas ladowania historii logowan.</p>";
            container.hidden = false;
            button.dataset.loaded = "0";
            button.textContent = "Sprobuj ponownie zaladowac historie";
        } finally {
            button.disabled = false;
            cells = getDurationCells();
            tick();
        }
    }

    function setupLoadButtons() {
        for (let i = 0; i < loadAllButtons.length; i += 1) {
            const button = loadAllButtons[i];

            button.addEventListener("click", function () {
                const login = button.dataset.login || "";
                const details = button.closest("details");
                const container = details
                    ? details.querySelector(".all-logs-container")
                    : null;

                if (!container || login === "") {
                    return;
                }

                if (button.dataset.loaded === "1") {
                    container.hidden = !container.hidden;
                    button.textContent = container.hidden
                        ? "Pokaz cala historie tego pracownika"
                        : "Ukryj cala historie tego pracownika";
                    return;
                }

                loadAllLogs(button, container, login);
            });
        }
    }

    setupLoadButtons();
    tick();
    setInterval(tick, 1000);
})();

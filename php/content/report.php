<?php
$currentLogin = isset($_SESSION['user_id']) ? (string)$_SESSION['user_id'] : '';
$canViewAll = canViewAllSessions($_SESSION['role_id'] ?? 0);
$sessions = [];
$summary = ['my_seconds' => 0, 'all_seconds' => 0];
$dailyRows = [];
$activeByLogin = [];

if ($currentLogin !== '') {
    $sessions = getMonthlyWorkSessions($pdo, $currentLogin, $canViewAll);
    $summary = getMonthlyWorkSummary($sessions, $currentLogin);

    foreach ($sessions as $session) {
        $rowLogin = (string)$session['LOGIN'];
        $dayKey = substr((string)$session['START_TIME'], 0, 10);
        $groupKey = $rowLogin . '|' . $dayKey;
        $person = trim(((string)$session['IMIE']) . ' ' . ((string)$session['NAZWISKO']));

        if (empty($session['END_TIME'])) {
            $activeByLogin[$rowLogin] = true;
        }

        if ($person === '') {
            $person = $rowLogin;
        } else {
            $person .= ' (' . $rowLogin . ')';
        }

        if (!isset($dailyRows[$groupKey])) {
            $dailyRows[$groupKey] = [
                'login' => $rowLogin,
                'person' => $person,
                'day' => $dayKey,
                'total_seconds' => 0,
                'sessions' => [],
            ];
        }

        $dailyRows[$groupKey]['total_seconds'] += (int)$session['DURATION_SECONDS'];
        $dailyRows[$groupKey]['sessions'][] = $session;
    }

    foreach ($dailyRows as $groupKey => $dailyRow) {
        $dailyRows[$groupKey]['is_active'] = !empty($activeByLogin[(string)$dailyRow['login']]);
    }
}

$monthLabel = date('m.Y');
$renderTs = time();
?>

<div id="content-raport" class="content-section" data-render-ts="<?php echo (int)$renderTs; ?>" data-current-login="<?php echo htmlspecialchars($currentLogin); ?>">
    <div style="padding: 30px;">
        <h2>Raport czasu pracy</h2>
        <p>Miesiac: <?php echo htmlspecialchars($monthLabel); ?></p>

        <div style="margin: 16px 0; display: flex; gap: 24px; flex-wrap: wrap;">
            <div>
                <strong>Twoj czas:</strong>
                <span id="my-total-time"><?php echo htmlspecialchars(formatWorkDuration($summary['my_seconds'])); ?></span>
            </div>

            <?php if ($canViewAll): ?>
                <div>
                    <strong>Czas wszystkich:</strong>
                    <span id="all-total-time"><?php echo htmlspecialchars(formatWorkDuration($summary['all_seconds'])); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <?php if (empty($sessions)): ?>
            <p>Brak sesji pracy w biezacym miesiacu.</p>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ccc;">Pracownik</th>
                            <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ccc;">Laczny czas za dzien</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailyRows as $dailyRow): ?>
                            <tr data-day-row="1" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>" style="vertical-align: top;">
                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                    <div><?php echo htmlspecialchars((string)$dailyRow['person']); ?></div>
                                    <div style="font-size: 12px; opacity: 0.8;">Dzien: <?php echo htmlspecialchars((string)$dailyRow['day']); ?></div>
                                    <div style="font-size: 12px; margin-top: 4px; color: <?php echo !empty($dailyRow['is_active']) ? '#0a7f32' : '#666'; ?>;">
                                        Aktywnosc: <?php echo !empty($dailyRow['is_active']) ? 'Aktywny teraz' : 'Nieaktywny'; ?>
                                    </div>

                                    <details style="margin-top: 8px;">
                                        <summary style="cursor: pointer;">Historia logowan tego dnia</summary>

                                        <div style="margin-top: 10px; overflow-x: auto;">
                                            <table style="width: 100%; border-collapse: collapse;">
                                                <thead>
                                                    <tr>
                                                        <th style="text-align:left; padding: 6px; border-bottom: 1px solid #ddd;">Start</th>
                                                        <th style="text-align:left; padding: 6px; border-bottom: 1px solid #ddd;">Koniec</th>
                                                        <th style="text-align:left; padding: 6px; border-bottom: 1px solid #ddd;">Czas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($dailyRow['sessions'] as $session): ?>
                                                        <?php
                                                        $baseSeconds = (int)$session['DURATION_SECONDS'];
                                                        $isActive = empty($session['END_TIME']);
                                                        ?>
                                                        <tr>
                                                            <td style="padding: 6px; border-bottom: 1px solid #f1f1f1;"><?php echo htmlspecialchars((string)$session['START_TIME']); ?></td>
                                                            <td style="padding: 6px; border-bottom: 1px solid #f1f1f1;"><?php echo htmlspecialchars((string)($session['END_TIME'] ?? '-')); ?></td>
                                                            <td style="padding: 6px; border-bottom: 1px solid #f1f1f1;" class="duration-cell" data-duration-cell="1" data-base-seconds="<?php echo (int)$baseSeconds; ?>" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>" data-active="<?php echo $isActive ? '1' : '0'; ?>">
                                                                <?php echo htmlspecialchars(formatWorkDuration($baseSeconds)); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <button type="button" class="load-all-logs-btn" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>" style="margin-top: 10px;">Zaladuj cala historie tego pracownika</button>
                                        <div class="all-logs-container" style="margin-top: 10px;"></div>
                                    </details>
                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #eee;" class="day-total-cell" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>" data-base-seconds="<?php echo (int)$dailyRow['total_seconds']; ?>">
                                    <?php echo htmlspecialchars(formatWorkDuration((int)$dailyRow['total_seconds'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
(function () {
    const root = document.getElementById('content-raport');
    if (!root) {
        return;
    }

    const renderTs = Number(root.dataset.renderTs || 0);
    const currentLogin = root.dataset.currentLogin || '';
    const cells = Array.from(root.querySelectorAll('[data-duration-cell="1"]'));
    const dayRows = Array.from(root.querySelectorAll('tr[data-day-row="1"]'));
    const loadAllButtons = Array.from(root.querySelectorAll('.load-all-logs-btn'));
    const myTotalNode = document.getElementById('my-total-time');
    const allTotalNode = document.getElementById('all-total-time');

    if (dayRows.length === 0 || renderTs <= 0) {
        return;
    }

    function formatDuration(totalSeconds) {
        const safe = Math.max(0, Number(totalSeconds) || 0);
        const hours = Math.floor(safe / 3600);
        const minutes = Math.floor((safe % 3600) / 60);
        const seconds = safe % 60;
        const pad = (n) => String(n).padStart(2, '0');
        return pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
    }

    function tick() {
        const nowTs = Math.floor(Date.now() / 1000);
        const elapsed = Math.max(0, nowTs - renderTs);

        cells.forEach((cell) => {
            const base = Number(cell.dataset.baseSeconds || 0);
            const active = cell.dataset.active === '1';
            const current = base + (active ? elapsed : 0);

            cell.textContent = formatDuration(current);
            cell.dataset.currentSeconds = String(current);
        });

        let myTotal = 0;
        let allTotal = 0;

        dayRows.forEach((row) => {
            const rowLogin = row.dataset.login || '';
            const dayTotalNode = row.querySelector('.day-total-cell');
            const dayCells = Array.from(row.querySelectorAll('.duration-cell'));

            let dayTotal = 0;
            dayCells.forEach((cell) => {
                dayTotal += Number(cell.dataset.currentSeconds || cell.dataset.baseSeconds || 0);
            });

            if (dayTotalNode) {
                dayTotalNode.textContent = formatDuration(dayTotal);
            }

            allTotal += dayTotal;
            if (rowLogin === currentLogin) {
                myTotal += dayTotal;
            }
        });

        if (myTotalNode) {
            myTotalNode.textContent = formatDuration(myTotal);
        }

        if (allTotalNode) {
            allTotalNode.textContent = formatDuration(allTotal);
        }
    }

    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function renderAllLogs(container, logs) {
        if (!Array.isArray(logs) || logs.length === 0) {
            container.innerHTML = '<p>Brak historii logowan.</p>';
            return;
        }

        const isActiveNow = logs.some((log) => !log.END_TIME);
        const activityLabel = isActiveNow ? 'Aktywny teraz' : 'Nieaktywny';
        const activityColor = isActiveNow ? '#0a7f32' : '#666';

        const rows = logs.map((log) => {
            const start = escapeHtml(log.START_TIME || '');
            const end = escapeHtml(log.END_TIME || '-');
            const login = escapeHtml(log.LOGIN || '');
            const baseSeconds = Number(log.DURATION_SECONDS || 0);
            const isActive = !log.END_TIME;

            return '<tr>' +
                '<td style="padding: 6px; border-bottom: 1px solid #f1f1f1;">' + start + '</td>' +
                '<td style="padding: 6px; border-bottom: 1px solid #f1f1f1;">' + end + '</td>' +
                '<td style="padding: 6px; border-bottom: 1px solid #f1f1f1;" data-duration-cell="1" data-base-seconds="' + baseSeconds + '" data-active="' + (isActive ? '1' : '0') + '">' + formatDuration(baseSeconds) + '</td>' +
            '</tr>';
        }).join('');

        container.innerHTML =
            '<div style="overflow-x:auto;">' +
                '<table style="width:100%; border-collapse: collapse;">' +
                    '<thead>' +
                        '<tr>' +
                            '<th style="text-align:left; padding: 6px; border-bottom: 1px solid #ddd;">Start</th>' +
                            '<th style="text-align:left; padding: 6px; border-bottom: 1px solid #ddd;">Koniec</th>' +
                            '<th style="text-align:left; padding: 6px; border-bottom: 1px solid #ddd;">Czas</th>' +
                        '</tr>' +
                    '</thead>' +
                    '<tbody>' + rows + '</tbody>' +
                '</table>' +
            '</div>';
    }

    loadAllButtons.forEach((button) => {
        button.addEventListener('click', async function () {
            const login = button.dataset.login || '';
            const details = button.closest('details');
            const container = details ? details.querySelector('.all-logs-container') : null;

            if (!container || login === '') {
                return;
            }

            if (button.dataset.loaded === '1') {
                container.hidden = !container.hidden;
                button.textContent = container.hidden
                    ? 'Pokaz cala historie tego pracownika'
                    : 'Ukryj cala historie tego pracownika';
                return;
            }

            button.disabled = true;
            button.textContent = 'Ladowanie...';

            try {
                const response = await fetch('api.php?action=get_work_logs&login=' + encodeURIComponent(login), {
                    credentials: 'same-origin'
                });
                const data = await response.json();

                if (!data || data.success !== true || !Array.isArray(data.logs)) {
                    container.innerHTML = '<p>Nie udalo sie pobrac historii logowan.</p>';
                } else {
                    renderAllLogs(container, data.logs);
                }

                button.dataset.loaded = '1';
                container.hidden = false;
                button.textContent = 'Ukryj cala historie tego pracownika';
            } catch (e) {
                container.innerHTML = '<p>Blad podczas ladowania historii logowan.</p>';
                container.hidden = false;
                button.textContent = 'Sprobuj ponownie zaladowac historie';
                button.dataset.loaded = '0';
            } finally {
                button.disabled = false;
                cells.length = 0;
                Array.prototype.push.apply(cells, root.querySelectorAll('[data-duration-cell="1"]'));
                tick();
            }
        });
    });

    tick();
    setInterval(tick, 1000);
})();
</script>


<?php
$currentLogin = isset($_SESSION['user_id']) ? (string)$_SESSION['user_id'] : '';
$canViewAll = canViewAllSessions($_SESSION['role_id'] ?? 0);
$sessions = [];
$summary = ['my_seconds' => 0, 'all_seconds' => 0];

if ($currentLogin !== '') {
    $sessions = getMonthlyWorkSessions($pdo, $currentLogin, $canViewAll);
    $summary = getMonthlyWorkSummary($sessions, $currentLogin);
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
                            <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ccc;">Start</th>
                            <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ccc;">Koniec</th>
                            <th style="text-align:left; padding: 8px; border-bottom: 1px solid #ccc;">Czas</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <?php
                            $person = trim(((string)$session['IMIE']) . ' ' . ((string)$session['NAZWISKO']));
                            $rowLogin = (string)$session['LOGIN'];
                            $baseSeconds = (int)$session['DURATION_SECONDS'];
                            $isActive = empty($session['END_TIME']);
                            if ($person === '') {
                                $person = $rowLogin;
                            } else {
                                $person .= ' (' . $rowLogin . ')';
                            }
                            ?>
                            <tr>
                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                    <?php echo htmlspecialchars($person); ?>
                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                    <?php echo htmlspecialchars((string)$session['START_TIME']); ?>
                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #eee;">
                                    <?php echo htmlspecialchars((string)($session['END_TIME'] ?? '-')); ?>
                                </td>
                                <td style="padding: 8px; border-bottom: 1px solid #eee;" class="duration-cell" data-base-seconds="<?php echo (int)$baseSeconds; ?>" data-login="<?php echo htmlspecialchars($rowLogin); ?>" data-active="<?php echo $isActive ? '1' : '0'; ?>">
                                    <?php echo htmlspecialchars(formatWorkDuration($baseSeconds)); ?>
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
    const cells = Array.from(root.querySelectorAll('.duration-cell'));
    const myTotalNode = document.getElementById('my-total-time');
    const allTotalNode = document.getElementById('all-total-time');

    if (cells.length === 0 || renderTs <= 0) {
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
        let myTotal = 0;
        let allTotal = 0;

        cells.forEach((cell) => {
            const base = Number(cell.dataset.baseSeconds || 0);
            const active = cell.dataset.active === '1';
            const rowLogin = cell.dataset.login || '';
            const current = base + (active ? elapsed : 0);

            cell.textContent = formatDuration(current);
            allTotal += current;
            if (rowLogin === currentLogin) {
                myTotal += current;
            }
        });

        if (myTotalNode) {
            myTotalNode.textContent = formatDuration(myTotal);
        }

        if (allTotalNode) {
            allTotalNode.textContent = formatDuration(allTotal);
        }
    }

    tick();
    setInterval(tick, 1000);
})();
</script>


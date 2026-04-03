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
    <div class="report-shell">
        <h2>Raport czasu pracy</h2>
        <p>Miesiac: <?php echo htmlspecialchars($monthLabel); ?></p>

        <div class="report-summary">
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
            <div class="report-table-wrap">
                <table class="report-main-table">
                    <thead>
                        <tr>
                            <th>Pracownik</th>
                            <th>Laczny czas za dzien</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($dailyRows as $dailyRow): ?>
                            <tr data-day-row="1" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>">
                                <td>
                                    <div><?php echo htmlspecialchars((string)$dailyRow['person']); ?></div>
                                    <div class="report-day">Dzien: <?php echo htmlspecialchars((string)$dailyRow['day']); ?></div>
                                    <div class="report-activity<?php echo !empty($dailyRow['is_active']) ? ' is-active' : ''; ?>">
                                        Aktywnosc: <?php echo !empty($dailyRow['is_active']) ? 'Aktywny teraz' : 'Nieaktywny'; ?>
                                    </div>

                                    <details class="report-day-details">
                                        <summary class="report-day-summary">Historia logowan tego dnia</summary>

                                        <div class="report-day-history-wrap">
                                            <table class="report-day-history-table">
                                                <thead>
                                                    <tr>
                                                        <th>Start</th>
                                                        <th>Koniec</th>
                                                        <th>Czas</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($dailyRow['sessions'] as $session): ?>
                                                        <?php
                                                        $baseSeconds = (int)$session['DURATION_SECONDS'];
                                                        $isActive = empty($session['END_TIME']);
                                                        ?>
                                                        <tr>
                                                            <td><?php echo htmlspecialchars((string)$session['START_TIME']); ?></td>
                                                            <td><?php echo htmlspecialchars((string)($session['END_TIME'] ?? '-')); ?></td>
                                                            <td class="duration-cell" data-duration-cell="1" data-base-seconds="<?php echo (int)$baseSeconds; ?>" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>" data-active="<?php echo $isActive ? '1' : '0'; ?>">
                                                                <?php echo htmlspecialchars(formatWorkDuration($baseSeconds)); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>

                                        <button type="button" class="load-all-logs-btn" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>">Zaladuj cala historie tego pracownika</button>
                                        <div class="all-logs-container"></div>
                                    </details>
                                </td>
                                <td class="day-total-cell" data-login="<?php echo htmlspecialchars((string)$dailyRow['login']); ?>" data-base-seconds="<?php echo (int)$dailyRow['total_seconds']; ?>">
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
<script src="../js/content/report.js"></script>


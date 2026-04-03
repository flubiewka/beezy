<?php

function getCurrentMonthRange() {
    $start = new DateTime('first day of this month 00:00:00');
    $end = new DateTime('first day of next month 00:00:00');
    return [$start->format('Y-m-d H:i:s'), $end->format('Y-m-d H:i:s')];
}

function canViewAllSessions($roleId) {
    return in_array((int)$roleId, [1, 3], true);
}

function startWorkSession($pdo, $login) {
    $stmt = $pdo->prepare(
        'INSERT INTO WORK_SESSIONS (LOGIN, START_TIME, END_TIME)
         SELECT ?, NOW(), NULL
         WHERE NOT EXISTS (
             SELECT 1 FROM WORK_SESSIONS
             WHERE LOGIN = ? AND END_TIME IS NULL
         )'
    );
    return $stmt->execute([$login, $login]);
}

function endWorkSession($pdo, $login) {
    $stmt = $pdo->prepare(
        'UPDATE WORK_SESSIONS
         SET END_TIME = NOW()
         WHERE LOGIN = ? AND END_TIME IS NULL'
    );
    return $stmt->execute([$login]);
}

function getMonthlyWorkSessions($pdo, $viewerLogin, $viewAll = false) {
    [$monthStart, $monthEnd] = getCurrentMonthRange();

    $sql = 'SELECT
                ws.ID_SESSION,
                ws.LOGIN,
                u.IMIE,
                u.NAZWISKO,
                ws.START_TIME,
                ws.END_TIME,
                GREATEST(TIMESTAMPDIFF(SECOND, ws.START_TIME, COALESCE(ws.END_TIME, NOW())), 0) AS DURATION_SECONDS
            FROM WORK_SESSIONS ws
            JOIN USERS u ON u.LOGIN = ws.LOGIN
            WHERE ws.START_TIME >= ?
              AND ws.START_TIME < ?';

    $params = [$monthStart, $monthEnd];

    if (!$viewAll) {
        $sql .= ' AND ws.LOGIN = ?';
        $params[] = $viewerLogin;
    }

    $sql .= ' ORDER BY ws.START_TIME DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

function getMonthlyWorkSummary($sessions, $viewerLogin) {
    $summary = ['my_seconds' => 0, 'all_seconds' => 0];

    foreach ($sessions as $session) {
        $duration = (int)$session['DURATION_SECONDS'];
        $summary['all_seconds'] += $duration;
        if (isset($session['LOGIN']) && $session['LOGIN'] === $viewerLogin) {
            $summary['my_seconds'] += $duration;
        }
    }

    return $summary;
}

function getWorkLogsByLogin($pdo, $login) {
    $sql = 'SELECT
                ws.ID_SESSION,
                ws.LOGIN,
                u.IMIE,
                u.NAZWISKO,
                ws.START_TIME,
                ws.END_TIME,
                GREATEST(TIMESTAMPDIFF(SECOND, ws.START_TIME, COALESCE(ws.END_TIME, NOW())), 0) AS DURATION_SECONDS
            FROM WORK_SESSIONS ws
            JOIN USERS u ON u.LOGIN = ws.LOGIN
            WHERE ws.LOGIN = ?
            ORDER BY ws.START_TIME DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$login]);
    return $stmt->fetchAll();
}

function formatWorkDuration($seconds) {
    $seconds = max(0, (int)$seconds);
    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $secs = $seconds % 60;

    return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
}

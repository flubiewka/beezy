<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('m');

if ($month < 1)  { $month = 12; $year--; }
if ($month > 12) { $month = 1;  $year++; }

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1)  { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1;  $nextYear++; }

$firstDay     = mktime(0, 0, 0, $month, 1, $year);
$daysInMonth  = (int)date('t', $firstDay);
$startWeekday = (int)date('N', $firstDay);

$monthNames = [
    1=>'Styczeń', 2=>'Luty', 3=>'Marzec', 4=>'Kwiecień',
    5=>'Maj', 6=>'Czerwiec', 7=>'Lipiec', 8=>'Sierpień',
    9=>'Wrzesień', 10=>'Październik', 11=>'Listopad', 12=>'Grudzień'
];

// Pobieramy tylko informację o dniach, w których były jakiekolwiek sesje
$stmtSessions = $pdo->prepare("
    SELECT DISTINCT DAY(START_TIME) as day
    FROM WORK_SESSIONS 
    WHERE YEAR(START_TIME) = :y AND MONTH(START_TIME) = :m
");
$stmtSessions->execute([':y' => $year, ':m' => $month]);
$sessionDays = $stmtSessions->fetchAll(PDO::FETCH_COLUMN);
?>

<div id="content-calendar" class="content-section">
    <div class="calendar-shell-centered">
        
        <div class="cal-card box-TEMPLATE color_2">
            <!-- Nawigacja -->
            <div class="cal-nav">
                <a class="cal-nav-btn color_3" 
                   href="?page=calendar&month=<?= $prevMonth ?>&year=<?= $prevYear ?>">&#8592;</a>
                <span class="cal-nav-title"><?= $monthNames[$month] ?> <?= $year ?></span>
                <a class="cal-nav-btn color_3" 
                   href="?page=calendar&month=<?= $nextMonth ?>&year=<?= $nextYear ?>">&#8594;</a>
            </div>

            <!-- Siatka kalendarza -->
            <div class="cal-grid">
                <?php foreach (['Pon','Wt','Śr','Czw','Pt','Sob','Nie'] as $d): ?>
                    <div class="cal-header-cell"><?= $d ?></div>
                <?php endforeach; ?>

                <?php for ($i = 1; $i < $startWeekday; $i++): ?>
                    <div class="cal-cell cal-cell--empty"></div>
                <?php endfor; ?>

                <?php for ($day = 1; $day <= $daysInMonth; $day++):
                    $isToday = ($day == (int)date('j') && $month == (int)date('n') && $year == (int)date('Y'));
                    $hasSessions = in_array($day, $sessionDays);
                    $cls = 'cal-cell color_3';
                    if ($isToday) $cls .= ' cal-cell--today';
                ?>
                    <div class="<?= $cls ?>">
                        <span class="cal-day-num"><?= $day ?></span>
                        <?php if ($hasSessions): ?>
                            <span class="cal-dot"></span>
                        <?php endif; ?>
                    </div>
                <?php endfor; ?>
            </div>
        </div>

    </div>
</div>
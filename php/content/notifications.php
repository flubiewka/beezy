<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}

$currentLogin = $_SESSION['user_id'];

// Pobieramy tylko pilne wiadomości z chatów użytkownika
$stmtUrgent = $pdo->prepare("
    SELECT m.ID_MESSAGE, m.CONTENT, m.SENT_AT, m.SENDER_LOGIN,
           u.IMIE, u.NAZWISKO,
           c.CHAT_NAME, c.IS_GROUP
    FROM MESSAGES m
    JOIN CHAT c          ON m.ID_CHAT = c.ID_CHAT
    JOIN CHAT_MEMBERS cm ON cm.ID_CHAT = c.ID_CHAT AND cm.LOGIN = :login
    JOIN USERS u         ON m.SENDER_LOGIN = u.LOGIN
    WHERE m.IS_URGENT = 1
    ORDER BY m.SENT_AT DESC
    LIMIT 50
");
$stmtUrgent->execute([':login' => $currentLogin]);
$urgentMessages = $stmtUrgent->fetchAll(PDO::FETCH_ASSOC);

function timeAgo(string $datetime): string {
    $diff = time() - strtotime($datetime);
    if ($diff < 60)    return 'przed chwilą';
    if ($diff < 3600)  return floor($diff/60).' min temu';
    if ($diff < 86400) return floor($diff/3600).' godz. temu';
    return date('d.m.Y H:i', strtotime($datetime));
}
?>

<div id="content-notifications" class="content-section">
    <div class="notifications-shell">

        <!-- ====== SEKCOJA PILNE ====== -->
        <div class="notif-card box-TEMPLATE color_2">
            <div class="notif-card-header color_3">
                <span class="notif-icon-urgent">&#9888;</span>
                <h3 class="notif-card-title">Pilne wiadomości</h3>
                <?php if (!empty($urgentMessages)): ?>
                    <span class="notif-badge notif-badge--urgent"><?= count($urgentMessages) ?></span>
                <?php endif; ?>
            </div>

            <?php if (empty($urgentMessages)): ?>
                <p class="notif-empty">Brak pilnych wiadomości.</p>
            <?php else: ?>
                <ul class="notif-list">
                    <?php foreach ($urgentMessages as $msg): ?>
                        <li class="notif-item notif-item--urgent color_3">
                            <div class="notif-avatar notif-avatar--urgent">
                                <?= strtoupper(mb_substr($msg['IMIE'], 0, 1)) . strtoupper(mb_substr($msg['NAZWISKO'], 0, 1)) ?>
                            </div>
                            <div class="notif-body">
                                <div class="notif-meta">
                                    <span class="notif-sender">
                                        <?= htmlspecialchars($msg['IMIE'] . ' ' . $msg['NAZWISKO']) ?>
                                    </span>
                                    <?php if ($msg['IS_GROUP'] && $msg['CHAT_NAME']): ?>
                                        <span class="notif-chat-name">
                                            · <?= htmlspecialchars($msg['CHAT_NAME']) ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="notif-time"><?= timeAgo($msg['SENT_AT']) ?></span>
                                </div>
                                <p class="notif-content">
                                    <?= nl2br(htmlspecialchars($msg['CONTENT'])) ?>
                                </p>
                            </div>
                            <span class="notif-urgent-pill">PILNE</span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

    </div>
</div>
<?php
require_once 'admin_auth.php';
require_once 'functions.php';
require_once 'db_connect.php';

$stmt = $pdo->prepare('
    SELECT tl.id, tl.completed_date,
           t.title, t.points,
           c.name AS child_name
    FROM task_logs tl
    JOIN tasks t ON t.id = tl.task_id AND t.deleted_at IS NULL
    JOIN children c ON c.id = t.child_id AND c.deleted_at IS NULL
    WHERE tl.deleted_at IS NULL
    ORDER BY tl.completed_date DESC
');
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once 'header.php'; ?>
    <h1>達成ログ</h1>
    <?php require_once 'admin_nav.php'; ?>
    <?php if (empty($logs)): ?>
        <p>達成ログがありません</p>
    <?php else: ?>
        <?php foreach ($logs as $log): ?>
            <div>
                <p><?= h($log['child_name']) ?> / <?= h($log['completed_date']) ?></p>
                <p><?= h($log['title']) ?> / <?= h($log['points']) ?>ポイント</p>
            </div>
            <hr>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
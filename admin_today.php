<?php
require_once 'admin_auth.php';
require_once 'functions.php';
require_once 'db_connect.php';

$face_icons = [1 => '😢', 2 => '😕', 3 => '😊', 4 => '😄', 5 => '🤩'];

$children = $pdo->query('SELECT id, name, total_points FROM children WHERE deleted_at IS NULL')->fetchAll(PDO::FETCH_ASSOC);

$today_data = [];
foreach ($children as $child) {
    $stmt = $pdo->prepare('SELECT task_id FROM task_logs WHERE task_id IN (SELECT id FROM tasks WHERE child_id = ?) AND completed_date = CURDATE() AND deleted_at IS NULL');
    $stmt->execute([$child['id']]);
    $completed_ids = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'task_id');

    $stmt = $pdo->prepare('SELECT id, title, points FROM tasks WHERE child_id = ? AND deleted_at IS NULL');
    $stmt->execute([$child['id']]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare('SELECT id, content, body_score, mind_score FROM diaries WHERE child_id = ? AND diary_date = CURDATE() AND deleted_at IS NULL');
    $stmt->execute([$child['id']]);
    $diary = $stmt->fetch(PDO::FETCH_ASSOC);

    $today_data[] = [
        'child' => $child,
        'tasks' => $tasks,
        'completed_ids' => $completed_ids,
        'diary' => $diary,
    ];
}
?>
<?php require_once 'header.php'; ?>
    <h1>きょうのようす</h1>
    <?php require_once 'admin_nav.php'; ?>

    <?php foreach ($today_data as $data): ?>
        <h2><?= h($data['child']['name']) ?> / <?= h($data['child']['total_points']) ?>ポイント</h2>

        <h3>タスク</h3>
        <?php if (empty($data['tasks'])): ?>
            <p>タスクがありません</p>
        <?php else: ?>
            <?php foreach ($data['tasks'] as $task): ?>
                <?php $is_done = in_array($task['id'], $data['completed_ids']); ?>
                <div class="admin-card">
                    <div>
                        <span><?= h($task['title']) ?></span>
                        <span class="task-point-admin"><?= h($task['points']) ?>ポイント</span>
                    </div>
                    <?php if ($is_done): ?>
                        <span class="task-badge">できた！</span>
                    <?php else: ?>
                        <span class="task-not-done">まだ</span>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <h3>きょうのにっき</h3>
        <?php if ($data['diary']): ?>
            <div class="admin-card admin-diary-card">
                <p class="diary-score-admin">からだ：<?= $face_icons[$data['diary']['body_score']] ?> こころ：<?= $face_icons[$data['diary']['mind_score']] ?></p>
                <?php if ($data['diary']['content']): ?>
                    <p><?= h($data['diary']['content']) ?></p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="diary-not-written">まだかいていません</p>
        <?php endif; ?>
        <hr>
    <?php endforeach; ?>
<?php require_once 'footer.php'; ?>
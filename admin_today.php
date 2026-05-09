<?php
session_start();
require_once 'admin_auth.php';
require_once 'functions.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    if ($action === 'reply') {
        $diary_id = isset($_POST['diary_id']) ? (int)$_POST['diary_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';
        if ($diary_id !== 0 && $content !== '') {
            $stmt = $pdo->prepare('INSERT INTO diary_replies (diary_id, content) VALUES (?, ?)');
            $stmt->execute([$diary_id, $content]);
        }
    }
    header('Location: admin_today.php');
    exit;
}

$face_icons = [1 => '😢', 2 => '😕', 3 => '😊', 4 => '😄', 5 => '🤩'];


$stmt = $pdo->prepare('SELECT id, name, total_points FROM children WHERE deleted_at IS NULL AND user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                <?php
                $stmt = $pdo->prepare('SELECT content FROM diary_replies WHERE diary_id = ? AND deleted_at IS NULL');
                $stmt->execute([$data['diary']['id']]);
                $reply = $stmt->fetch(PDO::FETCH_ASSOC);
                ?>
                <?php if ($reply): ?>
                    <p class="reply-done">返信済み：<?= h($reply['content']) ?></p>
                <?php else: ?>
                    <form method="post">
                        <input type="hidden" name="action" value="reply">
                        <input type="hidden" name="diary_id" value="<?= h($data['diary']['id']) ?>">
                        <textarea name="content" rows="2" placeholder="返信を入力" class="admin-input reply-textarea"></textarea><br>
                        <button type="submit" class="btn-admin">返信する</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <p class="diary-not-written">まだかいていません</p>
        <?php endif; ?>
        <hr>
    <?php endforeach; ?>
<?php require_once 'footer.php'; ?>
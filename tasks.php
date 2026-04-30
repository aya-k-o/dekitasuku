<?php
require_once 'db_connect.php';

$child_id = isset($_GET['child_id']) ? (int)$_GET['child_id'] : 0;

if ($child_id === 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, name, total_points FROM children WHERE id = ? AND deleted_at IS NULL');
$stmt->execute([$child_id]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, title, points FROM tasks WHERE child_id = ? AND deleted_at IS NULL');
$stmt->execute([$child_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php require_once 'header.php'; ?>
    <h1><?= h($child['name']) ?>のタスク</h1>
    <p><?= h($child['total_points']) ?>ポイント</p>
    <?php if (empty($tasks)): ?>
        <p>タスクがまだないよ！</p>
    <?php else: ?>
        <?php foreach ($tasks as $task): ?>
            <div>
                <span><?= h($task['title']) ?></span>
                <span><?= h($task['points']) ?>ポイント</span>
                <form method="post" action="complete.php">
                    <input type="hidden" name="task_id" value="<?= h($task['id']) ?>">
                    <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
                    <button type="submit">できた！</button>
                </form>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
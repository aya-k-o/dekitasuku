<?php
session_start();

if (!isset($_SESSION['admin']) || $_SESSION['admin'] !== true) {
    header('Location: admin.php');
    exit;
}

require_once 'functions.php';
require_once 'db_connect.php';

$children = $pdo->query('SELECT id, name FROM children WHERE deleted_at IS NULL')->fetchAll(PDO::FETCH_ASSOC);

$child_id = isset($_GET['child_id']) ? (int)$_GET['child_id'] : 0;

if ($child_id === 0 && !empty($children)) {
    $child_id = $children[0]['id'];
}

$tasks = [];
if ($child_id !== 0) {
    $stmt = $pdo->prepare('SELECT id, title, points FROM tasks WHERE child_id = ? AND deleted_at IS NULL');
    $stmt->execute([$child_id]);
    $tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $points = isset($_POST['points']) ? (int)$_POST['points'] : 10;
        $post_child_id = isset($_POST['child_id']) ? (int)$_POST['child_id'] : 0;

        if ($title !== '' && $post_child_id !== 0) {
            $stmt = $pdo->prepare('INSERT INTO tasks (child_id, title, points) VALUES (?, ?, ?)');
            $stmt->execute([$post_child_id, $title, $points]);
        }
    }

    if ($action === 'delete') {
        $task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
        if ($task_id !== 0) {
            $stmt = $pdo->prepare('UPDATE tasks SET deleted_at = NOW() WHERE id = ?');
            $stmt->execute([$task_id]);
        }
    }

    header('Location: admin_tasks.php?child_id=' . (int)$_POST['child_id']);
    exit;
}
?>
<?php require_once 'header.php'; ?>
    <h1>タスク管理</h1>
    <a href="admin_tasks.php">タスク</a>
    <a href="admin_children.php">子ども</a>
    <a href="admin_rewards.php">報酬</a>
    <a href="admin_diaries.php">日記</a>
    <a href="admin_logs.php">達成ログ</a>

    <h2>子どもを選ぶ</h2>
    <?php foreach ($children as $child): ?>
        <a href="admin_tasks.php?child_id=<?= h($child['id']) ?>">
            <?= h($child['name']) ?>
        </a>
    <?php endforeach; ?>

    <?php if ($child_id !== 0): ?>
        <h2>タスク追加</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
            <input type="text" name="title" placeholder="タスク名">
            <input type="number" name="points" value="10" min="1">
            <button type="submit">追加</button>
        </form>

        <h2>タスク一覧</h2>
        <?php if (empty($tasks)): ?>
            <p>タスクがありません</p>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div>
                    <span><?= h($task['title']) ?></span>
                    <span><?= h($task['points']) ?>ポイント</span>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="task_id" value="<?= h($task['id']) ?>">
                        <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
                        <button type="submit">削除</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
<?php
session_start();
require_once 'admin_auth.php';
require_once 'functions.php';
require_once 'db_connect.php';


$stmt = $pdo->prepare('SELECT id, name FROM children WHERE deleted_at IS NULL AND user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            $stmt = $pdo->prepare('
                UPDATE tasks SET deleted_at = NOW() 
                WHERE id = ? 
                AND child_id IN (SELECT id FROM children WHERE user_id = ? AND deleted_at IS NULL)
            ');
            $stmt->execute([$task_id, $_SESSION['user_id']]);
        }
    }

    header('Location: admin_tasks.php?child_id=' . (int)$_POST['child_id']);
    exit;
}
?>
<?php require_once 'header.php'; ?>
    <h1>タスク管理</h1>
    <?php require_once 'admin_nav.php'; ?>

    <h2>お子さんを選んでください</h2>
    <div class="admin-form">
        <?php foreach ($children as $child): ?>
            <a href="admin_tasks.php?child_id=<?= h($child['id']) ?>" class="btn-admin">
                <?= h($child['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($child_id !== 0): ?>
        <h2>タスク追加</h2>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
            <input type="text" name="title" placeholder="タスク名" class="admin-input">
            <input type="number" name="points" value="10" min="1" class="admin-input" style="width: 80px; flex: none;">
            <button type="submit" class="btn-admin">追加</button>
        </form>

        <h2>タスク一覧</h2>
        <?php if (empty($tasks)): ?>
            <p>タスクがありません</p>
        <?php else: ?>
            <?php foreach ($tasks as $task): ?>
                <div class="admin-card">
                    <div>
                        <span><?= h($task['title']) ?></span>
                        <span style="color: #888; margin-left: 8px;"><?= h($task['points']) ?>ポイント</span>
                    </div>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="task_id" value="<?= h($task['id']) ?>">
                        <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
                        <button type="submit" class="btn-delete">削除</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
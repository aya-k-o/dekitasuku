<?php

require_once 'admin_auth.php';
require_once 'functions.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';

        if ($name !== '') {
            $stmt = $pdo->prepare('INSERT INTO children (name) VALUES (?)');
            $stmt->execute([$name]);
        }
    }

    if ($action === 'delete') {
        $del_id = isset($_POST['del_id']) ? (int)$_POST['del_id'] : 0;
        if ($del_id !== 0) {
            $stmt = $pdo->prepare('UPDATE children SET deleted_at = NOW() WHERE id = ?');
            $stmt->execute([$del_id]);
        }
    }

    header('Location: admin_children.php');
    exit;
}

$children = $pdo->query('SELECT id, name, total_points FROM children WHERE deleted_at IS NULL')->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once 'header.php'; ?>
    <h1>子ども管理</h1>
    <?php require_once 'admin_nav.php'; ?>

    <h2>子ども追加</h2>
    <form method="post" class="admin-form">
        <input type="hidden" name="action" value="add">
        <input type="text" name="name" placeholder="子どもの名前" class="admin-input">
        <button type="submit" class="btn-admin">追加</button>
    </form>

    <h2>子ども一覧</h2>
    <?php if (empty($children)): ?>
        <p>子どもが登録されていません</p>
    <?php else: ?>
        <?php foreach ($children as $child): ?>
            <div class="admin-card">
                <div>
                    <span><?= h($child['name']) ?></span>
                    <span style="color: #888; margin-left: 8px;"><?= h($child['total_points']) ?>ポイント</span>
                </div>
                <div>
                    <a href="tasks.php?child_id=<?= h($child['id']) ?>" class="btn-admin" style="margin-right: 8px; text-decoration: none;">子どもの画面を見る</a>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="del_id" value="<?= h($child['id']) ?>">
                        <button type="submit" class="btn-delete">削除</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
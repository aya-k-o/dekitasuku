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

$rewards = [];
if ($child_id !== 0) {
    $stmt = $pdo->prepare('SELECT id, title, points_required FROM rewards WHERE child_id = ? AND deleted_at IS NULL');
    $stmt->execute([$child_id]);
    $rewards = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'add') {
        $title = isset($_POST['title']) ? trim($_POST['title']) : '';
        $points_required = isset($_POST['points_required']) ? (int)$_POST['points_required'] : 0;
        $post_child_id = isset($_POST['child_id']) ? (int)$_POST['child_id'] : 0;

        if ($title !== '' && $points_required > 0 && $post_child_id !== 0) {
            $stmt = $pdo->prepare('INSERT INTO rewards (child_id, title, points_required) VALUES (?, ?, ?)');
            $stmt->execute([$post_child_id, $title, $points_required]);
        }
    }

    if ($action === 'delete') {
        $reward_id = isset($_POST['reward_id']) ? (int)$_POST['reward_id'] : 0;
        if ($reward_id !== 0) {
            $stmt = $pdo->prepare('
                UPDATE rewards SET deleted_at = NOW() 
                WHERE id = ? 
                AND child_id IN (SELECT id FROM children WHERE user_id = ? AND deleted_at IS NULL)
            ');
            $stmt->execute([$reward_id, $_SESSION['user_id']]);
        }    
    }

    header('Location: admin_rewards.php?child_id=' . (int)$_POST['child_id']);
    exit;
}
?>
<?php require_once 'header.php'; ?>
    <h1>報酬管理</h1>
    <?php require_once 'admin_nav.php'; ?>

    <h2>お子さんを選んでください</h2>
    <div class="admin-form">
        <?php foreach ($children as $child): ?>
            <a href="admin_rewards.php?child_id=<?= h($child['id']) ?>" class="btn-admin" style="text-decoration: none;">
                <?= h($child['name']) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <?php if ($child_id !== 0): ?>
        <h2>報酬追加</h2>
        <form method="post" class="admin-form">
            <input type="hidden" name="action" value="add">
            <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
            <input type="text" name="title" placeholder="報酬名" class="admin-input">
            <input type="number" name="points_required" value="50" min="1" class="admin-input" style="width: 80px; flex: none;">
            <button type="submit" class="btn-admin">追加</button>
        </form>

        <h2>報酬一覧</h2>
        <?php if (empty($rewards)): ?>
            <p>報酬が登録されていません</p>
        <?php else: ?>
            <?php foreach ($rewards as $reward): ?>
                <div class="admin-card">
                    <div>
                        <span><?= h($reward['title']) ?></span>
                        <span style="color: #888; margin-left: 8px;"><?= h($reward['points_required']) ?>ポイント</span>
                    </div>
                    <form method="post" class="form-inline">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="reward_id" value="<?= h($reward['id']) ?>">
                        <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
                        <button type="submit" class="btn-delete">削除</button>
                    </form>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
<?php
session_start();
require_once 'functions.php';
require_once 'db_connect.php';
require_once 'user_auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $child_id = isset($_POST['child_id']) ? (int)$_POST['child_id'] : 0;
    if ($child_id !== 0) {
        $_SESSION['child_id'] = $child_id;
    }
    header('Location: today.php');
    exit;
}

// ログイン中のuser_idの子どもだけ取得
$stmt = $pdo->prepare('SELECT id, name FROM children WHERE deleted_at IS NULL AND user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once 'header.php'; ?>
    <h1>だれがつかうの？</h1>
    <?php foreach ($children as $child): ?>
        <form method="post">
            <input type="hidden" name="child_id" value="<?= h($child['id']) ?>">
            <button type="submit" class="child-btn"><?= h($child['name']) ?></button>
        </form>
    <?php endforeach; ?>
<?php require_once 'footer.php'; ?>
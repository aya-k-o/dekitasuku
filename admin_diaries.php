<?php
session_start();
require_once 'admin_auth.php';
require_once 'functions.php';
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); // CSRFトークン検証
    $action = isset($_POST['action']) ? $_POST['action'] : '';

    if ($action === 'reply') {
        $diary_id = isset($_POST['diary_id']) ? (int)$_POST['diary_id'] : 0;
        $content = isset($_POST['content']) ? trim($_POST['content']) : '';

        if ($diary_id !== 0 && $content !== '') {
            $stmt = $pdo->prepare('INSERT INTO diary_replies (diary_id, content) VALUES (?, ?)');
            $stmt->execute([$diary_id, $content]);
        }
    }

    header('Location: admin_diaries.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT d.id, d.child_id, d.content, d.body_score, d.mind_score, d.diary_date,
           c.name AS child_name,
           r.content AS reply_content
    FROM diaries d
    JOIN children c ON c.id = d.child_id AND c.deleted_at IS NULL AND c.user_id = ?
    LEFT JOIN diary_replies r ON r.diary_id = d.id AND r.deleted_at IS NULL
    WHERE d.deleted_at IS NULL
    ORDER BY d.diary_date DESC
');
$stmt->execute([$_SESSION['user_id']]);

$diaries = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once 'header.php'; ?>
    <h1>日記管理</h1>
    <?php require_once 'admin_nav.php'; ?>

    <?php if (empty($diaries)): ?>
        <p>日記がありません</p>
    <?php else: ?>
        <?php foreach ($diaries as $diary): ?>
           <div class="admin-card admin-diary-card">
    <p><strong><?= h($diary['child_name']) ?></strong> / <?= h($diary['diary_date']) ?></p>
    <p class="diary-score-admin">からだ：<?= h($diary['body_score']) ?> こころ：<?= h($diary['mind_score']) ?></p>
    <?php if ($diary['content']): ?>
        <p><?= h($diary['content']) ?></p>
    <?php endif; ?>
    <?php if ($diary['reply_content']): ?>
        <p class="diary-replied">返信済み：<?= h($diary['reply_content']) ?></p>
    <?php else: ?>
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <input type="hidden" name="action" value="reply">
            <input type="hidden" name="diary_id" value="<?= h($diary['id']) ?>">
            <textarea name="content" rows="3" placeholder="返信を入力" class="admin-input reply-textarea"></textarea><br>
            <button type="submit" class="btn-admin">返信する</button>
        </form>
    <?php endif; ?>
</div>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
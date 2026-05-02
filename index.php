<?php
require_once 'functions.php';
require_once 'db_connect.php';

$stmt = $pdo->prepare('SELECT id, name FROM children WHERE deleted_at IS NULL');
$stmt->execute();
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<?php require_once 'header.php'; ?>
    <h1>だれがつかうの？</h1>
    <?php foreach ($children as $child): ?>
        <a href="today.php?child_id=<?= h($child['id']) ?>" class="child-btn">
    <?= h($child['name']) ?>
</a>
    <?php endforeach; ?>
<?php require_once 'footer.php'; ?>
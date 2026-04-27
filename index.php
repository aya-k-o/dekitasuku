<?php
require_once 'db_connect.php';

$stmt = $pdo->prepare('SELECT id, name FROM children WHERE deleted_at IS NULL');
$stmt->execute();
$children = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>できたすく</title>
</head>
<body>
    <h1>だれがつかうの？</h1>
    <?php foreach ($children as $child): ?>
        <a href="tasks.php?child_id=<?= htmlspecialchars($child['id']) ?>">
            <?= htmlspecialchars($child['name']) ?>
        </a>
    <?php endforeach; ?>
</body>
</html>
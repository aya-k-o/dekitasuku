<?php
require_once 'db_connect.php';

$child_id = isset($_GET['child_id']) ? (int)$_GET['child_id'] : 0;

if ($child_id === 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, name FROM children WHERE id = ? AND deleted_at IS NULL');
$stmt->execute([$child_id]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('
    SELECT d.id, d.content, d.body_score, d.mind_score, d.diary_date,
           r.content AS reply_content
    FROM diaries d
    LEFT JOIN diary_replies r ON r.diary_id = d.id AND r.deleted_at IS NULL
    WHERE d.child_id = ? AND d.deleted_at IS NULL
    ORDER BY d.diary_date DESC
');
$stmt->execute([$child_id]);
$diaries = $stmt->fetchAll(PDO::FETCH_ASSOC);

$grouped = [];
foreach ($diaries as $diary) {
    $month = substr($diary['diary_date'], 0, 7);
    $grouped[$month][] = $diary;
}
?>

<?php require_once 'header.php'; ?>
    <h1><?= htmlspecialchars($child['name']) ?>のにっき</h1>
    <a href="diary.php?child_id=<?= htmlspecialchars($child_id) ?>">にっきをかく</a>

    <?php if (empty($grouped)): ?>
        <p>まだにっきがないよ！</p>
    <?php else: ?>
        <?php foreach ($grouped as $month => $entries): ?>
            <h2><?= htmlspecialchars($month) ?></h2>
            <?php foreach ($entries as $diary): ?>
                <div>
                    <p><?= htmlspecialchars($diary['diary_date']) ?></p>
                    <p>からだ：<?= htmlspecialchars($diary['body_score']) ?> こころ：<?= htmlspecialchars($diary['mind_score']) ?></p>
                    <?php if ($diary['content']): ?>
                        <p><?= htmlspecialchars($diary['content']) ?></p>
                    <?php endif; ?>
                    <?php if ($diary['reply_content']): ?>
                        <p>へんじ：<?= htmlspecialchars($diary['reply_content']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endforeach; ?>
    <?php endif; ?>
<?php require_once 'footer.php'; ?>
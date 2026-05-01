<?php
require_once 'db_connect.php';

$task_id = isset($_POST['task_id']) ? (int)$_POST['task_id'] : 0;
$child_id = isset($_POST['child_id']) ? (int)$_POST['child_id'] : 0;

if ($task_id === 0 || $child_id === 0) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id, points FROM tasks WHERE id = ? AND deleted_at IS NULL');
$stmt->execute([$task_id]);
$task = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$task) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare('SELECT id FROM task_logs WHERE task_id = ? AND completed_date = CURDATE() AND deleted_at IS NULL');
$stmt->execute([$task_id]);
$already_done = $stmt->fetch(PDO::FETCH_ASSOC);

if ($already_done) {
    header('Location: tasks.php?child_id=' . $child_id);
    exit;
}
try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare('INSERT INTO task_logs (task_id, completed_date) VALUES (?, CURDATE())');
    $stmt->execute([$task_id]);

    $stmt = $pdo->prepare('UPDATE children SET total_points = total_points + ? WHERE id = ?');
    $stmt->execute([$task['points'], $child_id]);

    $pdo->commit();
} catch (PDOException $e) {
    $pdo->rollBack();
    header('Location: index.php');
    exit;
}

header('Location: tasks.php?child_id=' . $child_id);
exit;
<?php
session_start();
require_once 'functions.php';
require_once 'db_connect.php';
require_once 'user_auth.php';

if (isset($_GET['child_id'])) {
    $child_id = (int)$_GET['child_id'];
    $_SESSION['child_id'] = $child_id;
} else {
    $child_id = isset($_SESSION['child_id']) ? (int)$_SESSION['child_id'] : 0;
}
if ($child_id === 0) {
  header('Location: index.php');
  exit;
}

$stmt = $pdo->prepare('SELECT id, name, total_points FROM children WHERE id = ? AND user_id = ? AND deleted_at IS NULL');
$stmt->execute([$child_id, $_SESSION['user_id']]);
$child = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$child) {
  header('Location: index.php');
  exit;
}

$stmt = $pdo->prepare('SELECT id, title, points FROM tasks WHERE child_id = ? AND deleted_at IS NULL');
$stmt->execute([$child_id]);
$tasks = $stmt->fetchAll(PDO::FETCH_ASSOC);
$face_icons = [1 => '😢', 2 => '😕', 3 => '😊', 4 => '😄', 5 => '🤩'];

$stmt = $pdo->prepare('SELECT task_id FROM task_logs WHERE task_id IN (SELECT id FROM tasks WHERE child_id = ?) AND completed_date = CURDATE() AND deleted_at IS NULL');
$stmt->execute([$child_id]);
$completed_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
$completed_ids = array_column($completed_rows, 'task_id');

$stmt = $pdo->prepare('
  SELECT d.id, d.content, d.body_score, d.mind_score, dr.content as reply_content
  FROM diaries d
  LEFT JOIN diary_replies dr ON d.id = dr.diary_id AND dr.deleted_at IS NULL
  WHERE d.child_id = ? AND d.diary_date = CURDATE() AND d.deleted_at IS NULL
');
$stmt->execute([$child_id]);
$today_diary = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $content = isset($_POST['content']) ? trim($_POST['content']) : null;
  $body_score = isset($_POST['body_score']) ? (int)$_POST['body_score'] : 0;
  $mind_score = isset($_POST['mind_score']) ? (int)$_POST['mind_score'] : 0;

  if (!$today_diary && $body_score >= 1 && $body_score <= 5 && $mind_score >= 1 && $mind_score <= 5) {
    $content = ($content === '') ? null : $content;

    $stmt = $pdo->prepare('INSERT INTO diaries (child_id, content, body_score, mind_score, diary_date) VALUES (?, ?, ?, ?, CURDATE())');
    $stmt->execute([$child_id, $content, $body_score, $mind_score]);
  }

  header('Location: today.php?child_id=' . $child_id);
  exit;
}
?>

<?php require_once 'header.php'; ?>
<h1><?= h($child['name']) ?>のきょう</h1>
<?php require_once 'child_nav.php'; ?>
<p class="points">⭐ <?= h($child['total_points']) ?>ポイント</p>
<h2 class="m-section">タスク</h2>
<?php if (empty($tasks)): ?>
  <p>タスクがまだないよ！</p>
<?php else: ?>
  <?php foreach ($tasks as $task): ?>
    <?php $is_done = in_array($task['id'], $completed_ids); ?>
    <div class="task-card <?= $is_done ? 'task-done' : '' ?>">
      <div>
        <span class="task-title"><?= h($task['title']) ?></span>
        <?php if ($is_done): ?>
          <span class="task-badge">できた！</span>
        <?php endif; ?>
        <br>
        <span class="task-points"><?= h($task['points']) ?>ポイント</span>
      </div>
      <?php if ($is_done): ?>
        <button class="btn-done btn-done-completed" disabled>やったね！</button>
      <?php else: ?>
        <form method="post" action="complete.php" class="form-inline">
          <input type="hidden" name="task_id" value="<?= h($task['id']) ?>">
          <input type="hidden" name="child_id" value="<?= h($child_id) ?>">
          <input type="hidden" name="redirect" value="today">
          <button type="submit" class="btn-done">できた！</button>
        </form>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
<?php endif; ?>

<h2 class="m-section">きょうのにっき</h2>
<?php if ($today_diary): ?>
  <div class="diary-card">
    <p class="diary-score">からだ：<?= $face_icons[$today_diary['body_score']] ?> こころ：<?= $face_icons[$today_diary['mind_score']] ?></p> 
    <?php if ($today_diary['content']): ?>
      <p class="diary-content"><?= h($today_diary['content']) ?></p>
    <?php endif; ?>
    <?php if ($today_diary['reply_content']): ?>
      <div class="diary-reply">
        <p class="reply-label">💌 おうちのひとから</p>
        <p class="reply-text"><?= h($today_diary['reply_content']) ?></p>
      </div>
    <?php endif; ?>
  </div>
  <p class="diary-already">きょうもかけたね！</p>
  
  <?php else: ?>
   <form method="post">
    <div class="score-group">
      <label class="score-label">からだのちょうしは？</label>
      <div class="face-select" data-name="body_score">
        <span class="face-btn" data-value="1">😢</span>
        <span class="face-btn" data-value="2">😕</span>
        <span class="face-btn" data-value="3">😊</span>
        <span class="face-btn" data-value="4">😄</span>
        <span class="face-btn" data-value="5">🤩</span>
      </div>
      <input type="hidden" name="body_score" id="body_score" value="0">
    </div>
    <div class="score-group">
      <label class="score-label">こころのちょうしは？</label>
      <div class="face-select" data-name="mind_score">
        <span class="face-btn" data-value="1">😢</span>
        <span class="face-btn" data-value="2">😕</span>
        <span class="face-btn" data-value="3">😊</span>
        <span class="face-btn" data-value="4">😄</span>
        <span class="face-btn" data-value="5">🤩</span>
      </div>
      <input type="hidden" name="mind_score" id="mind_score" value="0">
    </div>
    <div class="score-group">
      <label class="score-label">きょうのこと</label>
      <textarea name="content" rows="4" class="diary-textarea"></textarea>
    </div>
    <button type="submit" class="btn-submit">かけた！</button>
  </form>
<?php endif; ?>
<?php require_once 'footer.php'; ?>
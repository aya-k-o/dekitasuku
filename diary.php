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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = isset($_POST['content']) ? trim($_POST['content']) : null;
    $body_score = isset($_POST['body_score']) ? (int)$_POST['body_score'] : 0;
    $mind_score = isset($_POST['mind_score']) ? (int)$_POST['mind_score'] : 0;

    if ($body_score >= 1 && $body_score <= 5 && $mind_score >= 1 && $mind_score <= 5) {
        $content = ($content === '') ? null : $content;

        $stmt = $pdo->prepare('INSERT INTO diaries (child_id, content, body_score, mind_score, diary_date) VALUES (?, ?, ?, ?, CURDATE())');
        $stmt->execute([$child_id, $content, $body_score, $mind_score]);

        header('Location: diary_list.php?child_id=' . $child_id);
        exit;
    }
}
?>
<?php require_once 'header.php'; ?>
    <h1><?= htmlspecialchars($child['name']) ?>のにっき</h1>

    <form method="post">
        <div>
            <label>きょうのからだのちょうし</label>
            <select name="body_score">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
        <div>
            <label>きょうのこころのちょうし</label>
            <select name="mind_score">
                <option value="1">1</option>
                <option value="2">2</option>
                <option value="3">3</option>
                <option value="4">4</option>
                <option value="5">5</option>
            </select>
        </div>
        <div>
            <label>きょうのこと（かかなくてもいいよ）</label><br>
            <textarea name="content" rows="5"></textarea>
        </div>
        <button type="submit">かけた！</button>
    </form>
<?php require_once 'footer.php'; ?>
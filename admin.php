<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($password === $_ENV['ADMIN_PASS']) {
        $_SESSION['admin'] = true;
        header('Location: admin.php');
        exit;
    } else {
        $error = 'パスワードが違います';
    }
}

if (isset($_SESSION['admin']) && $_SESSION['admin'] === true) {
    header('Location: admin_tasks.php');
    exit;
}
?>
<?php require_once 'header.php'; ?>
    <h1>かんりしゃログイン</h1>

    <?php if (isset($error)): ?>
        <p><?= h($error) ?></p>
    <?php endif; ?>

    <form method="post">
        <label>パスワード</label><br>
        <input type="password" name="password">
        <button type="submit">ログイン</button>
    </form>
<?php require_once 'footer.php'; ?>
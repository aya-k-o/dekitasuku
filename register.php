<?php
// register.php

session_start();
require_once 'functions.php';
require_once 'db_connect.php';

// すでにログイン済みならindex.phpへ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($username === '' || $password === '') {
        $error = 'ユーザー名とパスワードを入力してください';
    } elseif (mb_strlen($password) < 8) {
        $error = 'パスワードは8文字以上にしてください';
    } else {
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $pdo->prepare('INSERT INTO users (username, password_hash) VALUES (?, ?)');
            $stmt->execute([$username, $password_hash]);
            $success = '登録完了しました！ログインしてください';
        } catch (PDOException $e) {
            $error = 'このユーザー名はすでに使われています';
        }
    }
}
?>
<?php require_once 'header.php'; ?>
    <div class="login-wrap">
        <h1>🌟 できたすく</h1>
        <h2>新規登録</h2>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= h($error) ?></p>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <p class="success-msg"><?= h($success) ?></p>
        <?php endif; ?>

        <form method="post" action="register.php">
            <label>ユーザー名（ニックネームでOK）</label>
            <input type="text" name="username" required>

            <label>パスワード（8文字以上）</label>
            <input type="password" name="password" required>

            <button type="submit">登録する</button>
        </form>

        <p><a href="login.php">ログインはこちら</a></p>
    </div>
<?php require_once 'footer.php'; ?>
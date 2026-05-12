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
    csrf_check(); // CSRFトークン検証
    // filter_input()で取得＆サニタイズ
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = trim(filter_input(INPUT_POST, 'password', FILTER_DEFAULT) ?? '');

    if ($username === '' || $password === '') {
        $error = 'ユーザー名とパスワードを入力してください';
    // 正規表現：ユーザー名は英数字・アンダースコアのみ、3〜20文字
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $error = 'ユーザー名は3〜20文字の半角英数字・アンダースコアで入力してください';
    // 正規表現：パスワードは英字と数字を両方含む8文字以上
    } elseif (!preg_match('/^(?=.*[a-zA-Z])(?=.*[0-9]).{8,}$/', $password)) {
        $error = 'パスワードは英字と数字を両方含む8文字以上にしてください';
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
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>ユーザー名（3〜20文字の半角英数字・アンダースコア）</label>
            <input type="text" name="username" required>

            <label>パスワード（英字と数字を含む8文字以上）</label>
            <input type="password" name="password" required>

            <button type="submit">登録する</button>
        </form>

        <p><a href="login.php">ログインはこちら</a></p>
    </div>
<?php require_once 'footer.php'; ?>
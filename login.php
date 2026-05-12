<?php
// login.php

session_start();
require_once 'functions.php';
require_once 'db_connect.php';

// すでにログイン済みならindex.phpへ
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check(); // CSRFトークン検証
    // filter_input()で取得＆サニタイズ
    $username = trim(filter_input(INPUT_POST, 'username', FILTER_SANITIZE_SPECIAL_CHARS) ?? '');
    $password = trim(filter_input(INPUT_POST, 'password', FILTER_DEFAULT) ?? '');

    if ($username === '' || $password === '') {
        $error = 'ユーザー名とパスワードを入力してください';
    } else {
        $stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE username = ? AND deleted_at IS NULL');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password_hash'])) {
            // セッション固定化攻撃対策
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            header('Location: index.php');
            exit;
        } else {
            // どちらが間違いか教えない
            $error = 'ユーザー名またはパスワードが間違っています';
        }
    }
}
?>
<?php require_once 'header.php'; ?>
    <div class="login-wrap">
        <h1>🌟 できたすく</h1>
        <h2>ログイン</h2>

        <?php if ($error !== ''): ?>
            <p class="error-msg"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post" action="login.php">
            <input type="hidden" name="csrf_token" value="<?= h(csrf_token()) ?>">
            <label>ユーザー名</label>
            <input type="text" name="username" required>

            <label>パスワード</label>
            <input type="password" name="password" required>

            <button type="submit">ログイン</button>
        </form>

        <p><a href="register.php">新規登録はこちら</a></p>
    </div>
<?php require_once 'footer.php'; ?>
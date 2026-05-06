<?php
session_start();
require_once 'functions.php';

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
    <div class="login-form">
        <h1>管理者ログイン</h1>

        <?php if (isset($error)): ?>
            <p style="color: #E53935;"><?= h($error) ?></p>
        <?php endif; ?>

        <form method="post">
            <label>パスワード</label><br>
            <input type="password" name="password" class="login-input">
            <button type="submit" class="btn-login">ログイン</button>
        </form>
    </div>
<?php require_once 'footer.php'; ?>
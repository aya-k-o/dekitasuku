<?php
// user_auth.php
// ログイン済みかチェックする共通ファイル（子ども画面・親画面共通）

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
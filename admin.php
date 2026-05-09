<?php
// admin.php
// 管理画面入口：ログイン済みならadmin_today.phpへ、未ログインならlogin.phpへ

session_start();
require_once 'functions.php';

if (isset($_SESSION['user_id'])) {
    header('Location: admin_today.php');
    exit;
}

header('Location: login.php');
exit;
<?php
session_start(); // 啟動 Session
if (!isset($_SESSION['帳號'])) {
    echo "<script>alert('請先登入！'); window.location.href = '登入.html';</script>";
    exit();
}
$帳號 = $_SESSION['帳號']; // 獲取登入的帳號
?>
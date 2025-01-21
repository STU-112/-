<?php
session_start();

// 檢查用戶是否登入
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: 登入.html");
    exit();
}

// 如果已登入，顯示受保護的內容
echo "歡迎進入受保護的頁面！";
?>
<a href="登出.php">登出</a>

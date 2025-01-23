


<?php
session_start();

// 清除 Session 和 Token
session_unset();
session_destroy();

// 選擇性清理 Token 資料庫中的紀錄
header("Location: 登入.html");
exit();
?>

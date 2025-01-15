<?php
session_start();
session_unset(); // 清空所有 Session 資料
session_destroy(); // 銷毀 Session
echo "<script>alert('已成功登出'); window.location.href = '登入.html';</script>";
?>

<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}

// 資料庫連線
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = ' '; // 若無密碼可留空字串
$target_db = '部門設定';

$db_link = new mysqli($host, $db_user, $db_pass, $target_db);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// 檢查是否帶「部門代號」參數
if (isset($_GET['部門代號'])) {
    $deptId = $_GET['部門代號'];

    // 刪除資料
    $delete_sql = "DELETE FROM 部門 WHERE 部門代號 = ?";
    $stmt = $db_link->prepare($delete_sql);
    $stmt->bind_param('s', $deptId);
    $stmt->execute();
    $stmt->close();
}

$db_link->close();
header('Location: 新增部門html.php');
exit;
?>

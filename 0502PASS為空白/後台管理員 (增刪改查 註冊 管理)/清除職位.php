<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 清除職位.php");
    exit;
}

// 資料庫連線
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = ' '; // 若無密碼可留空字串
$db_name = '職位';

$db_link = new mysqli($host, $db_user, $db_pass, $db_name);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// 檢查是否帶「編號」參數
if (isset($_GET['編號'])) {
    $deptId = $_GET['編號'];

    // 刪除資料
    $delete_sql = "DELETE FROM 職位 WHERE 編號 = ?";
    $stmt = $db_link->prepare($delete_sql);
    $stmt->bind_param('s', $deptId);
    $stmt->execute();
    $stmt->close();
}

$db_link->close();
header('Location: 新增職位html.php');
exit;
?>

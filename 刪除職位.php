<?php
// 開啟 Session
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}

// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";
$dbname = "職位設定";

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 獲取刪除的職位編號
if (isset($_GET['編號'])) {
    $編號 = $conn->real_escape_string($_GET['編號']);

    // 執行刪除操作
    $sql = "DELETE FROM 職位設定表 WHERE 編號 = '$編號'";
    if ($conn->query($sql) === TRUE) {
        echo "職位刪除成功！";
    } else {
        echo "刪除失敗：" . $conn->error;
    }
} else {
    echo "無效的請求。";
}

// 關閉資料庫連線
$conn->close();

// 返回上一頁
header("Location: 新增職位設定.php");
exit;
?>

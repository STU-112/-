<?php
// (1) 啟用 Session（如有需要）
session_start();

// (2) 資料庫連線參數
$host      = 'localhost:3307'; // 如 XAMPP MySQL 埠號是 3307
$db_user   = 'root';           // 預設 root
$db_pass   = '3307';               // 若無密碼多為空字串，切勿保留空白
$target_db = '部門設定';        // 資料庫名稱

// (3) 建立連線
$db_link = new mysqli($host, $db_user, $db_pass, $target_db);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// (4) 檢查是否有 GET 參數「部門代號」
if (isset($_GET['部門代號'])) {
    $部門代號 = $_GET['部門代號'];

    // 使用 Prepared Statement 以防 SQL Injection
    $sql = "DELETE FROM `部門` WHERE `部門代號` = ?";
    $stmt = $db_link->prepare($sql);
    $stmt->bind_param('s', $部門代號);
    $stmt->execute();
    $stmt->close();
}

// (5) 關閉連線並導回主程式
$db_link->close();
header('Location: 新增部門html.php'); 
exit;
?>

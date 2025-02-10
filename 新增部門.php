<?php
// 資料庫連線參數
$db_host = "localhost:3307"; // 主機和埠號，請確認 MySQL 埠號是否真的是 3307
$db_user = "root";           // 資料庫用戶名
$db_pw   = "3307";              // 資料庫密碼（若無密碼多為空字串 ""，而非 " "）
$db_name = "部門設定";        // 資料庫名稱（請確保不會有編碼問題，或可改用英文名稱）

// 連接資料庫（不含指定資料庫）
$db_link = mysqli_connect($db_host, $db_user, $db_pw);
if (!$db_link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 創建資料庫（如果不存在）
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name` 
        CHARACTER SET utf8mb4 
        COLLATE utf8mb4_general_ci";
if (!mysqli_query($db_link, $sql)) {
    die("創建資料庫失敗: " . mysqli_error($db_link));
}

// 選擇資料庫
mysqli_select_db($db_link, $db_name);

// 創建「部門設定表」（如果不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS `部門設定表` (
    `編號` CHAR(5) PRIMARY KEY,
    `部門名稱` VARCHAR(50) NOT NULL,
    `建立時間` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link));
}

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 獲取表單資料並防範 SQL 注入
    // 假設表單中 <input name="編號"> 和 <input name="部門名稱"> 對應
    $編號      = mysqli_real_escape_string($db_link, $_POST['編號']);
    $部門名稱  = mysqli_real_escape_string($db_link, $_POST['部門名稱']);

    // 插入記錄
    $insert_record_sql = "INSERT INTO `部門設定表` (編號, `部門名稱`) 
                          VALUES ('$編號', '$部門名稱')";

    if (mysqli_query($db_link, $insert_record_sql)) {
        echo "資料新增成功！";
    } else {
        echo "資料新增失敗: " . mysqli_error($db_link);
    }
}

// 關閉連接
mysqli_close($db_link);
?>

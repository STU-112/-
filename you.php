<?php
// 設定數據庫連接資料
$db_host = "localhost:3307"; // 指定主機和端口
$db_id = "root";              // 資料庫用戶名
$db_pw = " ";                 // 資料庫密碼（空白鍵）
$db_name = "you";             // 資料庫名稱

// 連接到 MySQL
$db_link = mysqli_connect($db_host, $db_id, $db_pw);

// 檢查連接是否成功
if (!$db_link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查資料庫是否存在，若不存在則創建
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (mysqli_query($db_link, $sql)) {
    // 選擇資料庫
    mysqli_select_db($db_link, $db_name);
} else {
    die("創建資料庫失敗: " . mysqli_error($db_link));
}

// 創建資料表（如果表不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS 請款人明細表 (
    單號 INT AUTO_INCREMENT PRIMARY KEY,
    填表日期 DATE NOT NULL,
    活動項目 CHAR(20) NOT NULL,
    請款金額 DECIMAL(10,2) NOT NULL,
    請款內容說明 VARCHAR(50) NOT NULL,
    支出明細 VARCHAR(100) NOT NULL
)";

if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link));
}

// 插入記錄
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $填表日期 = mysqli_real_escape_string($db_link, $_POST['填表日期']);
    $活動項目 = mysqli_real_escape_string($db_link, $_POST['活動項目']);
    $請款金額 = mysqli_real_escape_string($db_link, $_POST['請款金額']);
    $請款內容說明 = mysqli_real_escape_string($db_link, $_POST['請款內容說明']);
    $支出明細 = mysqli_real_escape_string($db_link, $_POST['支出明細']);

    // 使用 INSERT INTO 插入記錄
    $insert_record_sql = "INSERT INTO 請款人明細表 (填表日期, 活動項目, 請款金額, 請款內容說明, 支出明細) 
    VALUES ('$填表日期', '$活動項目', $請款金額, '$請款內容說明', '$支出明細')";

    if (mysqli_query($db_link, $insert_record_sql)) {
        // 插入成功，重定向到成功頁面
        header("Location: success.php");
        exit();
    } else {
        die("插入記錄失敗: " . mysqli_error($db_link));
    }
}

// 關閉資料庫連接
mysqli_close($db_link);
?>

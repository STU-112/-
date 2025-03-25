<?php
// 連接資料庫
$server = 'localhost:3307';
$用戶名 = 'root';
$密碼 = '3307';
$資料庫 = '我要哭';

// 連接到 MySQL
$連接 = mysqli_connect($server, $用戶名, $密碼);

// 檢查連接
if (!$連接) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查資料庫是否已存在，若不存在則創建
$sql = "CREATE DATABASE IF NOT EXISTS $資料庫";
if (mysqli_query($連接, $sql)) {
    echo "資料庫已存在或創建成功!!<br>";
} else {
    die("創建資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 選擇資料庫
if (!mysqli_select_db($連接, $資料庫)) {
    die("選擇資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 創建資料表
$create_table_sql = "CREATE TABLE IF NOT EXISTS 核銷表 (
    單號 VARCHAR(50) PRIMARY KEY,
    實支金額 DECIMAL(10,2),
    結餘 DECIMAL(10,2),
    金額 DECIMAL(10,2) -- 新增金額欄位
)";
if (mysqli_query($連接, $create_table_sql)) {
    echo "支用資料表創建成功或已存在!!<br>";
} else {
    die("創建支用資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 取得表單資料
$單號 = mysqli_real_escape_string($連接, $_POST['單號查詢']);
$實支金額 = isset($_POST['實支金額']) && $_POST['實支金額'] !== '' ? floatval($_POST['實支金額']) : null;
$結餘 = isset($_POST['結餘']) && $_POST['結餘'] !== '' ? floatval($_POST['結餘']) : null;
$金額 = mysqli_real_escape_string($連接, $_POST['金額']);
var_dump($_POST);

// 確保金額和結餘不是 NULL
if ($實支金額 === null) {
    die("[錯誤] 實支金額 不能為空");
}
if ($結餘 === null) {
    die("[錯誤] 結餘 不能為空");
}

// 插入資料
$insert_record_sql = "INSERT INTO 核銷表 (單號, 實支金額, 結餘, 金額) 
VALUES ('$單號', " . ($實支金額 !== null ? $實支金額 : 'NULL') . ", " . ($結餘 !== null ? $結餘 : 'NULL') . ", '$金額')";

if (mysqli_query($連接, $insert_record_sql)) {
    echo "表單已成功提交!!<br>";
} else {
    die("插入資料失敗: " . mysqli_error($連接) . "<br>SQL: " . $insert_record_sql);
}

// 關閉資料庫連接
mysqli_close($連接);
?>

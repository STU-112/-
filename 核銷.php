<?php
// 連接資料庫
$server = 'localhost:3307'; // 伺服器名稱
$用戶名 = 'root'; // 用戶名
$密碼 = '3307'; // 密碼 (設為空字串而不是空格)
$資料庫 = '核銷'; // 資料庫名稱

// 連接到 MySQL
$連接 = mysqli_connect($server, $用戶名, $密碼);

// 檢查連接
if (!$連接) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查資料庫是否已存在，若不存在則創建
$sql = "CREATE DATABASE IF NOT EXISTS $資料庫"; // 資料庫名稱用反引號包裹
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
	結餘繳回 DECIMAL(10,2) 
)";
if (mysqli_query($連接, $create_table_sql)) {
    echo "支用資料表創建成功或已存在!!<br>";
} else {
    die("創建支用資料表失敗: " . mysqli_error($連接) . "<br>");
}




	$單號 = mysqli_real_escape_string($連接, $_POST['單號']);
	$實支金額 = !empty($_POST['實支金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['實支金額']) . "'" : "NULL";
	$結餘繳回 = !empty($_POST['結餘繳回']) ? "'" . mysqli_real_escape_string($連接, $_POST['結餘繳回']) . "'" : "NULL";
	

    

    // 插入資料
    $insert_record_sql = "INSERT INTO 核銷表 (單號, 實支金額,結餘繳回)
	VALUES ('$單號', $實支金額, $結餘繳回)";

    if (mysqli_query($連接, $insert_record_sql)) {
        echo "表單已成功提交!!<br>";
    } else {
        die("插入資料失敗: " . mysqli_error($連接) . "<br>SQL: " . $insert_record_sql);
    }


// 關閉資料庫連接
mysqli_close($連接);
?>

<?php
$db_host = "localhost:3307"; // 指定主機和端口
$db_id = "root";              // 資料庫用戶名
$db_pw = " ";                  // 資料庫密碼（請根據需要更新此項）
$db_name = "new1_1";          // 資料庫名稱

// 連接資料庫
$db_link = mysqli_connect($db_host, $db_id, $db_pw);

if (!$db_link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 創建資料庫（如果資料庫不存在）
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (!mysqli_query($db_link, $sql)) {
    die("創建資料庫失敗: " . mysqli_error($db_link));
}

// 選擇資料庫
mysqli_select_db($db_link, $db_name);

// 創建資料表（如果表不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS 註冊資料表 (
    使用者id CHAR(8) NOT NULL,
    姓名 CHAR(30) NOT NULL,
    電話 CHAR(20),
    地址 CHAR(30),
    帳號 CHAR(20) NOT NULL,
    密碼 CHAR(20) NOT NULL,
    PRIMARY KEY (使用者id)
)";

if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建「資料表」失敗: " . mysqli_error($db_link));
}

// 插入記錄
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $userId = mysqli_real_escape_string($db_link, $_POST['使用者id']);
    $name = mysqli_real_escape_string($db_link, $_POST['姓名']);
    $phone = mysqli_real_escape_string($db_link, $_POST['電話']);
    $address = mysqli_real_escape_string($db_link, $_POST['地址']);
    $username = mysqli_real_escape_string($db_link, $_POST['帳號']);
    $password = mysqli_real_escape_string($db_link, $_POST['密碼']);

    // 使用 INSERT IGNORE 防止重複插入
    $insert_record_sql = "INSERT IGNORE INTO 註冊資料表 (使用者id, 姓名, 電話, 地址, 帳號, 密碼) VALUES ('$userId', '$name', '$phone', '$address', '$username', '$password')";

    if (mysqli_query($db_link, $insert_record_sql)) {
        if (mysqli_affected_rows($db_link) > 0) {
            echo "插入記錄成功!!<br>";
        } else {
            echo "插入記錄失敗: 記錄已存在。<br>";
        }
    } else {
        echo "插入記錄失敗: " . mysqli_error($db_link) . "<br>";
    }
}

// 關閉連接
mysqli_close($db_link);
?>

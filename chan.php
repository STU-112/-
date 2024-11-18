<?php
// 資料庫連接參數
$db_host = "localhost:3307";
$db_id = "root";
$db_pw = " ";
$db_name = "chan";

// 連接到 MySQL 伺服器
$db_link = mysqli_connect($db_host, $db_id, $db_pw);

if (!$db_link) {
    die("連線失敗: " . mysqli_connect_error());
}

// 創建資料庫 `chan`
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (mysqli_query($db_link, $sql)) {
    echo "資料庫創建成功!!<br>";
} else {
    echo "資料庫創建失敗: " . mysqli_error($db_link) . "<br>";
}

// 使用資料庫 `chan`
mysqli_select_db($db_link, $db_name);

// 創建資料表 `註冊資料表`
$create_table_sql = "CREATE TABLE IF NOT EXISTS 註冊資料表 (
    使用者id VARCHAR(8) NOT NULL,
    姓名 VARCHAR(30) NOT NULL,
    電話 VARCHAR(20),
    地址 VARCHAR(30),
    帳號 VARCHAR(20) NOT NULL,
    密碼 VARCHAR(255) NOT NULL,
    PRIMARY KEY (使用者id)
)";

if (mysqli_query($db_link, $create_table_sql)) {
    echo "資料表創建成功!!<br>";
} else {
    echo "資料表創建失敗: " . mysqli_error($db_link) . "<br>";
}

// 處理提交的資料
if (isset($_POST['提交資料'])) {
    $提交資料 = json_decode($_POST['提交資料'], true);
    
    foreach ($提交資料 as $資料) {
        $userid = mysqli_real_escape_string($db_link, $資料['使用者ID']);
        $name = mysqli_real_escape_string($db_link, $資料['姓名']);
        $phone = mysqli_real_escape_string($db_link, $資料['電話']);
        $address = mysqli_real_escape_string($db_link, $資料['地址']);
        $username = mysqli_real_escape_string($db_link, $資料['帳號']);
        $password = mysqli_real_escape_string($db_link, $資料['密碼']);
        
        // 將密碼進行哈希處理以提高安全性
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_sql = "INSERT INTO 註冊資料表 (使用者id, 姓名, 電話, 地址, 帳號, 密碼)
                        VALUES ('$userid', '$name', '$phone', '$address', '$username', '$hashed_password')";
        
        if (mysqli_query($db_link, $insert_sql)) {
            echo "資料插入成功!!<br>";
        } else {
            echo "資料插入失敗: " . mysqli_error($db_link) . "<br>";
        }
    }
} else {
    echo "未提交任何資料。<br>";
}

mysqli_close($db_link);
?>

<?php
// 設定數據庫連接資料
$db_host = "localhost:3307";  // 指定主機和端口
$db_id = "root";              // 資料庫用戶名
$db_pw = " ";                  // 資料庫密碼（空白鍵）
$db_name = "nb";              // 資料庫名稱

// 連接到 MySQL
$db_link = mysqli_connect($db_host, $db_id, $db_pw);

// 檢查連接是否成功
if (!$db_link) {
    die("連接失敗: " . mysqli_connect_error());
}
echo "連接成功!!<br>";

// 檢查資料庫是否已存在，若不存在則創建
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (mysqli_query($db_link, $sql)) {
    echo "資料庫已存在或創建成功!!<br>";
} else {
    die("創建資料庫失敗: " . mysqli_error($db_link) . "<br>");
}

// 選擇資料庫
mysqli_select_db($db_link, $db_name);

// 創建資料表
$create_table_sql = "CREATE TABLE IF NOT EXISTS 系統管理員 (
    管理員ID CHAR(20) PRIMARY KEY,
    姓名 VARCHAR(20),
    帳號 CHAR(20),
    密碼 CHAR(20),
    權限等級 INT,
    狀態 VARCHAR(10),
    操作日誌 VARCHAR(50)
)";

// 檢查資料表創建狀況
if (mysqli_query($db_link, $create_table_sql)) {
    echo "資料表「系統管理員」創建成功或已存在!!<br>";
} else {
    die("創建資料表失敗: " . mysqli_error($db_link) . "<br>");
}

// 插入提交的表單資料
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $管理員ID = $_POST['管理員ID'];
    $姓名 = $_POST['姓名'];
    $帳號 = $_POST['帳號'];
    $密碼 = $_POST['密碼'];
    $權限等級 = $_POST['權限等級'];
    $狀態 = $_POST['狀態'];
    $操作日誌 = $_POST['操作日誌'];

    $insert_sql = "INSERT INTO 系統管理員 (管理員ID, 姓名, 帳號, 密碼, 權限等級, 狀態, 操作日誌) 
                   VALUES ('$管理員ID', '$姓名', '$帳號', '$密碼', $權限等級, '$狀態', '$操作日誌')";

    if (mysqli_query($db_link, $insert_sql)) {
        echo "資料插入成功!!<br>";
    } else {
        die("插入資料失敗: " . mysqli_error($db_link) . "<br>");
    }
}

// 關閉資料庫連接
mysqli_close($db_link);
?>

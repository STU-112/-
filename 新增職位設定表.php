<?php 
// 資料庫連線參數
$db_host = "localhost:3307"; // 主機和端口
$db_user = "root";          // 資料庫用戶名
$db_pw = "3307";            // 資料庫密碼
$db_name = "職位設定";       // 資料庫名稱

// 連接資料庫
$db_link = mysqli_connect($db_host, $db_user, $db_pw);
if (!$db_link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 創建資料庫（如果不存在）
$sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci";
if (!mysqli_query($db_link, $sql)) {
    die("創建資料庫失敗: " . mysqli_error($db_link));
}

// 選擇資料庫
mysqli_select_db($db_link, $db_name);

// 創建資料表（如果不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS `職位設定表` (
    `編號` CHAR(5) PRIMARY KEY,
    `職位名稱` VARCHAR(10) NOT NULL,
	`上限` INT NOT NULL,
	`下限` INT NOT NULL,
    `建立時間` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link));
}

// 處理表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 獲取表單資料並防範 SQL 注入
    $職位名稱 = mysqli_real_escape_string($db_link, $_POST['title']); // 表單欄位 name="title"
    $編號 = mysqli_real_escape_string($db_link, $_POST['編號']);
	 $上限 = intval($_POST['上限']);
    $下限 = intval($_POST['下限']);

    // 驗證編號長度
    if (strlen($編號) !== 5) {
        die("錯誤：編號必須是 5 個字元！");
    }

    // 檢查上限是否大於下限
    if ($上限 < $下限) {
        die("錯誤：上限必須大於等於下限！");
    }

    // 檢查是否已存在該編號
    $check_sql = "SELECT * FROM `職位設定表` WHERE `編號` = '$編號'";
    $result = mysqli_query($db_link, $check_sql);
    if (mysqli_num_rows($result) > 0) {
        die("錯誤：該編號已存在！");
    }


    // 插入記錄
    $insert_record_sql = "INSERT INTO `職位設定表` (編號, `職位名稱`,上限,下限) 
    VALUES ('$編號', '$職位名稱', '$上限', '$下限')";

    if (mysqli_query($db_link, $insert_record_sql)) {
        echo "資料新增成功！";
    } else {
        echo "資料新增失敗: " . mysqli_error($db_link);
    }
}

// 關閉連接
mysqli_close($db_link);
?>

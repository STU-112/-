<?php
$servername = "localhost:3307";
$username = "root";
$password = "3307";
$dbname = "Review_comments";

// 建立資料庫連線
$db_link = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($db_link->connect_error) {
    die("連線失敗: " . $db_link->connect_error);
}

// 創建資料表（如果表不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS 主任審核意見 (
    流水號 INT AUTO_INCREMENT PRIMARY KEY,
    單號 VARCHAR(20) UNIQUE,
    審核意見 TEXT NOT NULL,
    狀態 CHAR(10) NOT NULL
)";
if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link));
}

// 插入記錄
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $number = mysqli_real_escape_string($db_link, $_POST['serial_count']);
    $opinion = mysqli_real_escape_string($db_link, $_POST['opinion']);
    $status = mysqli_real_escape_string($db_link, $_POST['status']);

    // 插入資料 SQL 語句
    $insert_record_sql = "INSERT INTO 主任審核意見 (單號, 審核意見, 狀態) 
                          VALUES ('$number', '$opinion', '$status')";

    // 執行 SQL 語句
    if (mysqli_query($db_link, $insert_record_sql)) {
        echo "<p style='color: green;'>記錄已成功提交！3 秒後將返回上一頁。</p>";
        echo "<script>setTimeout(function(){ history.back(); }, 3000);</script>";
    } else {
        if (mysqli_errno($db_link) == 1062) {
            // 1062 是重複鍵的錯誤代碼
			
			echo "<p style='color: orange;'>插入失敗：該單號已存在。3 秒後將返回上一頁。</p>";
        echo "<script>setTimeout(function(){ history.back(); }, 3000);</script>";
           
        } else {
            echo "插入記錄失敗: " . mysqli_error($db_link);
        }
    }
}

// 關閉連接
mysqli_close($db_link);
?>

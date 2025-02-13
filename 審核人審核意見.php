<?php
include '啟動Session.php';

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

$dbname_註冊 = "註冊"; 
$db_link_註冊 = new mysqli($servername, $username, $password, $dbname_註冊);

// 檢查連線 
if ($db_link_註冊->connect_error) { 
    die("註冊資料庫連線失敗: " . $db_link_註冊->connect_error); 
}


// 讀取職位選單
$職位_sql = "SELECT 職位名稱 FROM 職位設定表";
$職位_result = $db_link_註冊->query($職位_sql);

// 讀取登入者的職位名稱
// $帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link_註冊->query($職位查詢);
$職位名稱 = "未知職位";
if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $職位名稱 = $row["權限管理"];
}


// 讀取註冊資料表中的權限（這裡應該是權限管理，而不是職位名稱）
$職位_sql = "SELECT DISTINCT 權限管理 FROM 註冊資料表";
$職位_result = $db_link_註冊->query($職位_sql);

$職位選項 = "";
if ($職位_result->num_rows > 0) {
    while ($row = $職位_result->fetch_assoc()) {
        $職位選項 .= "<option value='" . htmlspecialchars($row["權限管理"]) . "'>" . htmlspecialchars($row["權限管理"]) . "</option>";
    }
}




$職位名稱 = mysqli_real_escape_string($db_link, $職位名稱); // 防止 SQL 注入
$table_name = "`" . $職位名稱 . "審核意見`"; // 確保表名正確


// 創建資料表（如果表不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS $table_name (
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
  $職位名稱 = mysqli_real_escape_string($db_link, $職位名稱); // 防止 SQL 注入
$table_name = "`" . $職位名稱 . "審核意見`"; // 確保表名正確

$insert_record_sql = "INSERT INTO $table_name (單號, 審核意見, 狀態) 
                      VALUES ('$number', '$opinion', '$status')";
					  
					  
					  
					  
					  
   // 執行 SQL 語句
if (mysqli_query($db_link, $insert_record_sql)) {
    echo "<p style='color: green;'>記錄已成功提交！3 秒後將返回頁面。</p>";
    echo "<script>setTimeout(function(){ window.location.href = '審核人.php'; }, 3000);</script>";
} else {
    if (mysqli_errno($db_link) == 1062) {
        // 1062 是重複鍵的錯誤代碼
        echo "<p style='color: orange;'>插入失敗：該單號已存在。3 秒後將返回頁面。</p>";
        echo "<script>setTimeout(function(){ window.location.href = '審核人.php'; }, 3000);</script>";
    } else {
        echo "插入記錄失敗: " . mysqli_error($db_link);
    }
}
}

// 關閉連接
mysqli_close($db_link);
?>

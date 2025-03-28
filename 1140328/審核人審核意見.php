<?php
include '啟動Session.php';

$servername = "localhost:3307";
$username = "root";
$password = "3307";
$dbname = "基金會";

// 建立資料庫連線
$db_link = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($db_link->connect_error) {
    die("連線失敗: " . $db_link->connect_error);
}

// 檢查連線 
if ($db_link->connect_error) { 
    die("註冊資料庫連線失敗: " . $db_link->connect_error); 
}

// 讀取職位選單
$職位_sql = "SELECT 職位名稱 FROM 職位";
$職位_result = $db_link->query($職位_sql);

// 讀取登入者的職位名稱
$帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link->query($職位查詢);
$職位名稱 = "未知職位";
if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $職位名稱 = $row["權限管理"];
}

// 讀取註冊資料表中的權限（這裡應該是權限管理，而不是職位名稱）
$職位_sql = "SELECT DISTINCT 權限管理 FROM 註冊資料表";
$職位_result = $db_link->query($職位_sql);

$職位選項 = "";
if ($職位_result->num_rows > 0) {
    while ($row = $職位_result->fetch_assoc()) {
        $職位選項 .= "<option value='" . htmlspecialchars($row["權限管理"]) . "'>" . htmlspecialchars($row["權限管理"]) . "</option>";
    }
}

$職位編號查詢 = "SELECT 編號 FROM 職位 WHERE 職位名稱 = '$職位名稱' LIMIT 1";
$職位編號結果 = $db_link->query($職位編號查詢);

// 檢查查詢是否有回傳結果
if ($職位編號結果 && $職位編號結果->num_rows > 0) {
    $row = $職位編號結果->fetch_assoc(); // 取得資料
    $當前職位編號 = $row["編號"];
}

// $當前職位編號 = null;
$職位編號結果 = $職位編號結果 ?? 0; // 確保至少是 0
$下一個職位名稱 = "無下一位審核者";
$下一個職位查詢 = "SELECT * FROM 職位 WHERE 編號 > $當前職位編號 ORDER BY 編號 ASC LIMIT 1";
$下一個職位結果 = $db_link->query($下一個職位查詢);

if ($下一個職位結果 && $下一個職位結果->num_rows > 0) {
    $row = $下一個職位結果->fetch_assoc();
    $職位編號結果 = $row["編號"];
}

$下一位審查者 = "SELECT * FROM 職位 WHERE 編號 = $職位編號結果";
$審查者_result = $db_link->query($下一位審查者);
if ($審查者_result && $審查者_result->num_rows > 0) {
    $row = $審查者_result->fetch_assoc(); // 取得資料
    $審查者 = $row["職位名稱"];
}

$職位名稱 = mysqli_real_escape_string($db_link, $職位名稱); // 防止 SQL 注入
$table_name = "`" . $職位名稱 . "審核意見`"; // 確保表名正確


// 創建資料表（如果表不存在），加入 "審核人"、"職位名稱"、"審核時間" 欄位
$create_table_sql = "CREATE TABLE IF NOT EXISTS $table_name  (
    流水號 INT AUTO_INCREMENT PRIMARY KEY,
    單號 VARCHAR(20) UNIQUE,
    審核意見 TEXT NOT NULL,
    狀態 CHAR(10) NOT NULL,
    審核部門 VARCHAR(50) NOT NULL,
    職位名稱 VARCHAR(50) NOT NULL,
    審核時間 DATETIME NOT NULL
)";

if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link));
}

// 取得登入者資訊
$帳號 = $_SESSION["帳號"];
$用戶查詢 = "SELECT 部門, 員工編號, 帳號, 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$用戶結果 = $db_link->query($用戶查詢);
$審核部門 = "未知";
// $職位名稱 = "未知職位";

if ($用戶結果 && $用戶結果->num_rows > 0) {
    $row = $用戶結果->fetch_assoc();
    $審核部門 = $row["部門"];
	
    // $職位名稱 = $row["權限管理"];  
	// 取得職位名稱
}

$審核部門 = mysqli_real_escape_string($db_link, $審核部門);
$職位名稱 = mysqli_real_escape_string($db_link, $職位名稱);
$審核時間 = date("Y-m-d H:i:s"); // 取得當前時間

// 插入記錄
if ($_SERVER["REQUEST_METHOD"] == "POST") {
	// 受款人代號
    $number = mysqli_real_escape_string($db_link, $_POST['serial_count']);
    $opinion = mysqli_real_escape_string($db_link, $_POST['opinion']);
    $status = mysqli_real_escape_string($db_link, $_POST['status']);

    // 插入資料 SQL 語句，加入 "審核人"、"職位名稱"、"審核時間"
    $insert_record_sql = "INSERT INTO $table_name  (單號, 審核意見, 狀態, 審核部門, 職位名稱, 審核時間) 
                          VALUES ('$number', '$opinion', '$status', '$審核部門', '$審查者', '$審核時間')";

    // 執行 SQL 語句
    if (mysqli_query($db_link, $insert_record_sql)) {
        echo "<p style='color: green;'>記錄已成功提交！3 秒後將返回頁面。</p>";
        echo "<script>setTimeout(function(){ window.location.href = '審核人.php'; }, 3000);</script>";
		
		
		
		// 查詢目前經辦人交易檔的原始審核狀態
$查詢原始狀態 = "SELECT 審核狀態 FROM 經辦人交易檔 WHERE 受款人代號 = '$number' LIMIT 1";
$原始結果 = $db_link->query($查詢原始狀態);

if ($原始結果 && $原始結果->num_rows > 0) {
    $row = $原始結果->fetch_assoc();
    $原始狀態 = $row["審核狀態"];

    // 預設新狀態
    $新狀態 = $原始狀態;

    // 如果職位是出納，根據原始狀態決定新狀態
    if ($職位名稱 === '出納' && $status === '通過') {
        if ($原始狀態 === '預支審核中') {
            $新狀態 = '預支完成審查';
        } elseif ($原始狀態 === '報帳審核中') {
            $新狀態 = '報帳完成審查';
        } else {
            $新狀態 = '不通過'; // 若狀態非預期，則不變
        }
    } 
    // 如果職位不是出納，且 status 為 "不通過"，直接更新為 "不通過"
    elseif ($職位名稱 !== '出納' && $status === '不通過') {
        $新狀態 = '不通過';
    }

    // 執行更新
    $更新狀態_SQL = "UPDATE 經辦人交易檔 SET 審核狀態 = '$新狀態' WHERE 受款人代號 = '$number'";
    mysqli_query($db_link, $更新狀態_SQL);
}

	
    } else {
		
		
        if (mysqli_errno($db_link) == 1062) {
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

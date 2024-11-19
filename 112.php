<?php
// 連接資料庫
$server = 'localhost:3307'; // 伺服器名稱
$用戶名 = 'root'; // 用戶名
$密碼 = '3307'; // 密碼 (設為空字串而不是空格)
$資料庫 = '預支'; // 資料庫名稱

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
$create_table_sql = "CREATE TABLE IF NOT EXISTS pay_table (
    count VARCHAR(50) PRIMARY KEY,
    受款人 VARCHAR(50),
    填表日期 DATE DEFAULT NULL,
    付款日期 DATE DEFAULT NULL,
    支出項目 VARCHAR(50),
    活動名稱 VARCHAR(50) DEFAULT NULL,
    專案日期 DATE DEFAULT NULL,
    獎學金人數 INT DEFAULT NULL,
    專案名稱 CHAR(10) DEFAULT NULL,
    主題 CHAR(50) DEFAULT NULL,
    獎學金日期 DATE DEFAULT NULL,
	經濟扶助 CHAR(10) DEFAULT NULL,
	其他項目 CHAR(50) DEFAULT NULL,
    說明 CHAR(100) DEFAULT NULL,
	支付方式 CHAR(10),
	
	國字金額_hidden CHAR(50),
	
	金額 DECIMAL(10,2) DEFAULT NULL, 
	簽收金額 DECIMAL(10,2) DEFAULT NULL,
	簽收人 CHAR(10) DEFAULT NULL,
	簽收日 DATE DEFAULT NULL,
	銀行郵局 CHAR(10) DEFAULT NULL,
	分行 CHAR(10) DEFAULT NULL,
	戶名 CHAR(10) DEFAULT NULL,
	帳戶 CHAR(10) DEFAULT NULL,
	票號 CHAR(10) DEFAULT NULL,
	到期日 DATE DEFAULT NULL,
	預收金額 DECIMAL(10,2) DEFAULT NULL
)";
if (mysqli_query($連接, $create_table_sql)) {
    echo "支用資料表創建成功或已存在!!<br>";
} else {
    die("創建支用資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 生成流水號函數
function generateSerialNumber($連接) {
    $now = new DateTime();
    $year = $now->format('Y') - 1911;
    $month = str_pad($now->format('m'), 2, '0', STR_PAD_LEFT);
    $prefix = "{$year}{$month}";
    $sql = "SELECT COUNT(*) as count FROM pay_table WHERE count LIKE '$prefix%'";
    $result = mysqli_query($連接, $sql);
    if (!$result) {
        die("查詢流水號失敗: " . mysqli_error($連接) . "<br>");
    }
    $row = mysqli_fetch_assoc($result);
    $serialNumber = $prefix . str_pad($row['count'] + 1, 5, '0', STR_PAD_LEFT);
    return $serialNumber;
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 驗證必填字段
    $必填字段 = ['填表日期', '受款人', '支出項目'];
    foreach ($必填字段 as $field) {
        if (empty($_POST[$field])) {
            die("請填寫所有必填字段。");
        }
    }

    // 取得表單數據
    $受款人 = mysqli_real_escape_string($連接, $_POST['受款人']);
    $填表日期 = mysqli_real_escape_string($連接, $_POST['填表日期']);
    $付款日期 = !empty($_POST['付款日期']) ? "'" . mysqli_real_escape_string($連接, $_POST['付款日期']) . "'" : "NULL";
    $支出項目 = mysqli_real_escape_string($連接, $_POST['支出項目']);
    $活動名稱 = !empty($_POST['活動名稱']) ? "'" . mysqli_real_escape_string($連接, $_POST['活動名稱']) . "'" : "NULL";
    $專案日期 = !empty($_POST['專案日期']) ? "'" . mysqli_real_escape_string($連接, $_POST['專案日期']) . "'" : "NULL";
    $獎學金人數 = !empty($_POST['獎學金人數']) ? intval($_POST['獎學金人數']) : "NULL";
    $專案名稱 = !empty($_POST['專案名稱']) ? "'" . mysqli_real_escape_string($連接, $_POST['專案名稱']) . "'" : "NULL";
    $主題 = !empty($_POST['主題']) ? "'" . mysqli_real_escape_string($連接, $_POST['主題']) . "'" : "NULL";
    $獎學金日期 = !empty($_POST['獎學金日期']) ? "'" . mysqli_real_escape_string($連接, $_POST['獎學金日期']) . "'" : "NULL";
	$經濟扶助 = !empty($_POST['經濟扶助']) ? "'" . mysqli_real_escape_string($連接, $_POST['經濟扶助']) . "'" : "NULL";
	$其他項目 = isset($_POST['其他項目']) ? implode(", ", $_POST['其他項目']) : "NULL"; // 將選中的項目轉為字串
	$說明 = mysqli_real_escape_string($連接, $_POST['說明']);
	$支付方式 = mysqli_real_escape_string($連接, $_POST['支付方式']);
	
	$國字金額 = isset($_POST['國字金額_hidden']) ? $_POST['國字金額_hidden'] : '';
	
	$金額 = !empty($_POST['金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['金額']) . "'" : "NULL";
	$簽收金額 = !empty($_POST['簽收金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收金額']) . "'" : "NULL";
	$簽收人 = !empty($_POST['簽收人']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收人']) . "'" : "NULL";
	$簽收日 = !empty($_POST['簽收日']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收日']) . "'" : "NULL";
	$銀行郵局 = !empty($_POST['銀行郵局']) ? "'" . mysqli_real_escape_string($連接, $_POST['銀行郵局']) . "'" : "NULL";
	$分行 = !empty($_POST['分行']) ? "'" . mysqli_real_escape_string($連接, $_POST['分行']) . "'" : "NULL";
	$戶名 = !empty($_POST['戶名']) ? "'" . mysqli_real_escape_string($連接, $_POST['戶名']) . "'" : "NULL";
	$帳戶 = !empty($_POST['帳戶']) ? "'" . mysqli_real_escape_string($連接, $_POST['帳戶']) . "'" : "NULL";
	$票號 = !empty($_POST['票號']) ? "'" . mysqli_real_escape_string($連接, $_POST['票號']) . "'" : "NULL";
	$到期日 = !empty($_POST['到期日']) ? "'" . mysqli_real_escape_string($連接, $_POST['到期日']) . "'" : "NULL";
	$預收金額 = !empty($_POST['預收金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['預收金額']) . "'" : "NULL";
	

    // 生成流水號
    $流水號 = generateSerialNumber($連接);

    // 插入資料
    $insert_record_sql = "INSERT INTO pay_table (count, 受款人, 填表日期, 付款日期, 支出項目, 活動名稱, 專案日期, 獎學金人數, 專案名稱, 主題, 獎學金日期,經濟扶助,其他項目, 說明,支付方式,國字金額_hidden,金額,簽收金額,簽收人,簽收日,銀行郵局,分行,戶名,帳戶,票號,到期日,預收金額)
	VALUES ('$流水號', '$受款人', '$填表日期', $付款日期, '$支出項目', $活動名稱, $專案日期, $獎學金人數, $專案名稱, $主題, $獎學金日期,  $經濟扶助,'$其他項目','$說明','$支付方式','$國字金額',$金額,$簽收金額,$簽收人,$簽收日,$銀行郵局,$分行,$戶名,$帳戶,$票號,$到期日,$預收金額)";

    if (mysqli_query($連接, $insert_record_sql)) {
        echo "表單已成功提交!!<br>";
    } else {
        die("插入資料失敗: " . mysqli_error($連接) . "<br>SQL: " . $insert_record_sql);
    }
}
include '尋找很久.html';
// 關閉資料庫連接
mysqli_close($連接);
?>

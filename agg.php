<?php  
// 連接資料庫
$server = 'localhost:3307'; // 伺服器名稱
$用戶名 = 'root'; // 用戶名
$密碼 = ' '; // 密碼 (設為空字串)
$資料庫 = '預支'; // 資料庫名稱

// 連接到 MySQL
$連接 = mysqli_connect($server, $用戶名, $密碼);

// 檢查連接
if (!$連接) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查資料庫是否已存在，若不存在則創建
$sql = "CREATE DATABASE IF NOT EXISTS `$資料庫`";
if (mysqli_query($連接, $sql)) {
    //echo "資料庫已存在或創建成功!!<br>";
} else {
    die("創建資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 選擇資料庫
if (!mysqli_select_db($連接, $資料庫)) {
    die("選擇資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 創建資料表（根據上述修改後的結構）
$create_table_sql = "CREATE TABLE IF NOT EXISTS pay_table (
    `count` VARCHAR(50) NOT NULL UNIQUE,
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
    國字金額 DECIMAL(10,2),
    國字金額_hidden CHAR(50),
    簽收金額 DECIMAL(10,2) DEFAULT NULL,
    簽收人 CHAR(10) DEFAULT NULL,
    簽收日 DATE DEFAULT NULL,
    銀行郵局 CHAR(10) DEFAULT NULL,
    分行 CHAR(10) DEFAULT NULL,
    戶名 CHAR(10) DEFAULT NULL,
    帳戶 CHAR(10) DEFAULT NULL,
    票號 CHAR(10) DEFAULT NULL,
    到期日 DATE DEFAULT NULL,
    預支金額 DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (`count`)
) ENGINE=InnoDB"; // 使用 InnoDB 以支援事務

if (mysqli_query($連接, $create_table_sql)) {
    //echo "支用資料表創建成功或已存在!!<br>";
} else {
    die("創建支用資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 生成帶有 'A' + 民國年 + 月 + 5位數序號的流水號函數
function generateSerialNumber($連接) {
    // 取得當前時間
    $now = new DateTime();
    $year = $now->format('Y') - 1911; // 民國年
    $month = str_pad($now->format('m'), 2, '0', STR_PAD_LEFT); // 月份，兩位數
    $prefix = "A{$year}{$month}"; // 前綴，如 A11311

    // 查詢當前月份的最大流水號
    $sql = "SELECT MAX(`count`) AS max_count FROM pay_table WHERE `count` LIKE '{$prefix}%'";
    $result = mysqli_query($連接, $sql);
    if (!$result) {
        die("查詢最大流水號失敗: " . mysqli_error($連接) . "<br>");
    }
    $row = mysqli_fetch_assoc($result);
    if ($row['max_count']) {
        // 取得當前最大的序號並加一
        $last_serial = intval(substr($row['max_count'], strlen($prefix)));
        $new_serial = $last_serial + 1;
    } else {
        // 如果沒有紀錄，從 1 開始
        $new_serial = 1;
    }

    // 生成新的流水號，填充到 5 位數
    $serialNumber = $prefix . str_pad($new_serial, 5, '0', STR_PAD_LEFT);
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

    // 取得表單數據並進行轉義
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
    $其他項目 = isset($_POST['其他項目']) ? "'" . mysqli_real_escape_string($連接, implode(", ", $_POST['其他項目'])) . "'" : "NULL"; // 將選中的項目轉為字串
    $說明 = mysqli_real_escape_string($連接, $_POST['說明']);
    $支付方式 = mysqli_real_escape_string($連接, $_POST['支付方式']);
    $國字金額 = isset($_POST['國字金額']) ? mysqli_real_escape_string($連接, $_POST['國字金額']) : '';
    $國字金額_hidden = isset($_POST['國字金額_hidden']) ? mysqli_real_escape_string($連接, $_POST['國字金額_hidden']) : '';

    $簽收金額 = !empty($_POST['簽收金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收金額']) . "'" : "NULL";
    $簽收人 = !empty($_POST['簽收人']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收人']) . "'" : "NULL";
    $簽收日 = !empty($_POST['簽收日']) ? "'" . mysqli_real_escape_string($連接, $_POST['簽收日']) . "'" : "NULL";
    $銀行郵局 = !empty($_POST['銀行郵局']) ? "'" . mysqli_real_escape_string($連接, $_POST['銀行郵局']) . "'" : "NULL";
    $分行 = !empty($_POST['分行']) ? "'" . mysqli_real_escape_string($連接, $_POST['分行']) . "'" : "NULL";
    $戶名 = !empty($_POST['戶名']) ? "'" . mysqli_real_escape_string($連接, $_POST['戶名']) . "'" : "NULL";
    $帳戶 = !empty($_POST['帳戶']) ? "'" . mysqli_real_escape_string($連接, $_POST['帳戶']) . "'" : "NULL";
    $票號 = !empty($_POST['票號']) ? "'" . mysqli_real_escape_string($連接, $_POST['票號']) . "'" : "NULL";
    $到期日 = !empty($_POST['到期日']) ? "'" . mysqli_real_escape_string($連接, $_POST['到期日']) . "'" : "NULL";
    $預支金額 = !empty($_POST['預支金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['預支金額']) . "'" : "NULL";

    // 開始事務處理
    mysqli_begin_transaction($連接);

    try {
        // 生成新的流水號
        $流水號 = generateSerialNumber($連接);

        // 插入資料，包括 `count`
        $insert_record_sql = "INSERT INTO pay_table 
            (`count`, 受款人, 填表日期, 付款日期, 支出項目, 活動名稱, 專案日期, 獎學金人數, 專案名稱, 主題, 獎學金日期, 經濟扶助, 其他項目, 說明, 支付方式, 國字金額, 國字金額_hidden, 簽收金額, 簽收人, 簽收日, 銀行郵局, 分行, 戶名, 帳戶, 票號, 到期日, 預支金額)
            VALUES 
            ('$流水號', '$受款人', '$填表日期', $付款日期, '$支出項目', $活動名稱, $專案日期, $獎學金人數, $專案名稱, $主題, $獎學金日期, $經濟扶助, $其他項目, '$說明', '$支付方式', '$國字金額', '$國字金額_hidden', $簽收金額, $簽收人, $簽收日, $銀行郵局, $分行, $戶名, $帳戶, $票號, $到期日, $預支金額)";

        if (!mysqli_query($連接, $insert_record_sql)) {
            // 如果插入失敗且是因為重複的 `count`，則重試
            if (mysqli_errno($連接) == 1062) { // 1062 是 Duplicate entry 錯誤碼
                // 重新生成流水號並重試
                mysqli_rollback($連接);
                // 開始新的事務
                mysqli_begin_transaction($連接);
                $流水號 = generateSerialNumber($連接);
                $insert_record_sql = "INSERT INTO pay_table 
                    (`count`, 受款人, 填表日期, 付款日期, 支出項目, 活動名稱, 專案日期, 獎學金人數, 專案名稱, 主題, 獎學金日期, 經濟扶助, 其他項目, 說明, 支付方式, 國字金額, 國字金額_hidden, 簽收金額, 簽收人, 簽收日, 銀行郵局, 分行, 戶名, 帳戶, 票號, 到期日, 預支金額)
                    VALUES 
                    ('$流水號', '$受款人', '$填表日期', $付款日期, '$支出項目', $活動名稱, $專案日期, $獎學金人數, $專案名稱, $主題, $獎學金日期, $經濟扶助, $其他項目, '$說明', '$支付方式', '$國字金額', '$國字金額_hidden', $簽收金額, $簽收人, $簽收日, $銀行郵局, $分行, $戶名, $帳戶, $票號, $到期日, $預支金額)";
                
                if (!mysqli_query($連接, $insert_record_sql)) {
                    throw new Exception("插入資料失敗: " . mysqli_error($連接));
                }
            } else {
                throw new Exception("插入資料失敗: " . mysqli_error($連接));
            }
        }

        // 提交事務
        mysqli_commit($連接);

        echo "表單已成功提交!!<br>";
        header("Location: ll1.html");
        exit(); // 確保停止執行後續代碼

    } catch (Exception $e) {
        // 發生錯誤時回滾事務
        mysqli_rollback($連接);
        die($e->getMessage());
    }
}

// 關閉資料庫連接
mysqli_close($連接);
?>

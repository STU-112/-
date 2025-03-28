<?php
// 連接資料庫
$server = 'localhost:3307'; // 伺服器名稱
$username = 'root'; // 用戶名
$password = '3307'; // 密碼 (空字串)
$database = '支出報帳'; // 資料庫名稱

// 連接到 MySQL
$conn = new mysqli($server, $username, $password);

// 檢查連接
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 建立資料庫（若不存在）
$sql = "CREATE DATABASE IF NOT EXISTS $database";
if ($conn->query($sql) !== TRUE) {
    die("建立資料庫失敗：" . $conn->error);
}

// 使用資料庫
$conn->select_db($database);

// 建立資料表 支出報帳表單
$createTable1 = "CREATE TABLE IF NOT EXISTS 支出報帳表單 (
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
if ($conn->query($createTable1) !== TRUE) {
    die("建立支出報帳表單失敗：" . $conn->error);
}

// 建立資料表 uploads
$createTable2 = "CREATE TABLE IF NOT EXISTS uploads (
    count VARCHAR(50) PRIMARY KEY, 
    image_path VARCHAR(255) DEFAULT NULL, 
    csv_path VARCHAR(255) DEFAULT NULL, 
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
if ($conn->query($createTable2) !== TRUE) {
    die("建立 uploads 資料表失敗：" . $conn->error);
}

// 生成流水號函數
function generateSerialNumber($conn) {
    $now = new DateTime();
    $year = $now->format('Y') - 1911; // 民國年
    $month = str_pad($now->format('m'), 2, '0', STR_PAD_LEFT);
    $prefix = "B{$year}{$month}";
    $sql = "SELECT COUNT(*) AS total FROM 支出報帳表單 WHERE count LIKE '$prefix%'";
    $result = $conn->query($sql);
    if (!$result) {
        die("查詢流水號失敗：" . $conn->error);
    }
    $row = $result->fetch_assoc();
    $serialNumber = $prefix . str_pad($row['total'] + 1, 5, '0', STR_PAD_LEFT);
    return $serialNumber;
}

// 檢查是否有表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 處理支出報帳表單數據
    $流水號 = generateSerialNumber($conn);

    // 逐一處理欄位，確保資料完整性
    $fields = [
        '受款人', '填表日期', '付款日期', '支出項目', '活動名稱', '專案日期',
        '獎學金人數', '專案名稱', '主題', '獎學金日期', '經濟扶助', '其他項目',
        '說明', '支付方式', '國字金額_hidden', '金額', '簽收金額', '簽收人',
        '簽收日', '銀行郵局', '分行', '戶名', '帳戶', '票號', '到期日', '預收金額'
    ];
    $data = [];
    foreach ($fields as $field) {
        $data[$field] = isset($_POST[$field]) && $_POST[$field] !== '' 
            ? "'" . $conn->real_escape_string($_POST[$field]) . "'"
            : "NULL";
    }

    // 插入到 支出報帳表單
    $insertTable1 = "INSERT INTO 支出報帳表單 (
        count, 受款人, 填表日期, 付款日期, 支出項目, 活動名稱, 專案日期, 獎學金人數,
        專案名稱, 主題, 獎學金日期, 經濟扶助, 其他項目, 說明, 支付方式, 國字金額_hidden,
        金額, 簽收金額, 簽收人, 簽收日, 銀行郵局, 分行, 戶名, 帳戶, 票號, 到期日, 預收金額
    ) VALUES (
        '$流水號', {$data['受款人']}, {$data['填表日期']}, {$data['付款日期']}, {$data['支出項目']}, 
        {$data['活動名稱']}, {$data['專案日期']}, {$data['獎學金人數']}, {$data['專案名稱']}, 
        {$data['主題']}, {$data['獎學金日期']}, {$data['經濟扶助']}, {$data['其他項目']}, 
        {$data['說明']}, {$data['支付方式']}, {$data['國字金額_hidden']}, {$data['金額']}, 
        {$data['簽收金額']}, {$data['簽收人']}, {$data['簽收日']}, {$data['銀行郵局']}, 
        {$data['分行']}, {$data['戶名']}, {$data['帳戶']}, {$data['票號']}, {$data['到期日']}, 
        {$data['預收金額']}
    )";

    if (!$conn->query($insertTable1)) {
        die("插入支出報帳表單失敗：" . $conn->error);
    }

    // 處理文件上傳
    $imagePath = null;
    $csvPath = null;

    if (!empty($_FILES['image-upload']['name'])) {
        $imagePath = "uploads/" . basename($_FILES['image-upload']['name']);
        move_uploaded_file($_FILES['image-upload']['tmp_name'], $imagePath);
    }

    if (!empty($_FILES['csv-upload']['name'])) {
        $csvPath = "uploads/" . basename($_FILES['csv-upload']['name']);
        move_uploaded_file($_FILES['csv-upload']['tmp_name'], $csvPath);
    }

    // 插入到 uploads
    $insertTable2 = "INSERT INTO uploads (
        count, image_path, csv_path
    ) VALUES (
        '$流水號', " . ($imagePath ? "'$imagePath'" : "NULL") . ", " . ($csvPath ? "'$csvPath'" : "NULL") . "
    )";

    if (!$conn->query($insertTable2)) {
        die("插入 uploads 資料表失敗：" . $conn->error);
    }

    echo "表單提交成功！流水號：$流水號";
}

// 關閉資料庫連接
$conn->close();
?>
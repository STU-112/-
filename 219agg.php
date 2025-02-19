<?php 
header('Content-Type: application/json'); // 設定回應 JSON
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ------------------- (1) 連線資料庫 -------------------
$伺服器  = 'localhost:3307';
$使用者  = 'root';
$密碼    = '';  // 空密碼請用 ''
$資料庫  = '預支';

$連接 = new mysqli($伺服器, $使用者, $密碼);

// 檢查連線
if ($連接->connect_error) {
    die(json_encode(["success" => false, "message" => "資料庫連線失敗: " . $連接->connect_error]));
}

// 建立資料庫 (如果尚未存在)
$sql = "CREATE DATABASE IF NOT EXISTS `$資料庫` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
$連接->query($sql);
$連接->select_db($資料庫);
$連接->set_charset("utf8mb4");

// ------------------- (2) 建立資料表 -------------------
$sqlCreate1 = "
CREATE TABLE IF NOT EXISTS `經辦業務檔` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `業務代號` VARCHAR(50) DEFAULT NULL,
  `業務名稱` VARCHAR(50) DEFAULT NULL,
  `活動日期` DATE DEFAULT NULL,
  `活動名稱` VARCHAR(100) DEFAULT NULL,
  `備註` TEXT DEFAULT NULL,
  `說明` TEXT DEFAULT NULL
) ENGINE=InnoDB;
";
$連接->query($sqlCreate1);

$sqlCreate2 = "
CREATE TABLE IF NOT EXISTS `經辦交易檔` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `交易單號` VARCHAR(50) UNIQUE NOT NULL,
  `交易時間` DATE DEFAULT NULL,
  `支付方式` VARCHAR(20) DEFAULT NULL,
  `金額` DECIMAL(11,2) DEFAULT NULL
) ENGINE=InnoDB;
";
$連接->query($sqlCreate2);

// ------------------- (3) 處理表單提交 -------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 避免 SQL Injection
    function 安全值($值, $連接) {
        return isset($_POST[$值]) && trim($_POST[$值]) !== '' ? "'" . $連接->real_escape_string($_POST[$值]) . "'" : "NULL";
    }

    // 交易時間 (依據支付方式)
    $支付方式 = $_POST['支付方式'] ?? '';
    $交易時間 = "NULL";

    if ($支付方式 === '支票') {
        $交易時間 = 安全值('到期日', $連接);
    } elseif ($支付方式 === '現金') {
        $交易時間 = 安全值('簽收日', $連接);
    } else {
        $交易時間 = 安全值('付款日期', $連接);
    }

    // 活動日期 (依據支出項目)
    $支出項目 = $_POST['支出項目'] ?? '';
    $活動日期 = "NULL";

    if ($支出項目 === '活動費用') {
        $活動日期 = 安全值('專案日期', $連接);
    } elseif ($支出項目 === '獎學金') {
        $活動日期 = 安全值('獎學金日期', $連接);
    }

    // 其他欄位
    $活動名稱 = ($支出項目 === '經濟扶助' || $支出項目 === '其他') ? "NULL" : 安全值('活動名稱', $連接);
    $說明 = 安全值('說明', $連接);

    // 備註 (處理獎學金人數 & 其他選項)
    $備註 = "NULL";
    if ($支出項目 === '獎學金') {
        $num = $_POST['獎學金人數'] ?? '';
        $subj = $_POST['主題'] ?? '';
        $tmp = "人數: $num / 主題: $subj";
        if (trim($tmp) !== '人數: / 主題:') {
            $備註 = "'" . $連接->real_escape_string($tmp) . "'";
        }
    } elseif ($支出項目 === '其他' && isset($_POST['其他項目']) && is_array($_POST['其他項目'])) {
        $tmp = implode(',', $_POST['其他項目']);
        if ($tmp !== '') {
            $備註 = "'" . $連接->real_escape_string($tmp) . "'";
        }
    }

    // 金額
    $金額 = isset($_POST['國字金額']) ? floatval($_POST['國字金額']) : 0;

    // **插入經辦業務檔**
    $業務代號 = 'TP001'; // 假設的代號
    $業務名稱Val = "'" . $連接->real_escape_string($支出項目) . "'";

    $sqlBiz = "
      INSERT INTO `經辦業務檔` (業務代號, 業務名稱, 活動日期, 活動名稱, 備註, 說明)
      VALUES ('$業務代號', $業務名稱Val, $活動日期, $活動名稱, $備註, $說明)
    ";
    if (!$連接->query($sqlBiz)) {
        die(json_encode(["success" => false, "message" => "插入經辦業務檔失敗: " . $連接->error]));
    }
    $lastBizId = $連接->insert_id;

    // **自動產生交易單號**
    $交易單號 = "TRX" . str_pad($lastBizId, 6, '0', STR_PAD_LEFT);
    $支付方式Val = "'" . $連接->real_escape_string($支付方式) . "'";

    // **插入經辦交易檔**
    $sqlTx = "
      INSERT INTO `經辦交易檔` (交易單號, 交易時間, 支付方式, 金額)
      VALUES ('$交易單號', $交易時間, $支付方式Val, '$金額')
    ";
    if (!$連接->query($sqlTx)) {
        die(json_encode(["success" => false, "message" => "插入經辦交易檔失敗: " . $連接->error]));
    }

    // **回傳 JSON 給前端**
    echo json_encode([
        "success" => true,
        "message" => "資料插入成功！",
        "交易單號" => $交易單號,
        "支出項目" => $支出項目,
        "交易時間" => $交易時間,
        "活動日期" => $活動日期
    ]);
}

$連接->close();
?>

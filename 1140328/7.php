<?php 
error_reporting(E_ALL);  
ini_set('display_errors', 1);  
date_default_timezone_set('Asia/Taipei');  

$host     = 'localhost:3307';   
$dbname   = '基金會';             
$username = 'root';             
$password = '3307';                 

try {     
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);     
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);      
    $pdo->exec("         
        CREATE DATABASE IF NOT EXISTS `$dbname`
        DEFAULT CHARACTER SET utf8
        COLLATE utf8_general_ci;
    ");     
    $pdo->exec("USE `$dbname`;");      
    
    // 1) 受款人資料檔     
    $pdo->exec("         
        CREATE TABLE IF NOT EXISTS 受款人資料檔 (
            受款人代號  VARCHAR(5)  NOT NULL,
            受款人姓名  VARCHAR(50) NOT NULL,
            手機號碼    VARCHAR(20) NOT NULL,
            地址        VARCHAR(100) NOT NULL,
            PRIMARY KEY (受款人代號)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");      
    
    // 2) 經辦業務檔     
    $pdo->exec("         
        CREATE TABLE IF NOT EXISTS 經辦業務檔 (
            業務代號     VARCHAR(5)   NOT NULL,
            受款人代號   VARCHAR(5)   NOT NULL,
            填表日期     DATETIME     NOT NULL,
            付款日期     DATE         NOT NULL,
            支出項目     VARCHAR(50)  NOT NULL,
            經辦代號     VARCHAR(50)  NOT NULL,
            說明         TEXT         DEFAULT NULL,
            專案活動     VARCHAR(50)  DEFAULT NULL,
            活動名稱     VARCHAR(100) DEFAULT NULL,
            專案日期     DATE         DEFAULT NULL,
            獎學金人數   INT          DEFAULT NULL,
            獎學金專案   VARCHAR(100) DEFAULT NULL,
            主題         VARCHAR(100) DEFAULT NULL,
            獎學金日期   DATE         DEFAULT NULL,
            經濟扶助     VARCHAR(50)  DEFAULT NULL,
            其他項目     TEXT         DEFAULT NULL,
            PRIMARY KEY (業務代號),
            CONSTRAINT fk_業務_受款人
              FOREIGN KEY (受款人代號)
              REFERENCES 受款人資料檔 (受款人代號)
              ON UPDATE CASCADE
              ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");      
    
    // 3) 經辦人交易檔     
    $pdo->exec("         
        CREATE TABLE IF NOT EXISTS 經辦人交易檔 (
            交易單號    VARCHAR(15)   NOT NULL,
            受款人代號  VARCHAR(5)    NOT NULL,
            業務代號    VARCHAR(5)    NOT NULL,
            金額        DECIMAL(10,2) NOT NULL,
            國字金額    VARCHAR(100)  DEFAULT NULL,
            交易時間    DATETIME      NOT NULL,
            交易方式    VARCHAR(10)   NOT NULL,
            銀行別      VARCHAR(50)   DEFAULT NULL,
            行號        VARCHAR(10)   DEFAULT NULL,
            戶名        VARCHAR(50)   DEFAULT NULL,
            帳號        VARCHAR(50)   DEFAULT NULL,
            票號        VARCHAR(50)   DEFAULT NULL,
            到期日      DATE          DEFAULT NULL,
            簽收日      DATE          DEFAULT NULL,
			實支金額    DECIMAL(10,2) DEFAULT NULL,
			結餘        DECIMAL(10,2) DEFAULT NULL,
			審核狀態    VARCHAR(50)   DEFAULT NULL,
            PRIMARY KEY (交易單號),
            CONSTRAINT fk_交易檔_受款人
              FOREIGN KEY (受款人代號)
              REFERENCES 受款人資料檔 (受款人代號)
              ON UPDATE CASCADE
              ON DELETE RESTRICT,
            CONSTRAINT fk_交易檔_業務
              FOREIGN KEY (業務代號)
              REFERENCES 經辦業務檔 (業務代號)
              ON UPDATE CASCADE
              ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");  
} catch (PDOException $e) {     
    die('資料庫或資料表建立失敗: ' . $e->getMessage()); 
}

/**
 * 產生交易單號：格式 = A + 民國年 + 月 + 5碼流水號
 * 例如：A11203xxxxx
 */
function generateTransactionID(PDO $pdo) {
    $now = new DateTime();
    $rocYear = $now->format('Y') - 1911;
    $month   = $now->format('m');
    $prefix  = "A{$rocYear}{$month}";
    $stmt = $pdo->prepare("
        SELECT MAX(交易單號) AS max_id 
        FROM 經辦人交易檔 
        WHERE 交易單號 LIKE ?
    ");
    $stmt->execute(["{$prefix}%"]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['max_id']) {
        $lastSerial = (int)substr($row['max_id'], strlen($prefix));
        $newSerial  = $lastSerial + 1;
    } else {
        $newSerial  = 1;
    }
    return $prefix . str_pad($newSerial, 5, '0', STR_PAD_LEFT);
}




/**
 * 產生業務代號：依支出項目決定前綴 (W, X, Y, Z)
 * 例如：W活動費用, X獎學金, Y經濟扶助, Z其他
 */
function generateBusinessCode(PDO $pdo, string $expenseItem) {
    // 根據整個字串，取第一個英文字母
    switch ($expenseItem) {
        case 'W活動費用':
            $prefix = 'W';
            break;
        case 'X獎學金':
            $prefix = 'X';
            break;
        case 'Y經濟扶助':
            $prefix = 'Y';
            break;
        case 'Z其他':
            $prefix = 'Z';
            break;
        default:
            // 萬一抓不到，預設 Z
            $prefix = 'Z';
            break;
    }
    $stmt = $pdo->prepare("
        SELECT MAX(業務代號) AS max_code 
        FROM 經辦業務檔 
        WHERE 業務代號 LIKE CONCAT(?, '%')
    ");
    $stmt->execute([$prefix]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['max_code']) {
        $lastNum = (int)substr($row['max_code'], 1); // 去除第一碼後剩餘的數字
        $newNum  = $lastNum + 1;
    } else {
        $newNum  = 1;
    }
    return $prefix . str_pad($newNum, 3, '0', STR_PAD_LEFT);
}

// ------------------------------------------------------------------------
// 處理表單提交
// ------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 受款人代號 + 受款人姓名
    $recipientCode = $_POST['受款人']     ?? '';  // Rxx
    $recipientName = $_POST['受款人姓名'] ?? '';  // 使用者自行輸入

    // 其他欄位
    $expenseItem   = $_POST['支出項目'] ?? '';    // W活動費用 / X獎學金 / Y經濟扶助 / Z其他
    $businessName  = $_POST['填表人']   ?? '';    // 經辦代號(舊「業務名稱」)
    $desc          = $_POST['說明']     ?? '';
	$電話號碼          = $_POST['電話號碼']     ?? '';
	$地址          = $_POST['地址']     ?? '';
    // 填表日期 (DATETIME)
    $fillDateStr = $_POST['填表日期'] ?? '';
    $fillDate    = $fillDateStr ? ($fillDateStr . ' ' . date('H:i:s')) : date('Y-m-d H:i:s');

    // 付款日期 (DATE)
    $payDateStr = $_POST['付款日期'] ?? '';
    $payDate    = $payDateStr ?: date('Y-m-d');

    // 金額與支付方式
    $amtNum     = isset($_POST['國字金額']) ? floatval($_POST['國字金額']) : 0;
    $amtChinese = $_POST['國字金額_hidden'] ?? '';
    $payMethod  = $_POST['支付方式']       ?? '';

    // 其他專案 / 活動 / 獎學金 / 扶助 / 其他
    $projectType  = $_POST['專案活動']    ?? '';
    $activityName = $_POST['活動名稱']    ?? '';
    $activityDate = $_POST['專案日期']    ?? '';
    $scholarCount = $_POST['獎學金人數']  ?? '';
    $scholarProj  = $_POST['專案名稱']    ?? '';
    $scholarTopic = $_POST['主題']        ?? '';
    $scholarDate  = $_POST['獎學金日期']  ?? '';
    $helpType     = $_POST['經濟扶助']    ?? '';
    $othersArr    = $_POST['其他項目']    ?? [];
    $othersStr    = is_array($othersArr) ? implode(',', $othersArr) : '';

    // 現金 / 轉帳 / 匯款 / 劃撥 / 支票
    $signDate   = $_POST['簽收日']     ?? '';
    $bank       = $_POST['銀行郵局']   ?? '';
    $branch     = $_POST['分行']       ?? '';
    $acctName   = $_POST['戶名']       ?? '';
    $acctNumber = $_POST['帳號']       ?? '';
    $checkNo    = $_POST['票號']       ?? '';
    $dueDate    = $_POST['到期日']     ?? '';

    // 防呆檢查
    if (!$recipientCode) {
        die("[錯誤] 受款人代號 不可為空");
    }
    if (!$recipientName) {
        die("[錯誤] 受款人姓名 不可為空");
    }
    if (!$expenseItem || !$businessName) {
        die("[錯誤] 支出項目 / 填表人 不可為空");
    }
    if ($amtNum <= 0 || !$payMethod) {
        die("[錯誤] 金額必須大於 0，且支付方式不可為空");
    }

    // 產生業務代號與交易單號
    $businessCode = generateBusinessCode($pdo, $expenseItem);
    $trxID        = generateTransactionID($pdo);



// 根據交易單號決定審核狀態
$reviewStatus = str_starts_with($trxID, 'A') 
    ? '預支審核中' 
    : (str_starts_with($trxID, 'B') 
        ? '報帳審核中' 
        : '未設定'); // 如果不是A或B開頭，當作未設定




    try {
        $pdo->beginTransaction();

        // 1) 若受款人代號不存在，新增到 受款人資料檔
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM 受款人資料檔 WHERE 受款人代號 = ?");
        $stmtCheck->execute([$recipientCode]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmt1 = $pdo->prepare("
                INSERT INTO 受款人資料檔
                (受款人代號, 受款人姓名, 手機號碼, 地址)
                VALUES (?, ?, ?, ?)
            ");
            $stmt1->execute([
                $recipientCode,
                $recipientName,  // 使用者輸入的姓名
				$電話號碼,
				$地址,
            ]);
        }

        // 2) 新增 經辦業務檔
        $stmt2 = $pdo->prepare("
            INSERT INTO 經辦業務檔
            (
                業務代號, 受款人代號, 填表日期, 付款日期,
                支出項目, 經辦代號, 說明,
                專案活動, 活動名稱, 專案日期,
                獎學金人數, 獎學金專案, 主題, 獎學金日期,
                經濟扶助, 其他項目
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt2->execute([
            $businessCode,
            $recipientCode,
            $fillDate,
            $payDate,
            $expenseItem,   // 例如 "W活動費用" / "X獎學金" / ...
            $businessName,  // 將「填表人」存入「經辦代號」欄位
            $desc ?: null,
            $projectType    ?: null,
            $activityName   ?: null,
            !empty($activityDate)  ? $activityDate : null,
            !empty($scholarCount) ? intval($scholarCount) : null,
            $scholarProj    ?: null,
            $scholarTopic   ?: null,
            !empty($scholarDate) ? $scholarDate : null,
            $helpType       ?: null,
            $othersStr      ?: null
        ]);

        // 3) 新增 經辦人交易檔
        $stmt3 = $pdo->prepare("
    INSERT INTO 經辦人交易檔
    (
        交易單號, 受款人代號, 業務代號, 金額, 國字金額, 交易時間, 交易方式,
        銀行別, 行號, 戶名, 帳號, 票號, 到期日, 簽收日,
        實支金額, 結餘, 審核狀態
    )
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");
        $stmt3->execute([
    $trxID,
    $recipientCode,
    $businessCode,
    $amtNum,
    $amtChinese ?: null,
    date('Y-m-d H:i:s'),
    $payMethod,
    $bank       ?: null,
    $branch     ?: null,
    $acctName   ?: null,
    $acctNumber ?: null,
    $checkNo    ?: null,
    !empty($dueDate) ? $dueDate : null,
    !empty($signDate) ? $signDate : null,
    null,  // 實支金額
    null,  // 結餘
    $reviewStatus
]);

        $pdo->commit();
        echo "表單提交成功 | 受款人代號: {$recipientCode}, 業務代號: {$businessCode}, 交易單號: {$trxID}";
        exit;
    } catch (Exception $ex) {
        $pdo->rollBack();
        die("[錯誤] 資料寫入失敗: " . $ex->getMessage());
    }
} else {
    die("請使用 POST 方式提交表單。");
}
?>

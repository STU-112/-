<?php 
error_reporting(E_ALL);  
ini_set('display_errors', 1);  
date_default_timezone_set('Asia/Taipei');  

$host     = 'localhost:3307';   
$dbname   = '基金會';             
$username = 'root';             
$password = ' ';                 

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
            業務代號     VARCHAR(10)   NOT NULL,
            受款人代號   VARCHAR(10)   NOT NULL,
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
        CONSTRAINT fk_受款人 FOREIGN KEY (受款人代號)
            REFERENCES 受款人資料檔 (受款人代號)
            ON UPDATE CASCADE ON DELETE RESTRICT,
        CONSTRAINT fk_經辦人 FOREIGN KEY (經辦代號)
            REFERENCES 註冊資料表 (員工編號)
            ON UPDATE CASCADE ON DELETE RESTRICT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");
    
    // 3) 經辦人交易檔     
    $pdo->exec("         
        CREATE TABLE IF NOT EXISTS 經辦人交易檔 (
            交易單號    VARCHAR(30)   NOT NULL,
            受款人代號  VARCHAR(10)    NOT NULL,
            業務代號    VARCHAR(10)    NOT NULL,
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
 * 產生交易單號 (B+民國年+月+流水號)，例如：B11206xxxxx
 */
function generateTransactionID(PDO $pdo) {
    $now = new DateTime();
    $rocYear = $now->format('Y') - 1911;
    $month   = $now->format('m');
	$date    = $now->format('d');  // 補上這一行
    $prefix  = "B{$rocYear}{$month}{$date}";
    $stmt = $pdo->prepare("SELECT MAX(交易單號) AS max_id FROM 經辦人交易檔 WHERE 交易單號 LIKE ?");
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
 * 根據支出項目產生業務代號 (W, X, Y, Z)，流水號部分以 3 位數表示
 */
function generateBusinessCode(PDO $pdo, string $expenseItem) {
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
            $prefix = 'Z';
            break;
    }
    $stmt = $pdo->prepare("SELECT MAX(業務代號) AS max_code FROM 經辦業務檔 WHERE 業務代號 LIKE CONCAT(?, '%')");
    $stmt->execute([$prefix]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row && $row['max_code']) {
        $lastNum = (int)substr($row['max_code'], 1);
        $newNum  = $lastNum + 1;
    } else {
        $newNum  = 1;
    }
    return $prefix . str_pad($newNum, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 接收前端欄位
    $recipientCode       = $_POST['受款人'] ?? '';
    $recipientName       = $_POST['受款人姓名'] ?? '';
    $expenseItem = $_POST['支出項目'] ?? '';
    $desc                = $_POST['說明'] ?? '';
    $fillDateStr         = $_POST['填表日期'] ?? '';
    $fillDate            = !empty($fillDateStr) ? $fillDateStr . ' ' . date('H:i:s') : date('Y-m-d H:i:s');

    $payDateStr = $_POST['付款日期'] ?? '';
    $payDate    = !empty($payDateStr) ? $payDateStr : date('Y-m-d');

    // 從前端取得金額 (使用者可能輸入 33333)
    // 先去除逗號（若有）並轉成數字
    $amtRaw     = isset($_POST['國字金額']) ? str_replace(',', '', $_POST['國字金額']) : '0';
    $amtNum     = floatval($amtRaw);
    // 以 number_format() 將數值轉為帶千分位字串 (保留兩位小數)
    $amtFormatted = number_format($amtNum, 2, '.', ',');
    // 其餘中文金額由前端傳入
    $amtChinese = $_POST['國字金額_hidden'] ?? '';

    $payMethod  = $_POST['支付方式'] ?? '';
    $單據張數   = isset($_POST['單據張數']) ? intval($_POST['單據張數']) : 0;

    $projectType  = $_POST['專案活動'] ?? '';
    $activityName = $_POST['活動名稱'] ?? '';
    $activityDate = $_POST['專案日期'] ?? '';
    $scholarCount = $_POST['獎學金人數'] ?? '';
    $scholarProj  = $_POST['專案名稱'] ?? '';
    $scholarTopic = $_POST['主題'] ?? '';
    $scholarDate  = $_POST['獎學金日期'] ?? '';
    $helpType     = $_POST['經濟扶助'] ?? '';
    $othersArr    = $_POST['其他項目'] ?? [];
    $othersStr    = is_array($othersArr) ? implode(',', $othersArr) : '';

    $signDate   = $_POST['簽收日'] ?? '';
    $bank       = $_POST['銀行郵局'] ?? '';
    $branch     = $_POST['分行'] ?? '';
    $acctName   = $_POST['戶名'] ?? '';
    $acctNumber = $_POST['帳號'] ?? '';
    $checkNo    = $_POST['票號'] ?? '';
    $dueDate    = $_POST['到期日'] ?? '';
    $電話號碼      = $_POST['電話號碼']   ?? '';
	$地址          = $_POST['地址']       ?? '';

    $businessName  = $_POST['填表人'] ?? '';

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
            : '未設定');




    // 多檔上傳：圖片
    $imagePaths = [];
    if (isset($_FILES['image_files'])) {
        foreach ($_FILES['image_files']['error'] as $key => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['image_files']['tmp_name'][$key];
                $rawName = $_FILES['image_files']['name'][$key];
                if (!empty($rawName)) {
                    $safeName = str_replace('/', '_', $rawName);
                    $imageFolder = 'uploads/images/';
                    if (!is_dir($imageFolder)) mkdir($imageFolder, 0777, true);
                    $baseName = $trxID . '_' . $safeName;
                    $targetPath = $imageFolder . $baseName;
                    $i = 1;
                    while(file_exists($targetPath)){
                        $targetPath = $imageFolder . $trxID . '_' . $i . '_' . $safeName;
                        $i++;
                    }
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $imagePaths[] = $targetPath;
                    }
                }
            }
        }
    }
    $imagePath = count($imagePaths) ? implode(',', $imagePaths) : '--';

    // 多檔上傳：CSV
    $csvPaths = [];
    if (isset($_FILES['csv_files'])) {
        foreach ($_FILES['csv_files']['error'] as $key => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['csv_files']['tmp_name'][$key];
                $rawName = $_FILES['csv_files']['name'][$key];
                if (!empty($rawName)) {
                    $safeName = str_replace('/', '_', $rawName);
                    $csvFolder = 'uploads/csv/';
                    if (!is_dir($csvFolder)) mkdir($csvFolder, 0777, true);
                    $baseName = $trxID . '_' . $safeName;
                    $targetCsvPath = $csvFolder . $baseName;
                    $i = 1;
                    while(file_exists($targetCsvPath)){
                        $targetCsvPath = $csvFolder . $trxID . '_' . $i . '_' . $safeName;
                        $i++;
                    }
                    if (move_uploaded_file($tmpName, $targetCsvPath)) {
                        $csvPaths[] = $targetCsvPath;
                    }
                }
            }
        }
    }
    $csvPath = count($csvPaths) ? implode(',', $csvPaths) : '--';

    // 若未上傳任何檔案，則單據張數強制為 0
    if ($imagePath === '--' && $csvPath === '--') {
        $單據張數 = 0;
    }

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
                $recipientName,
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
            $expenseItem,
            $businessName,
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

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

// 將單檔上傳上限、整個 POST 資料上限、執行時間與記憶體限制都設定為 1024M (1GB) 或 600 秒
ini_set('upload_max_filesize', '1024M');
ini_set('post_max_size', '1024M');
ini_set('max_execution_time', '600');
ini_set('memory_limit', '1024M');

$host     = 'localhost:3307';
$dbname   = '基金會';  // 若是數字開頭名稱需用反引號
$username = 'root';
$password = '3307';      // 若有密碼請填入

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 建立資料庫（若不存在）
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `$dbname`
        DEFAULT CHARACTER SET utf8
        COLLATE utf8_general_ci;
    ");
    $pdo->exec("USE `$dbname`;");

    // 1) 受款人資料檔
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS 受款人資料檔 (
            受款人代號 VARCHAR(10) NOT NULL,
            受款人姓名 VARCHAR(50) DEFAULT NULL,
            手機號碼   VARCHAR(20) DEFAULT NULL,
            地址       VARCHAR(100) DEFAULT NULL,
            PRIMARY KEY (受款人代號)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    // 2) 經辦業務檔 (填表日期: DATETIME、付款日期: DATE)
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS 經辦業務檔 (
            業務代號      VARCHAR(10)  NOT NULL,
            受款人代號    VARCHAR(10)  NOT NULL,
            填表日期      DATETIME    NOT NULL,
            付款日期      DATE        NOT NULL,
            支出項目      VARCHAR(50) NOT NULL,
            經辦代號      VARCHAR(50) NOT NULL,
            說明          TEXT        DEFAULT NULL,

            專案活動      VARCHAR(50)  DEFAULT NULL,
            活動名稱      VARCHAR(100) DEFAULT NULL,
            專案日期      DATE        DEFAULT NULL,
            獎學金人數    INT         DEFAULT NULL,
            獎學金專案    VARCHAR(100) DEFAULT NULL,
            主題          VARCHAR(100) DEFAULT NULL,
            獎學金日期    DATE        DEFAULT NULL,
            經濟扶助      VARCHAR(50)  DEFAULT NULL,
            其他項目      TEXT         DEFAULT NULL,

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
            交易單號    VARCHAR(30)  NOT NULL,
            受款人代號  VARCHAR(10)   NOT NULL,
            業務代號    VARCHAR(10)   NOT NULL,
            金額        DECIMAL(10,2) NOT NULL,
            國字金額    VARCHAR(100) DEFAULT NULL,
            交易時間    DATETIME    NOT NULL,
            交易方式    VARCHAR(10)  NOT NULL,

            銀行別      VARCHAR(50)  DEFAULT NULL,
            行號        VARCHAR(10)  DEFAULT NULL,
            戶名        VARCHAR(50)  DEFAULT NULL,
            帳號        VARCHAR(50)  DEFAULT NULL,
            票號        VARCHAR(50)  DEFAULT NULL,
            簽收日 		DATE         DEFAULT NULL,
            到期日      DATE         DEFAULT NULL,
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

    // 4) uploads 資料表（保持原來兩格欄位）
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS uploads (
            交易單號     VARCHAR(30) NOT NULL,
            image_path   TEXT NOT NULL,
            csv_path     TEXT NOT NULL,
            upload_timestamp TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            單據張數     INT NOT NULL,
            PRIMARY KEY (交易單號),
            FOREIGN KEY (交易單號) REFERENCES 經辦人交易檔(交易單號)
              ON DELETE CASCADE
              ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

} catch (PDOException $e) {
    die('資料庫或資料表建立失敗: ' . $e->getMessage());
}








// 原始交易單號（未加 C）
$originalTrxID = $_POST['交易單號'] ?? '';

// 產生新的交易單號：原單號 + C + 三碼流水號
function generateCodeWithSerial(PDO $pdo, $table, $column, $baseID) {
    $likePattern = $baseID . 'C%';
    $sql = "SELECT MAX($column) FROM `$table` WHERE $column LIKE ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$likePattern]);
    $maxID = $stmt->fetchColumn();

    if ($maxID) {
        $lastSerial = (int)substr($maxID, strlen($baseID) + 1);
        $newSerial = $lastSerial + 1;
    } else {
        $newSerial = 1;
    }

    return $baseID . 'C' . str_pad($newSerial, 3, '0', STR_PAD_LEFT);
}


$trxID = generateCodeWithSerial($pdo, '經辦人交易檔', '交易單號', $originalTrxID);


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $formType = $_POST['選擇表單'] ?? '';
    if ($formType === '支出核銷') {
        echo "您選擇了支出核銷(單號查詢)，尚未實作。";
        exit;
    }

    // --- 表單資料接收 ---
	$originalTrxID = $_POST['交易單號'] ?? '';  // 原單號
	$updateStmt = $pdo->prepare("UPDATE 經辦人交易檔 SET 審核狀態 = ? WHERE 交易單號 = ?");
	$updateStmt->execute(['核銷審核中', $originalTrxID]);
$trxID = generateCodeWithSerial($pdo, '經辦人交易檔', '交易單號', $originalTrxID);


$baseRecipient = $_POST['受款人代號'] ?? 'R001'; // 假設原始受款人代號開頭是 R001
$recipientCode = generateCodeWithSerial($pdo, '受款人資料檔', '受款人代號', $baseRecipient);

$baseBusiness = $_POST['業務代號'] ?? 'Y001'; // 假設原始業務代號開頭是 Y001
$businessCode = generateCodeWithSerial($pdo, '經辦業務檔', '業務代號', $baseBusiness);

     //受款人代號+C
    // $recipientCode = $_POST['受款人代號'] ?? '';
	// $recipientCode = $recipientCode . "C";
    // $recipientCode = generateSubTransactionID($pdo, $originalTrxID); // 自動產生 C + 3碼流水號
			
			
    // $businessCode  = $_POST['業務代號'] ?? '';
	// $businessCode = $businessCode . "C";    
    // $businessCode = generateSubTransactionID($pdo, $originalTrxID); // 自動產生 C + 3碼流水號
			
			
			
	$expenseItem   = $_POST['支出項目'] ?? '';
    $businessName  = $_POST['經辦代號'] ?? '';
    $amtNum        = isset($_POST['金額']) ? floatval($_POST['金額']) : 0;
    $amtChinese    = $_POST['國字金額_hidden'] ?? '';
    $payMethod     = $_POST['支付方式'] ?? '';
    $signDate      = $_POST['簽收日'] ?? '';
    $bank          = $_POST['銀行郵局'] ?? '';
    $branch        = $_POST['分行'] ?? '';
    $acctName      = $_POST['戶名'] ?? '';
    $acctNumber    = $_POST['帳號'] ?? '';
    $checkNo       = $_POST['票號'] ?? '';
    $dueDate       = $_POST['到期日'] ?? '';
    $實支金額      = $_POST['實支金額'] ?? null;
    $結餘          = $_POST['結餘'] ?? null;
    $單據張數      = isset($_POST['單據張數']) ? intval($_POST['單據張數']) : 0;
    $reviewStatus  = '核銷審核中'; // 預設審核狀態

    // --- 多檔圖片上傳處理 ---
    $imagePaths = [];
    if (isset($_FILES['image_files'])) {
        foreach ($_FILES['image_files']['error'] as $key => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['image_files']['tmp_name'][$key];
                $rawName = $_FILES['image_files']['name'][$key];
                if (!empty($rawName)) {
                    $safeName = str_replace('/', '_', $rawName);
                    $folder = 'uploads/images/';
                    if (!is_dir($folder)) mkdir($folder, 0777, true);
                    $uniqueName = uniqid() . '_' . $safeName;
                    $targetPath = $folder . $uniqueName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $imagePaths[] = $targetPath;
                    }
                }
            }
        }
    }
    $imagePath = count($imagePaths) ? implode(',', $imagePaths) : '--';

    // --- 多檔 CSV/PDF/WORD/EXCEL 上傳 ---
    $csvPaths = [];
    if (isset($_FILES['csv_files'])) {
        foreach ($_FILES['csv_files']['error'] as $key => $error) {
            if ($error === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['csv_files']['tmp_name'][$key];
                $rawName = $_FILES['csv_files']['name'][$key];
                if (!empty($rawName)) {
                    $safeName = str_replace('/', '_', $rawName);
                    $folder = 'uploads/csv/';
                    if (!is_dir($folder)) mkdir($folder, 0777, true);
                    $uniqueName = uniqid() . '_' . $safeName;
                    $targetPath = $folder . $uniqueName;
                    if (move_uploaded_file($tmpName, $targetPath)) {
                        $csvPaths[] = $targetPath;
                    }
                }
            }
        }
    }
    $csvPath = count($csvPaths) ? implode(',', $csvPaths) : '--';
	
    // --- 開始資料庫交易 ---
    try {
        $pdo->beginTransaction();
		
		
		
		
		// 7.1 若受款人代號不存在，先新增 (此處示範簡單寫法)
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM 受款人資料檔 WHERE 受款人代號 = ?");
        $stmtCheck->execute([$recipientCode]);
        if ($stmtCheck->fetchColumn() == 0) {
            $stmt1 = $pdo->prepare("
                INSERT INTO 受款人資料檔
                (受款人代號, 受款人姓名, 手機號碼, 地址)
                VALUES (?, ?, ?, ?)
            ");
            // 這裡示範：先用 受款人代號 當作姓名、手機與地址先填假資料
            $stmt1->execute([
			$recipientCode, 
			'未填寫',
			'未填寫',
			'未填寫' ]);
        }
		
		//受款人資料表 新增核銷受款人代碼
		// $stmt3 = $pdo->prepare("INSERT INT 受款人資料檔(受款人代號)VALUES (?)");
		

// 7.2 新增 經辦業務檔
        $stmt2 = $pdo->prepare("
            INSERT INTO 經辦業務檔
            (
              業務代號, 受款人代號, 填表日期, 付款日期,支出項目,
              經辦代號, 說明,專案活動, 活動名稱, 專案日期,
              獎學金人數, 獎學金專案, 主題, 獎學金日期,經濟扶助,
              其他項目
            )
            VALUES (?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?,
                    ?, ?, ?, ?, ?, ?)
        ");
		
$fillDate = date('Y-m-d H:i:s');
$payDate  = date('Y-m-d');

$stmt2->execute([
    $businessCode,     // 業務代號
    $recipientCode,    // 受款人代號
    $fillDate,         // 填表日期
    $payDate,          // 付款日期
    $expenseItem,      // 支出項目
    $businessName,     // 經辦代號
    null,              // 說明
    null,              // 專案活動
    null,              // 活動名稱
    null,              // 專案日期
    null,              // 獎學金人數
    null,              // 獎學金專案
    null,              // 主題
    null,              // 獎學金日期
    null,              // 經濟扶助
    null               // 其他項目
]);

        // ✅ 寫入 經辦人交易檔
        $stmt3 = $pdo->prepare("
            INSERT INTO 經辦人交易檔
            (交易單號, 受款人代號, 業務代號, 金額, 國字金額, 交易時間, 交易方式,
             銀行別, 行號, 戶名, 帳號, 票號, 到期日, 簽收日,
             實支金額, 結餘, 審核狀態)
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
            $bank ?: null,
            $branch ?: null,
            $acctName ?: null,
            $acctNumber ?: null,
            $checkNo ?: null,
            !empty($dueDate) ? $dueDate : null,
            !empty($signDate) ? $signDate : null,
            $實支金額 ?: null,
            $結餘 ?: null,
            $reviewStatus
        ]);
		
		
// ✅ 將原單號（未加C的）更新為「核銷審核中」
// $originalTrxID = rtrim($trxID, "C"); // 拿掉最後一個C，還原原單號
// $updateStmt = $pdo->prepare("UPDATE 經辦人交易檔 SET 審核狀態 = ? WHERE 交易單號 = ?");
// $updateStmt->execute(['核銷審核中', $originalTrxID]);





        // ✅ 寫入 uploads 多檔資料
        $stmt4 = $pdo->prepare("
            INSERT INTO uploads
            (交易單號, image_path, csv_path, 單據張數)
            VALUES (?, ?, ?, ?)
        ");
        $stmt4->execute([
            $trxID,
            $imagePath,
            $csvPath,
            $單據張數
        ]);

        $pdo->commit();
        echo "表單提交成功 | 受款人代號: {$recipientCode} | 交易單號: {$trxID}";
        exit;

    } catch (Exception $ex) {
        $pdo->rollBack();
        die("[錯誤] 資料寫入失敗: " . $ex->getMessage());
    }

} else {
    die("請使用POST方式提交表單。");
}
?>

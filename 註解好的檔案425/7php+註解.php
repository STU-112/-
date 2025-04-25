<?php
// 顯示所有錯誤訊息，程式出錯時才能立刻看到
error_reporting(E_ALL);
ini_set('display_errors', 1);

// 把時間設定成台北時間
date_default_timezone_set('Asia/Taipei');

/* ── 資料庫連線設定 ── */
$host     = 'localhost:3307';   // 資料庫主機位置
$dbname   = '基金會';            // 資料庫名稱
$username = 'root';             // 登入帳號
$password = '3307';             // 登入密碼

try {
    // 建立與 MySQL 的連線，並設定編碼為 UTF-8
    $pdo = new PDO("mysql:host=$host;charset=utf8", $username, $password);
    // 把錯誤處理方式設成「發生錯誤就丟出例外」
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 如果指定的資料庫不存在，就建立它
    $pdo->exec("
        CREATE DATABASE IF NOT EXISTS `$dbname`
        DEFAULT CHARACTER SET utf8
        COLLATE utf8_general_ci;
    ");

    // 切換到剛建立或已存在的資料庫
    $pdo->exec("USE `$dbname`;");

    /* ── 建立三張資料表 ── */

    // 受款人資料表，存放收款人的基本資料
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS 受款人資料檔 (
            受款人代號 VARCHAR(5)  NOT NULL,
            受款人姓名 VARCHAR(50) NOT NULL,
            手機號碼   VARCHAR(20) NOT NULL,
            地址       VARCHAR(100) NOT NULL,
            PRIMARY KEY (受款人代號)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    // 經辦業務資料表，記錄每筆業務申請的主要資訊
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS 經辦業務檔 (
            業務代號     VARCHAR(10) NOT NULL,
            受款人代號   VARCHAR(10) NOT NULL,
            填表日期     DATETIME    NOT NULL,
            付款日期     DATE        NOT NULL,
            支出項目     VARCHAR(50) NOT NULL,
            經辦代號     VARCHAR(50) NOT NULL,
            說明         TEXT        DEFAULT NULL,
            專案活動     VARCHAR(50) DEFAULT NULL,
            活動名稱     VARCHAR(100) DEFAULT NULL,
            專案日期     DATE        DEFAULT NULL,
            獎學金人數   INT         DEFAULT NULL,
            獎學金專案   VARCHAR(100) DEFAULT NULL,
            主題         VARCHAR(100) DEFAULT NULL,
            獎學金日期   DATE        DEFAULT NULL,
            經濟扶助     VARCHAR(50)  DEFAULT NULL,
            其他項目     TEXT         DEFAULT NULL,
            PRIMARY KEY (業務代號),
            FOREIGN KEY (受款人代號) REFERENCES 受款人資料檔 (受款人代號)
              ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    // 經辦人交易資料表，記錄實際的金流與付款細節
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS 經辦人交易檔 (
            交易單號    VARCHAR(30)   NOT NULL,
            受款人代號  VARCHAR(10)   NOT NULL,
            業務代號    VARCHAR(10)   NOT NULL,
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
            FOREIGN KEY (受款人代號) REFERENCES 受款人資料檔 (受款人代號)
              ON UPDATE CASCADE ON DELETE RESTRICT,
            FOREIGN KEY (業務代號)   REFERENCES 經辦業務檔 (業務代號)
              ON UPDATE CASCADE ON DELETE RESTRICT
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");
} catch (PDOException $e) {
    // 如果建立資料庫或資料表失敗，就停止並顯示原因
    die('建立資料庫或資料表失敗：' . $e->getMessage());
}

/* ------------ 產生交易單號 ------------ 
   組成方式：A + 民國年 + 月 + 日 + 5 位流水號
---------------------------------------- */
function generateTransactionID(PDO $pdo) {
    $now     = new DateTime();               // 取得現在時間
    $rocYear = $now->format('Y') - 1911;     // 轉成民國年
    $month   = $now->format('m');            // 取月份
    $date    = $now->format('d');            // 取日期
    $prefix  = "A{$rocYear}{$month}{$date}"; // 前綴

    // 找出今天目前最大號碼
    $stmt = $pdo->prepare("
        SELECT MAX(交易單號) AS max_id
        FROM 經辦人交易檔
        WHERE 交易單號 LIKE ?
    ");
    $stmt->execute(["{$prefix}%"]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 決定新的流水號
    $newSerial = ($row && $row['max_id'])
        ? ((int)substr($row['max_id'], strlen($prefix)) + 1)
        : 1;

    // 回傳完整的交易單號
    return $prefix . str_pad($newSerial, 5, '0', STR_PAD_LEFT);
}

/* ------------ 產生業務代號 ------------
   前綴為 W/X/Y/Z，後面接 3 位流水號
---------------------------------------- */
function generateBusinessCode(PDO $pdo, string $expenseItem) {
    // 依支出項目決定前綴
    $prefix = match ($expenseItem) {
        'W活動費用' => 'W',
        'X獎學金'   => 'X',
        'Y經濟扶助' => 'Y',
        default     => 'Z',
    };

    // 找出目前最大的流水號
    $stmt = $pdo->prepare("
        SELECT MAX(業務代號) AS max_code
        FROM 經辦業務檔
        WHERE 業務代號 LIKE CONCAT(?, '%')
    ");
    $stmt->execute([$prefix]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    $newNum = ($row && $row['max_code'])
        ? ((int)substr($row['max_code'], 1) + 1)
        : 1;

    // 回傳完整的業務代號
    return $prefix . str_pad($newNum, 3, '0', STR_PAD_LEFT);
}

/* =========== 接收表單資料 =========== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 收集表單欄位，如果沒有就給空字串
    $recipientCode = $_POST['受款人']     ?? '';
    $recipientName = $_POST['受款人姓名'] ?? '';
    $expenseItem   = $_POST['支出項目']   ?? '';
    $businessName  = $_POST['填表人']     ?? '';
    $desc          = $_POST['說明']       ?? '';
    $phone         = $_POST['電話號碼']   ?? '';
    $address       = $_POST['地址']       ?? '';

    // 處理日期欄位
    $fillDateStr = $_POST['填表日期'] ?? '';
    $fillDate    = $fillDateStr ? ($fillDateStr . ' ' . date('H:i:s')) : date('Y-m-d H:i:s');
    $payDate     = $_POST['付款日期'] ?: date('Y-m-d');

    // 金額與支付方式
    $amtNum     = isset($_POST['國字金額']) ? floatval($_POST['國字金額']) : 0;
    $amtChinese = $_POST['國字金額_hidden'] ?? '';
    $payMethod  = $_POST['支付方式']         ?? '';

    // 其他欄位
    $projectType  = $_POST['專案活動']    ?? '';
    $activityName = $_POST['活動名稱']    ?? '';
    $activityDate = $_POST['專案日期']    ?? '';
    $scholarCount = $_POST['獎學金人數']  ?? '';
    $scholarProj  = $_POST['專案名稱']    ?? '';
    $scholarTopic = $_POST['主題']        ?? '';
    $scholarDate  = $_POST['獎學金日期']  ?? '';
    $helpType     = $_POST['經濟扶助']    ?? '';
    $othersStr    = isset($_POST['其他項目']) ? implode(',', (array)$_POST['其他項目']) : '';

    // 支付方式相關欄位
    $signDate   = $_POST['簽收日']     ?? '';
    $bank       = $_POST['銀行郵局']   ?? '';
    $branch     = $_POST['分行']       ?? '';
    $acctName   = $_POST['戶名']       ?? '';
    $acctNumber = $_POST['帳號']       ?? '';
    $checkNo    = $_POST['票號']       ?? '';
    $dueDate    = $_POST['到期日']     ?? '';

    // 基本欄位檢查
    if (!$recipientCode) die("受款人代號必填");
    if (!$recipientName) die("受款人姓名必填");
    if (!$expenseItem || !$businessName) die("支出項目與填表人必填");
    if ($amtNum <= 0 || !$payMethod) die("金額需大於 0，且支付方式必填");

    // 產生代號
    $businessCode = generateBusinessCode($pdo, $expenseItem);
    $trxID        = generateTransactionID($pdo);

    // 設定預設審核狀態
    $reviewStatus = str_starts_with($trxID, 'A') ? '預支審核中'
                  : (str_starts_with($trxID, 'B') ? '報帳審核中' : '未設定');

    // 寫入資料庫
    try {
        $pdo->beginTransaction();

        // 如果受款人不存在，就新增
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM 受款人資料檔 WHERE 受款人代號 = ?");
        $stmtCheck->execute([$recipientCode]);
        if (!$stmtCheck->fetchColumn()) {
            $pdo->prepare("
                INSERT INTO 受款人資料檔 (受款人代號, 受款人姓名, 手機號碼, 地址)
                VALUES (?, ?, ?, ?)
            ")->execute([$recipientCode, $recipientName, $phone, $address]);
        }

        // 新增經辦業務資料
        $pdo->prepare("
            INSERT INTO 經辦業務檔 (
                業務代號, 受款人代號, 填表日期, 付款日期,
                支出項目, 經辦代號, 說明,
                專案活動, 活動名稱, 專案日期,
                獎學金人數, 獎學金專案, 主題, 獎學金日期,
                經濟扶助, 其他項目
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")->execute([
            $businessCode,
            $recipientCode,
            $fillDate,
            $payDate,
            $expenseItem,
            $businessName,
            $desc ?: null,
            $projectType  ?: null,
            $activityName ?: null,
            $activityDate ?: null,
            $scholarCount ? intval($scholarCount) : null,
            $scholarProj  ?: null,
            $scholarTopic ?: null,
            $scholarDate  ?: null,
            $helpType     ?: null,
            $othersStr    ?: null
        ]);

        // 新增經辦人交易資料
        $pdo->prepare("
            INSERT INTO 經辦人交易檔 (
                交易單號, 受款人代號, 業務代號, 金額, 國字金額, 交易時間, 交易方式,
                銀行別, 行號, 戶名, 帳號, 票號, 到期日, 簽收日,
                實支金額, 結餘, 審核狀態
            )
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ")->execute([
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
            $dueDate    ?: null,
            $signDate   ?: null,
            null,
            null,
            $reviewStatus
        ]);

        $pdo->commit();
        echo "表單提交成功：受款人代號 $recipientCode, 業務代號 $businessCode, 交易單號 $trxID";
    } catch (Exception $ex) {
        $pdo->rollBack();
        die("資料寫入失敗：" . $ex->getMessage());
    }
} else {
    die("請用 POST 方式提交資料");
}
?>

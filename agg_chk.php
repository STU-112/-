<?php
// ✅ 啟用錯誤顯示，方便除錯
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// ✅ 連線參數
$server   = 'localhost:3307';
$用戶名   = 'root';
$密碼     = '3307';
$資料庫   = '預支77777777777';

// ✅ 建立連線
$連接 = mysqli_connect($server, $用戶名, $密碼);
if (!$連接) {
    die("❌ 連線失敗：" . mysqli_connect_error());
}

// ✅ 建立資料庫（若不存在）
$sql = "CREATE DATABASE IF NOT EXISTS `$資料庫`";
if (!mysqli_query($連接, $sql)) {
    die("❌ 建立資料庫失敗：" . mysqli_error($連接));
}
mysqli_select_db($連接, $資料庫);

/* ✅ 建立「申請人資料檔」 */
$create_申請人資料檔_sql = "
    CREATE TABLE IF NOT EXISTS 申請人資料檔 (
        申請人代號 VARCHAR(5) NOT NULL,
        申請人姓名 VARCHAR(50) NOT NULL,
        手機號碼   VARCHAR(20) NOT NULL,
        地址       VARCHAR(100) NOT NULL,
        PRIMARY KEY (申請人代號)
    ) ENGINE=InnoDB;
";
mysqli_query($連接, $create_申請人資料檔_sql);

/* ✅ 建立「經辦業務檔」 */
$create_經辦業務檔_sql = "
    CREATE TABLE IF NOT EXISTS 經辦業務檔 (
        業務代號   VARCHAR(15) NOT NULL,
        申請人姓名 VARCHAR(50) NOT NULL,
        填表日期   DATE NOT NULL,
        業務名稱   VARCHAR(50) NOT NULL,
        活動日期   DATE DEFAULT NULL,
        負責員工編號 VARCHAR(10) DEFAULT 'TP318',
        備註       TEXT DEFAULT NULL,
        說明       TEXT DEFAULT NULL,
        PRIMARY KEY (業務代號)
    ) ENGINE=InnoDB;
";
mysqli_query($連接, $create_經辦業務檔_sql);

/* ✅ 建立「經辦人交易檔」 */
$create_經辦人交易檔_sql = "
    CREATE TABLE IF NOT EXISTS 經辦人交易檔 (
        交易單號    INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        申請人代號  VARCHAR(5) NOT NULL,
        業務代號    VARCHAR(15) NOT NULL,
        金額        DECIMAL(10,2) NOT NULL,
        交易時間    DATETIME NOT NULL,
        交易方式    VARCHAR(10) NOT NULL,
        銀行別      VARCHAR(50) DEFAULT NULL,
        行號        VARCHAR(10) DEFAULT NULL,
        戶名        VARCHAR(50) DEFAULT NULL,
        帳號        VARCHAR(50) DEFAULT NULL,
        票號        VARCHAR(50) DEFAULT NULL,
        CONSTRAINT fk_申請者代號 FOREIGN KEY (申請人代號) REFERENCES 申請人資料檔 (申請人代號) ON DELETE CASCADE ON UPDATE CASCADE,
        CONSTRAINT fk_業務代號 FOREIGN KEY (業務代號) REFERENCES 經辦業務檔 (業務代號) ON DELETE CASCADE ON UPDATE CASCADE
    ) ENGINE=InnoDB;
";
mysqli_query($連接, $create_經辦人交易檔_sql);

/* ✅ 開始處理 POST 資料 */
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $受款人 = $_POST['受款人'] ?? '';
    $受款人編號 = $_POST['受款人編號'] ?? '';
    $填表日期 = $_POST['填表日期'] ?? '';
    $支出項目 = $_POST['支出項目'] ?? '';
    $說明 = $_POST['說明'] ?? '';

    /* ✅ 產生「業務代號」 */
    $代號前綴 = match ($支出項目) {
		'獎學金' => 'X',
        '活動費用' => 'W',
        '經濟扶助' => 'Y',
        '其他' => 'Z',
    };

    $今天日期 = date('Ymd');
    $查詢最新編號 = "SELECT MAX(SUBSTRING(業務代號, 10, 4)) AS 最新流水號 FROM 經辦業務檔 WHERE 業務代號 LIKE '$代號前綴$今天日期%'";
    $結果 = mysqli_query($連接, $查詢最新編號);
    $最新流水號 = ($結果 && mysqli_num_rows($結果) > 0) ? intval(mysqli_fetch_assoc($結果)['最新流水號']) + 1 : 1;
    $業務代號 = sprintf("%s%s%04d", $代號前綴, $今天日期, $最新流水號);

    /* ✅ 1. 插入「申請人資料檔」 */
    if (!empty($受款人) && !empty($受款人編號)) {
        $stmt1 = mysqli_prepare($連接, "INSERT IGNORE INTO 申請人資料檔 (申請人代號, 申請人姓名, 手機號碼, 地址) VALUES (?, ?, '', '')");
        mysqli_stmt_bind_param($stmt1, "ss", $受款人編號, $受款人);
        mysqli_stmt_execute($stmt1);
        mysqli_stmt_close($stmt1);
    }

    /* ✅ 2. 插入「經辦業務檔」 */
    $活動日期 = $_POST['專案日期'] ?? ($_POST['獎學金日期'] ?? NULL);
    $備註 = ($_POST['獎學金人數'] ?? '') . ' ' . ($_POST['主題'] ?? '');

    $stmt2 = mysqli_prepare($連接, "INSERT INTO 經辦業務檔 (業務代號, 申請人姓名, 填表日期, 業務名稱, 活動日期, 備註, 說明) VALUES (?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt2, "sssssss", $業務代號, $受款人, $填表日期, $支出項目, $活動日期, $備註, $說明);
    mysqli_stmt_execute($stmt2);
    mysqli_stmt_close($stmt2);

    /* ✅ 3. 插入「經辦人交易檔」 */
    $金額 = ($_POST['支付方式'] === '支票') ? $_POST['預支金額'] : $_POST['國字金額'];
    $交易時間 = ($_POST['支付方式'] === '現金') ? $_POST['簽收日'] : (($_POST['支付方式'] === '支票') ? $_POST['到期日'] : date('Y-m-d H:i:s'));
    $銀行別 = $_POST['銀行郵局'] ?? '';
    $行號 = $_POST['transferBankBranch'] ?? '';
    $戶名 = $_POST['transferAccountName'] ?? '';
    $帳號 = $_POST['transferAccountNumber'] ?? '';
    $票號 = $_POST['票號'] ?? '';

    $stmt3 = mysqli_prepare($連接, "INSERT INTO 經辦人交易檔 (申請人代號, 業務代號, 金額, 交易時間, 交易方式, 銀行別, 行號, 戶名, 帳號, 票號) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt3, "ssdsssssss", $受款人編號, $業務代號, $金額, $交易時間, $_POST['支付方式'], $銀行別, $行號, $戶名, $帳號, $票號);
    mysqli_stmt_execute($stmt3);
    mysqli_stmt_close($stmt3);

    echo "✅ 資料成功寫入資料庫！";
}

mysqli_close($連接);
?>
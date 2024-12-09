<?php
// 設定資料庫連接資訊
$server = 'localhost:3307'; // 伺服器名稱
$用戶名 = 'root';          // 用戶名
$密碼 = ' ';               // 密碼 (設為空字串)
$資料庫 = '預支';          // 資料庫名稱

// 連接到 MySQL
$連接 = mysqli_connect($server, $用戶名, $密碼);

// 檢查連接
if (!$連接) {
    die("連接失敗: " . mysqli_connect_error());
}

// 創建資料庫（如果不存在）
$sql = "CREATE DATABASE IF NOT EXISTS `$資料庫` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($連接, $sql)) {
    echo "資料庫 `$資料庫` 已存在或創建成功。<br>";
} else {
    die("創建資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 選擇資料庫
if (!mysqli_select_db($連接, $資料庫)) {
    die("選擇資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 設定 SQL 模式以避免外鍵創建問題
mysqli_query($連接, "SET FOREIGN_KEY_CHECKS = 0");

// 創建所有相關資料表
// 1. 受款人表
$create_payees_sql = "CREATE TABLE IF NOT EXISTS `受款人` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `名稱` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_payees_sql)) {
    echo "資料表 `受款人` 創建成功或已存在。<br>";
} else {
    die("創建 `受款人` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 2. 支出項目表
$create_expenditure_items_sql = "CREATE TABLE IF NOT EXISTS `支出項目` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `名稱` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_expenditure_items_sql)) {
    echo "資料表 `支出項目` 創建成功或已存在。<br>";
} else {
    die("創建 `支出項目` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 3. 支付方式表
$create_payment_methods_sql = "CREATE TABLE IF NOT EXISTS `支付方式` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `方式名稱` VARCHAR(50) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_payment_methods_sql)) {
    echo "資料表 `支付方式` 創建成功或已存在。<br>";
} else {
    die("創建 `支付方式` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 4. 付款紀錄表
$create_pay_records_sql = "CREATE TABLE IF NOT EXISTS `付款紀錄` (
    `流水號` VARCHAR(50) NOT NULL UNIQUE,
    `受款人_ID` INT NOT NULL,
    `填表日期` DATE NOT NULL,
    `付款日期` DATE DEFAULT NULL,
    `支出項目_ID` INT NOT NULL,
    `支付方式_ID` INT NOT NULL,
    `國字金額` DECIMAL(10,2) NOT NULL,
    `國字金額_hidden` VARCHAR(50),
    `預支金額` DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (`流水號`),
    FOREIGN KEY (`受款人_ID`) REFERENCES `受款人`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`支出項目_ID`) REFERENCES `支出項目`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`支付方式_ID`) REFERENCES `支付方式`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_pay_records_sql)) {
    echo "資料表 `付款紀錄` 創建成功或已存在。<br>";
} else {
    die("創建 `付款紀錄` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 5. 專案表
$create_projects_sql = "CREATE TABLE IF NOT EXISTS `專案` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `付款紀錄_流水號` VARCHAR(50) NOT NULL,
    `專案名稱` VARCHAR(50) NOT NULL,
    `活動名稱` VARCHAR(50) DEFAULT NULL,
    `主題` VARCHAR(50) DEFAULT NULL,
    `專案日期` DATE DEFAULT NULL,
    `獎學金人數` INT DEFAULT NULL,
    `簽收金額` DECIMAL(10,2) DEFAULT NULL,
    `簽收人` VARCHAR(50) DEFAULT NULL,
    `簽收日` DATE DEFAULT NULL,
    FOREIGN KEY (`付款紀錄_流水號`) REFERENCES `付款紀錄`(`流水號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_projects_sql)) {
    echo "資料表 `專案` 創建成功或已存在。<br>";
} else {
    die("創建 `專案` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 6. 銀行資訊表
$create_bank_details_sql = "CREATE TABLE IF NOT EXISTS `銀行資訊` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `付款紀錄_流水號` VARCHAR(50) NOT NULL,
    `銀行郵局` VARCHAR(50) DEFAULT NULL,
    `分行` VARCHAR(50) DEFAULT NULL,
    `戶名` VARCHAR(50) DEFAULT NULL,
    `帳號` VARCHAR(50) DEFAULT NULL,
    `票號` VARCHAR(50) DEFAULT NULL,
    `到期日` DATE DEFAULT NULL,
    FOREIGN KEY (`付款紀錄_流水號`) REFERENCES `付款紀錄`(`流水號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_bank_details_sql)) {
    echo "資料表 `銀行資訊` 創建成功或已存在。<br>";
} else {
    die("創建 `銀行資訊` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 7. 獎學金表
$create_scholarships_sql = "CREATE TABLE IF NOT EXISTS `獎學金` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `付款紀錄_流水號` VARCHAR(50) NOT NULL,
    `獎學金日期` DATE DEFAULT NULL,
    `經濟扶助` VARCHAR(50) DEFAULT NULL,
    FOREIGN KEY (`付款紀錄_流水號`) REFERENCES `付款紀錄`(`流水號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_scholarships_sql)) {
    echo "資料表 `獎學金` 創建成功或已存在。<br>";
} else {
    die("創建 `獎學金` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 8. 其他項目表
$create_other_items_sql = "CREATE TABLE IF NOT EXISTS `其他項目` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `付款紀錄_流水號` VARCHAR(50) NOT NULL,
    `其他項目` VARCHAR(50) DEFAULT NULL,
    `說明` VARCHAR(255) DEFAULT NULL,
    FOREIGN KEY (`付款紀錄_流水號`) REFERENCES `付款紀錄`(`流水號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($連接, $create_other_items_sql)) {
    echo "資料表 `其他項目` 創建成功或已存在。<br>";
} else {
    die("創建 `其他項目` 資料表失敗: " . mysqli_error($連接) . "<br>");
}

// 重新啟用外鍵檢查
mysqli_query($連接, "SET FOREIGN_KEY_CHECKS = 1");

echo "所有資料表已成功創建。<br><br>";

// --- 資料表創建完成，開始處理表單提交 ---

// 生成帶有 'A' + 民國年 + 月 + 5位數序號的流水號函數
function generateSerialNumber($連接) {
    // 取得當前時間
    $now = new DateTime();
    $year = $now->format('Y') - 1911; // 民國年
    $month = str_pad($now->format('m'), 2, '0', STR_PAD_LEFT); // 月份，兩位數
    $prefix = "A{$year}{$month}"; // 前綴，如 A11311

    // 查詢當前月份的最大流水號
    $sql = "SELECT MAX(`流水號`) AS max_count FROM `付款紀錄` WHERE `流水號` LIKE '{$prefix}%'";
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
    $帳號 = !empty($_POST['帳號']) ? "'" . mysqli_real_escape_string($連接, $_POST['帳號']) . "'" : "NULL"; // 修改此處，將帳戶改為帳號
    $票號 = !empty($_POST['票號']) ? "'" . mysqli_real_escape_string($連接, $_POST['票號']) . "'" : "NULL";
    $到期日 = !empty($_POST['到期日']) ? "'" . mysqli_real_escape_string($連接, $_POST['到期日']) . "'" : "NULL";
    $預支金額 = !empty($_POST['預支金額']) ? "'" . mysqli_real_escape_string($連接, $_POST['預支金額']) . "'" : "NULL";

    // 開始事務處理
    mysqli_begin_transaction($連接);

    try {
        // 插入受款人
        $insert_payees_sql = "INSERT INTO `受款人` (`名稱`) VALUES ('$受款人') 
            ON DUPLICATE KEY UPDATE `ID`=LAST_INSERT_ID(`ID`)";
        if (!mysqli_query($連接, $insert_payees_sql)) {
            throw new Exception("插入 `受款人` 失敗: " . mysqli_error($連接));
        }
        $受款人_id = mysqli_insert_id($連接);

        // 插入支出項目
        $insert_expenditure_items_sql = "INSERT INTO `支出項目` (`名稱`) VALUES ('$支出項目') 
            ON DUPLICATE KEY UPDATE `ID`=LAST_INSERT_ID(`ID`)";
        if (!mysqli_query($連接, $insert_expenditure_items_sql)) {
            throw new Exception("插入 `支出項目` 失敗: " . mysqli_error($連接));
        }
        $支出項目_id = mysqli_insert_id($連接);

        // 插入支付方式
        $insert_payment_methods_sql = "INSERT INTO `支付方式` (`方式名稱`) VALUES ('$支付方式') 
            ON DUPLICATE KEY UPDATE `ID`=LAST_INSERT_ID(`ID`)";
        if (!mysqli_query($連接, $insert_payment_methods_sql)) {
            throw new Exception("插入 `支付方式` 失敗: " . mysqli_error($連接));
        }
        $支付方式_id = mysqli_insert_id($連接);

        // 生成新的流水號
        $流水號 = generateSerialNumber($連接);

        // 插入付款紀錄
        $insert_pay_records_sql = "INSERT INTO `付款紀錄` 
            (`流水號`, `受款人_ID`, `填表日期`, `付款日期`, `支出項目_ID`, `支付方式_ID`, `國字金額`, `國字金額_hidden`, `預支金額`)
            VALUES 
            ('$流水號', $受款人_id, '$填表日期', $付款日期, $支出項目_id, $支付方式_id, '$國字金額', '$國字金額_hidden', $預支金額)";

        if (!mysqli_query($連接, $insert_pay_records_sql)) {
            // 如果插入失敗且是因為重複的 `流水號`，則重試
            if (mysqli_errno($連接) == 1062) { // 1062 是 Duplicate entry 錯誤碼
                // 重新生成流水號並重試
                mysqli_rollback($連接);
                // 開始新的事務
                mysqli_begin_transaction($連接);
                $流水號 = generateSerialNumber($連接);
                $insert_pay_records_sql = "INSERT INTO `付款紀錄` 
                    (`流水號`, `受款人_ID`, `填表日期`, `付款日期`, `支出項目_ID`, `支付方式_ID`, `國字金額`, `國字金額_hidden`, `預支金額`)
                    VALUES 
                    ('$流水號', $受款人_id, '$填表日期', $付款日期, $支出項目_id, $支付方式_id, '$國字金額', '$國字金額_hidden', $預支金額)";

                if (!mysqli_query($連接, $insert_pay_records_sql)) {
                    throw new Exception("插入 `付款紀錄` 失敗: " . mysqli_error($連接));
                }
            } else {
                throw new Exception("插入 `付款紀錄` 失敗: " . mysqli_error($連接));
            }
        }

        // 插入專案表（如果有資料）
        if (!empty($_POST['專案名稱'])) {
            $insert_projects_sql = "INSERT INTO `專案` 
                (`付款紀錄_流水號`, `專案名稱`, `活動名稱`, `主題`, `專案日期`, `獎學金人數`, `簽收金額`, `簽收人`, `簽收日`)
                VALUES 
                ('$流水號', $專案名稱, $活動名稱, $主題, $專案日期, $獎學金人數, $簽收金額, $簽收人, $簽收日)";

            if (!mysqli_query($連接, $insert_projects_sql)) {
                throw new Exception("插入 `專案` 失敗: " . mysqli_error($連接));
            }
        }

        // 插入銀行資訊表（如果有資料）
        if (!empty($_POST['銀行郵局']) || !empty($_POST['分行']) || !empty($_POST['戶名']) || !empty($_POST['帳號']) || !empty($_POST['票號']) || !empty($_POST['到期日'])) {
            $insert_bank_details_sql = "INSERT INTO `銀行資訊` 
                (`付款紀錄_流水號`, `銀行郵局`, `分行`, `戶名`, `帳號`, `票號`, `到期日`)
                VALUES 
                ('$流水號', $銀行郵局, $分行, $戶名, $帳號, $票號, $到期日)";

            if (!mysqli_query($連接, $insert_bank_details_sql)) {
                throw new Exception("插入 `銀行資訊` 失敗: " . mysqli_error($連接));
            }
        }

        // 插入獎學金表（如果有資料）
        if (!empty($_POST['獎學金日期']) || !empty($_POST['經濟扶助'])) {
            $insert_scholarships_sql = "INSERT INTO `獎學金` 
                (`付款紀錄_流水號`, `獎學金日期`, `經濟扶助`)
                VALUES 
                ('$流水號', $獎學金日期, $經濟扶助)";

            if (!mysqli_query($連接, $insert_scholarships_sql)) {
                throw new Exception("插入 `獎學金` 失敗: " . mysqli_error($連接));
            }
        }

        // 插入其他項目表（如果有資料）
        if (!empty($_POST['其他項目']) || !empty($_POST['說明'])) {
            $insert_other_items_sql = "INSERT INTO `其他項目` 
                (`付款紀錄_流水號`, `其他項目`, `說明`)
                VALUES 
                ('$流水號', $其他項目, '$說明')";

            if (!mysqli_query($連接, $insert_other_items_sql)) {
                throw new Exception("插入 `其他項目` 失敗: " . mysqli_error($連接));
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

<?php
// init_db.php

// 資料庫連接資訊
$server = 'localhost:3307'; // 確認您的 MySQL 伺服器埠號
$username = 'root';
$password = ' '; // 如果無密碼，請使用空字串
$database = '預支';

// 連接到 MySQL
$connection = mysqli_connect($server, $username, $password);

// 檢查連接
if (!$connection) {
    die("連接失敗: " . mysqli_connect_error());
}

// 創建資料庫（如果不存在）
$sql = "CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if (mysqli_query($connection, $sql)) {
    echo "資料庫 `$database` 已存在或創建成功。<br>";
} else {
    die("創建資料庫失敗: " . mysqli_error($connection) . "<br>");
}

// 選擇資料庫
if (!mysqli_select_db($connection, $database)) {
    die("選擇資料庫失敗: " . mysqli_error($connection) . "<br>");
}

// 設定 SQL 模式以避免外鍵創建問題
mysqli_query($connection, "SET FOREIGN_KEY_CHECKS = 0");

// 創建 `申請單號` 表
$create_application_numbers_sql = "CREATE TABLE IF NOT EXISTS `申請單號` (
    `申請單號` VARCHAR(20) PRIMARY KEY
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($connection, $create_application_numbers_sql)) {
    echo "資料表 `申請單號` 創建成功或已存在。<br>";
} else {
    die("創建 `申請單號` 資料表失敗: " . mysqli_error($connection) . "<br>");
}

// 創建 `受款人` 表
$create_payees_sql = "CREATE TABLE IF NOT EXISTS `受款人` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `名稱` VARCHAR(50) NOT NULL,
    `填表日期` DATE NOT NULL,
    `付款日期` DATE DEFAULT NULL,
    `國字金額` DECIMAL(10,2) NOT NULL,
    `申請單號` VARCHAR(20),
    FOREIGN KEY (`申請單號`) REFERENCES `申請單號`(`申請單號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($connection, $create_payees_sql)) {
    echo "資料表 `受款人` 創建成功或已存在。<br>";
} else {
    die("創建 `受款人` 資料表失敗: " . mysqli_error($connection) . "<br>");
}

// 創建 `支出項目` 表
$create_expenditure_items_sql = "CREATE TABLE IF NOT EXISTS `支出項目` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `名稱` VARCHAR(50) NOT NULL,
    `說明` VARCHAR(255) DEFAULT NULL,
    `申請單號` VARCHAR(20),
    FOREIGN KEY (`申請單號`) REFERENCES `申請單號`(`申請單號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($connection, $create_expenditure_items_sql)) {
    echo "資料表 `支出項目` 創建成功或已存在。<br>";
} else {
    die("創建 `支出項目` 資料表失敗: " . mysqli_error($connection) . "<br>");
}

// 創建 `支付方式` 表
$create_payment_methods_sql = "CREATE TABLE IF NOT EXISTS `支付方式` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `方式名稱` VARCHAR(50) NOT NULL,
    `簽收金額` DECIMAL(10,2) DEFAULT NULL,
    `簽收人` VARCHAR(50) DEFAULT NULL,
    `簽收日` DATE DEFAULT NULL,
    `國字金額_hidden` VARCHAR(50),
    `銀行郵局` VARCHAR(50) DEFAULT NULL,
    `分行` VARCHAR(50) DEFAULT NULL,
    `戶名` VARCHAR(50) DEFAULT NULL,
    `帳號` VARCHAR(50) DEFAULT NULL,
    `票號` VARCHAR(50) DEFAULT NULL,
    `到期日` DATE DEFAULT NULL,
    `預支金額` DECIMAL(10,2) DEFAULT NULL,
    `申請單號` VARCHAR(20),
    FOREIGN KEY (`申請單號`) REFERENCES `申請單號`(`申請單號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($connection, $create_payment_methods_sql)) {
    echo "資料表 `支付方式` 創建成功或已存在。<br>";
} else {
    die("創建 `支付方式` 資料表失敗: " . mysqli_error($connection) . "<br>");
}

// 創建 `付款紀錄` 表
$create_pay_records_sql = "CREATE TABLE IF NOT EXISTS `付款紀錄` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `申請單號` VARCHAR(20) NOT NULL UNIQUE,
    `受款人_ID` INT NOT NULL,
    `填表日期` DATE NOT NULL,
    `付款日期` DATE DEFAULT NULL,
    `支出項目_ID` INT NOT NULL,
    `支付方式_ID` INT NOT NULL,
    `國字金額` DECIMAL(10,2) NOT NULL,
    `國字金額_hidden` VARCHAR(50),
    `預支金額` DECIMAL(10,2) DEFAULT NULL,
    FOREIGN KEY (`受款人_ID`) REFERENCES `受款人`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`支出項目_ID`) REFERENCES `支出項目`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (`支付方式_ID`) REFERENCES `支付方式`(`ID`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($connection, $create_pay_records_sql)) {
    echo "資料表 `付款紀錄` 創建成功或已存在。<br>";
} else {
    die("創建 `付款紀錄` 資料表失敗: " . mysqli_error($connection) . "<br>");
}

// 創建 `檔案上傳` 表
$create_file_uploads_sql = "CREATE TABLE IF NOT EXISTS `檔案上傳` (
    `ID` INT AUTO_INCREMENT PRIMARY KEY,
    `申請單號` VARCHAR(20),
    `檔案名稱` VARCHAR(255) NOT NULL,
    `檔案路徑` VARCHAR(255) NOT NULL,
    `上傳時間` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`申請單號`) REFERENCES `申請單號`(`申請單號`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";

if (mysqli_query($connection, $create_file_uploads_sql)) {
    echo "資料表 `檔案上傳` 創建成功或已存在。<br>";
} else {
    die("創建 `檔案上傳` 資料表失敗: " . mysqli_error($connection) . "<br>");
}

// 重新啟用外鍵檢查
mysqli_query($connection, "SET FOREIGN_KEY_CHECKS = 1");

// 關閉資料庫連接
mysqli_close($connection);
?>

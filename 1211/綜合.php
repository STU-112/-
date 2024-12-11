<?php
// 連接資料庫
$server = 'localhost:3307'; // 伺服器名稱
$用戶名 = 'root'; // 用戶名
$密碼 = ' '; // 密碼 (設為空字串)
$資料庫 = '綜合'; // 資料庫名稱

// 連接到 MySQL
$連接 = mysqli_connect($server, $用戶名, $密碼);

// 檢查連接
if (!$連接) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查資料庫是否已存在，若不存在則創建
$sql = "CREATE DATABASE IF NOT EXISTS $資料庫";
if (mysqli_query($連接, $sql)) {
    // 資料庫已存在或創建成功
} else {
    die("創建資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 選擇資料庫
if (!mysqli_select_db($連接, $資料庫)) {
    die("選擇資料庫失敗: " . mysqli_error($連接) . "<br>");
}

// 創建資料表（基本資料、支出資料、說明、支付方式、附加檔案）
$create_基本資料_sql = "CREATE TABLE IF NOT EXISTS 基本資料 (
    count VARCHAR(50) NOT NULL UNIQUE,
    受款人 VARCHAR(50) NOT NULL,
    填表日期 DATE NOT NULL,
    付款日期 DATE DEFAULT NULL,
    PRIMARY KEY (count)
) ENGINE=InnoDB;";

if (!mysqli_query($連接, $create_基本資料_sql)) {
    die("創建基本資料表失敗: " . mysqli_error($連接) . "<br>");
}

$create_支出項目_sql = "CREATE TABLE IF NOT EXISTS 支出項目 (
    count VARCHAR(50) NOT NULL,
    支出項目 VARCHAR(50) NOT NULL,
    活動名稱 VARCHAR(50) DEFAULT NULL,
    專案日期 DATE DEFAULT NULL,
    獎學金人數 INT DEFAULT NULL,
    專案名稱 CHAR(10) DEFAULT NULL,
    主題 CHAR(50) DEFAULT NULL,
    獎學金日期 DATE DEFAULT NULL,
    經濟扶助 CHAR(10) DEFAULT NULL,
    其他項目 CHAR(50) DEFAULT NULL,
    PRIMARY KEY (count),
    FOREIGN KEY (count) REFERENCES 基本資料(count) ON DELETE CASCADE
) ENGINE=InnoDB;";

if (!mysqli_query($連接, $create_支出項目_sql)) {
    die("創建支出資料表失敗: " . mysqli_error($連接) . "<br>");
}

$create_說明_sql = "CREATE TABLE IF NOT EXISTS 說明 (
    count VARCHAR(50) NOT NULL UNIQUE,
    說明 CHAR(100) DEFAULT NULL,
    PRIMARY KEY (count),
    FOREIGN KEY (count) REFERENCES 基本資料(count) ON DELETE CASCADE
) ENGINE=InnoDB;";

if (!mysqli_query($連接, $create_說明_sql)) {
    die("創建說明資料表失敗: " . mysqli_error($連接) . "<br>");
}

$create_支付方式_sql = "CREATE TABLE IF NOT EXISTS 支付方式 (
    count VARCHAR(50) NOT NULL UNIQUE,
    支付方式 CHAR(10) NOT NULL,
    國字金額 DECIMAL(10,2),
    國字金額_hidden CHAR(50),
    簽收金額 DECIMAL(10,2) DEFAULT NULL,
    簽收人 CHAR(10) DEFAULT NULL,
    簽收日 DATE DEFAULT NULL,
    銀行郵局 CHAR(10) DEFAULT NULL,
    分行 CHAR(10) DEFAULT NULL,
    戶名 CHAR(10) DEFAULT NULL,
    帳號 CHAR(10) DEFAULT NULL,
    票號 CHAR(10) DEFAULT NULL,
    到期日 DATE DEFAULT NULL,
    付款金額 DECIMAL(10,2) DEFAULT NULL,
    PRIMARY KEY (count),
    FOREIGN KEY (count) REFERENCES 基本資料(count) ON DELETE CASCADE
) ENGINE=InnoDB;";

if (!mysqli_query($連接, $create_支付方式_sql)) {
    die("創建支付方式資料表失敗: " . mysqli_error($連接) . "<br>");
}

$create_附加檔案_sql = "CREATE TABLE IF NOT EXISTS uploads (
    count VARCHAR(50) PRIMARY KEY,
    image_path VARCHAR(255) NULL,
    csv_path VARCHAR(255) NULL,
    upload_time DATETIME NOT NULL
) ENGINE=InnoDB;";

if (!mysqli_query($連接, $create_附加檔案_sql)) {
    die("創建附加檔案資料表失敗: " . mysqli_error($連接) . "<br>");
}

$uploadDir = "C:/xampp/htdocs/uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

function generateSerialNumber($連接) {
    $now = new DateTime();
    $year = $now->format('Y') - 1911;
    $month = str_pad($now->format('m'), 2, '0', STR_PAD_LEFT);
    $prefix = "B{$year}{$month}";

    $sql = "SELECT MAX(count) AS max_count FROM 基本資料 WHERE count LIKE '{$prefix}%';";
    $result = mysqli_query($連接, $sql);
    $row = mysqli_fetch_assoc($result);

    $new_serial = ($row['max_count']) ? intval(substr($row['max_count'], strlen($prefix))) + 1 : 1;
    return $prefix . str_pad($new_serial, 5, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"]) && isset($_FILES["csv"])) {
    $流水號 = generateSerialNumber($連接);

    // 圖片處理
    $imageName = basename($_FILES["image"]["name"]);
    $imagePath = $uploadDir . $imageName;
    $imageFileType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

    // CSV 檔案處理
    $csvName = basename($_FILES["csv"]["name"]);
    $csvPath = $uploadDir . $csvName;
    $csvFileType = strtolower(pathinfo($csvPath, PATHINFO_EXTENSION));

    $allowedImageTypes = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedImageTypes)) {
        die("圖片格式僅限 JPEG, PNG 或 GIF。");
    }

    if ($csvFileType != "csv") {
        die("請上傳有效的 CSV 檔案。");
    }

    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
        die("圖片上傳失敗。");
    }

    if (!move_uploaded_file($_FILES["csv"]["tmp_name"], $csvPath)) {
        die("CSV 檔案上傳失敗。");
    }

    $uploadTime = date("Y-m-d H:i:s");
    $sql = "INSERT INTO uploads (count, image_path, csv_path, upload_time) VALUES (?, ?, ?, ?)";
    $stmt = $連接->prepare($sql);
    $stmt->bind_param("ssss", $流水號, $imagePath, $csvPath, $uploadTime);

    if ($stmt->execute()) {
        echo "上傳成功！流水號：$流水號";
    } else {
        echo "儲存到資料庫失敗：" . $stmt->error;
    }

    $stmt->close();
}

mysqli_close($連接);
?>

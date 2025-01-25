<?php
session_start();

// 檢查是否登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 測試html.php");
    exit;
}

// 檢查 Token
if (!isset($_POST['upload_token']) || !isset($_SESSION['upload_token']) || 
    $_POST['upload_token'] !== $_SESSION['upload_token']) {
    // Token 不符或不存在，就擋下
    exit("非法或重複的上傳請求，請重新操作。");
}

// 驗證成功後，馬上清除 Token，避免重複提交
unset($_SESSION['upload_token']);

// 使用者 ID（假設登入時就放在 session）
$userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;
if ($userId <= 0) {
    exit("使用者資訊有誤，無法上傳。");
}

// 設定資料庫連線參數 (依實際狀況調整)
$servername = "localhost:3307";
$username = "root";
$password = " ";
$dbname = "綜合";

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 建立資料庫（若不存在）
$sql = "CREATE DATABASE IF NOT EXISTS `綜合`";
$conn->query($sql);

// 使用資料庫
$conn->select_db($dbname);

// 建立資料表（若不存在）
// 新增 user_id 欄位，用來記錄「哪位使用者上傳的」。
$table = "CREATE TABLE IF NOT EXISTS uploads (
    count VARCHAR(15) NOT NULL UNIQUE,
    user_id INT NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    csv_path VARCHAR(255) NOT NULL,
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
    單據張數 INT NOT NULL
)";
$conn->query($table);

// 生成流水號功能
function generateSerialNumber($conn) {
    $now = new DateTime();
    $year = $now->format('Y') - 1911; // 民國年
    $month = str_pad($now->format('m'), 2, '0', STR_PAD_LEFT);
    $prefix = "B{$year}{$month}";

    $sql = "SELECT MAX(count) AS max_count FROM uploads WHERE count LIKE '{$prefix}%'";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    if ($row && $row['max_count']) {
        // 取出 max_count 後面的流水號
        $new_serial = intval(substr($row['max_count'], strlen($prefix))) + 1;
    } else {
        $new_serial = 1;
    }

    return $prefix . str_pad($new_serial, 5, '0', STR_PAD_LEFT);
}

// 處理上傳請求
$uploadDir = "C:/xampp/htdocs/uploads/";
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

$serialNumber = generateSerialNumber($conn);
$單據張數 = isset($_POST['單據張數']) ? intval($_POST['單據張數']) : 0;

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 不需要上傳情況
    if (isset($_POST['no-upload']) && $_POST['no-upload'] === 'true') {
        $sql = "INSERT INTO uploads (count, user_id, image_path, csv_path, 單據張數)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $emptyValue = "--";
        $stmt->bind_param("sissi", $serialNumber, $userId, $emptyValue, $emptyValue, $單據張數);

        if ($stmt->execute()) {
            echo <<<HTML
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>好的</title>
    <style>
        body {
            background-color: #fdf2e9;
            text-align: center;
            padding: 50px;
        }
    </style>
    <script>
        setTimeout(() => {
            window.location.href = "綜合.html";
        }, 3000);
    </script>
</head>
<body>
    <h1>好的~</h1>
    <p>3秒後跳轉回主頁...</p>
</body>
</html>
HTML;
        } else {
            echo "儲存資料失敗：" . $stmt->error;
        }
        $stmt->close();

    // 需要上傳情況
    } elseif (isset($_FILES["image"]) && isset($_FILES["csv"])) {
        $imageName = basename($_FILES["image"]["name"]);
        $imagePath = $uploadDir . $imageName;

        $csvName = basename($_FILES["csv"]["name"]);
        $csvPath = $uploadDir . $csvName;

        move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath);
        move_uploaded_file($_FILES["csv"]["tmp_name"], $csvPath);

        $sql = "INSERT INTO uploads (count, user_id, image_path, csv_path, 單據張數)
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sissi", $serialNumber, $userId, $imagePath, $csvPath, $單據張數);

        if ($stmt->execute()) {
            echo <<<HTML
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上傳成功</title>
    <style>
        body {
            background-color: #e6ffed;
            text-align: center;
            padding: 50px;
        }
    </style>
    <script>
        setTimeout(() => {
            window.location.href = "綜合.html";
        }, 3000);
    </script>
</head>
<body>
    <h1>上傳成功</h1>
    <p>資料已儲存，流水號：{$serialNumber}</p>
    <p>3秒後跳轉回主頁...</p>
</body>
</html>
HTML;
        } else {
            echo "儲存資料失敗：" . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
?>

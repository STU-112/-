<?php
// 設定資料庫連線參數
$servername = "localhost:3307"; // 指定正確的 MySQL 伺服器端口
$username = "root";
$password = " "; // 若無密碼則保持空白
$dbname = "預支";

// 建立資料庫連線
$conn = new mysqli($servername, $username, $password);

// 檢查連線
if ($conn->connect_error) {
    die("資料庫連線失敗：" . $conn->connect_error);
}

// 建立資料庫（若不存在）
$sql = "CREATE DATABASE IF NOT EXISTS 預支";
if ($conn->query($sql) === TRUE) {
    echo "資料庫 預支 檢查成功！<br>";
} else {
    die("建立資料庫失敗：" . $conn->error);
}

// 使用資料庫
$conn->select_db($dbname);

// 建立資料表（若不存在）
$table = "CREATE TABLE IF NOT EXISTS uploads (
    count INT AUTO_INCREMENT PRIMARY KEY,
    image_path VARCHAR(255) NOT NULL,
    csv_path VARCHAR(255) NOT NULL,
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
	單據張數 INT NOT NULL
)";
if ($conn->query($table) === TRUE) {
    echo "資料表 uploads 檢查成功！<br>";
} else {
    die("建立資料表失敗：" . $conn->error);
}

// 上傳資料夾路徑
$uploadDir = "C:/xampp/htdocs/uploads/";

// 確保資料夾存在
if (!file_exists($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// 檢查是否有表單提交
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES["image"]) && isset($_FILES["csv"])) {
    // 圖片處理
    $imageName = basename($_FILES["image"]["name"]);
    $imagePath = $uploadDir . $imageName;
    $imageFileType = strtolower(pathinfo($imagePath, PATHINFO_EXTENSION));

    // CSV 檔案處理
    $csvName = basename($_FILES["csv"]["name"]);
    $csvPath = $uploadDir . $csvName;
    $csvFileType = strtolower(pathinfo($csvPath, PATHINFO_EXTENSION));

    // 驗證圖片類型
    $allowedImageTypes = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowedImageTypes)) {
        die("圖片格式僅限 JPEG, PNG 或 GIF。");
    }

    // 驗證 CSV 類型
    if ($csvFileType != "csv") {
        die("請上傳有效的 CSV 檔案。");
    }

    // 上傳圖片
    if (!move_uploaded_file($_FILES["image"]["tmp_name"], $imagePath)) {
        die("圖片上傳失敗。");
    }

    // 上傳 CSV
    if (!move_uploaded_file($_FILES["csv"]["tmp_name"], $csvPath)) {
        die("CSV 檔案上傳失敗。");
    }

    // 假設單據張數來自表單中的輸入值
    $單據張數 = isset($_POST['單據張數']) ? intval($_POST['單據張數']) : 0;

    // 儲存到資料庫
    $sql = "INSERT INTO uploads (image_path, csv_path, 單據張數, upload_time) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $uploadTime = date("Y-m-d H:i:s");
    $stmt->bind_param("ssis", $imagePath, $csvPath, $單據張數, $uploadTime);

    if ($stmt->execute()) {
        echo "檔案上傳成功！<br>";
        echo "圖片路徑：" . $imagePath . "<br>";
        echo "CSV 路徑：" . $csvPath . "<br>";
        echo "單據張數：" . $單據張數 . "<br>";
    } else {
        echo "儲存到資料庫失敗：" . $stmt->error . "<br>";
    }

    $stmt->close();
}

// 關閉資料庫連線
$conn->close();
?>

<?php
$servername = "localhost:3307";
$username = "root";
$password = "3307";
$dbname = "報帳";

// 創建連接
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 建立資料庫
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// 建立資料表（用來存儲上傳的檔案資訊）
$conn->query("CREATE TABLE IF NOT EXISTS 報帳資料表 (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    檔案名稱 VARCHAR(255) NOT NULL,
    檔案路徑 VARCHAR(255) NOT NULL,
    上傳時間 TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 處理 CSV 上傳
$csv_success = false;
$csv_file_path = "";
if (isset($_FILES['csv_file'])) {
    $targetDir = "uploads/";
    $csv_file_name = basename($_FILES['csv_file']['name']);
    $targetFile = $targetDir . $csv_file_name;

    if (move_uploaded_file($_FILES['csv_file']['tmp_name'], $targetFile)) {
        // 儲存檔案資訊到資料庫
        $csv_file_path = $conn->real_escape_string($targetFile);
        $conn->query("INSERT INTO 報帳資料表 (檔案名稱, 檔案路徑) VALUES ('$csv_file_name', '$csv_file_path')");
        $csv_success = true;
    }
}

// 處理圖檔上傳
$image_success = false;
$image_file_path = "";
if (isset($_FILES['image'])) {
    $targetDir = "uploads/";
    $image_file_name = basename($_FILES['image']['name']);
    $targetFile = $targetDir . $image_file_name;
    $imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (in_array($imageFileType, $allowedTypes) && $_FILES['image']['size'] <= 2 * 1024 * 1024) {
        if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
            // 儲存圖檔資訊到資料庫
            $image_file_path = $conn->real_escape_string($targetFile);
            $conn->query("INSERT INTO 報帳資料表 (檔案名稱, 檔案路徑) VALUES ('$image_file_name', '$image_file_path')");
            $image_success = true;
        }
    }
}


$conn->close();
?>
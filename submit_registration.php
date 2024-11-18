<?php
// 連接到 MariaDB 資料庫
$servername = "localhost"; // 資料庫主機
$username = "root"; // 資料庫用戶名
$password = ""; // 資料庫密碼
$dbname = "ccna"; // 資料庫名稱

$conn = new mysqli($servername, $username, $password, $dbname);

// 檢查連接
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 獲取 JSON 格式的請求資料
$data = json_decode(file_get_contents('php://input'), true);

// 準備插入資料的 SQL 語句
$stmt = $conn->prepare("INSERT INTO users (userid, name, phone, address, username, password) VALUES (?, ?, ?, ?, ?, ?)");

// 遍歷每一條資料並插入
foreach ($data as $record) {
    $stmt->bind_param("ssssss", $record['userid'], $record['name'], $record['phone'], $record['address'], $record['username'], $record['password']);
    $stmt->execute();
}

$stmt->close();
$conn->close();

echo "資料已成功提交！";
?>

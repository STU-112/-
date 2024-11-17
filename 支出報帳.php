<?php
// 資料庫連線參數設定
$servername = "localhost:3307"; // 伺服器名稱
$username = "root"; // 資料庫使用者名稱
$password = " "; // 資料庫密碼 (空字串)
$dbname = "支出報帳"; // 資料庫名稱

// 創建連接
$conn = new mysqli($servername, $username, $password);
if ($conn->connect_error) {
    die("連接失敗: " . $conn->connect_error);
}

// 建立資料庫
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// 建立資料表（用來存儲上傳的檔案資訊）
$conn->query("CREATE TABLE IF NOT EXISTS 支出報帳資料表 (
    id INT(11) AUTO_INCREMENT PRIMARY KEY,
    form_date DATE,
    serial_number VARCHAR(50),
    recipient_name VARCHAR(100),
    payment_date DATE,
    activity_cost DECIMAL(10, 2),
    project TEXT,
    funding TEXT,
    scholarship_count INT,
    project_name VARCHAR(100),
    subject VARCHAR(100),
    event_date DATE,
    other_content TEXT,
    cross_dept_content TEXT,
    csv_file_path VARCHAR(255),
    image_file_path VARCHAR(255),
    upload_time TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// 從表單取得輸入資料
$form_date = $_POST['form-date'];
$serial_number = $_POST['serial-number'];
$recipient_name = $_POST['recipient-name'];
$payment_date = $_POST['payment-date'];
$activity_cost = $_POST['activity-cost'];
$project = isset($_POST['project']) ? implode(", ", $_POST['project']) : null;
$funding = isset($_POST['funding']) ? implode(", ", $_POST['funding']) : null;
$scholarship_count = $_POST['scholarship-count'];
$project_name = $_POST['project-name'];
$subject = $_POST['subject'];
$event_date = $_POST['event-date'];
$other_content = $_POST['other-content'];
$cross_dept_content = $_POST['cross-dept-content'];

// 處理文件上傳
$csv_file_path = null; // CSV 檔案路徑初始為 null
$image_file_path = null; // 圖片檔案路徑初始為 null

// 檢查是否有上傳 CSV 檔案
if (!empty($_FILES['csv-upload']['name'])) {
    $csv_file_path = "uploads/" . basename($_FILES['csv-upload']['name']);
    move_uploaded_file($_FILES['csv-upload']['tmp_name'], $csv_file_path);
}

// 檢查是否有上傳圖片檔案
if (!empty($_FILES['image-upload']['name'])) {
    $image_file_path = "uploads/" . basename($_FILES['image-upload']['name']);
    move_uploaded_file($_FILES['image-upload']['tmp_name'], $image_file_path);
}

// 建立 SQL 插入語句
$sql = "INSERT INTO 支出報帳資料表 (form_date, serial_number, recipient_name, payment_date, activity_cost, project, funding, 
        scholarship_count, project_name, subject, event_date, other_content, cross_dept_content, csv_file_path, image_file_path) 
        VALUES ('$form_date', '$serial_number', '$recipient_name', '$payment_date', '$activity_cost', '$project', '$funding', 
        '$scholarship_count', '$project_name', '$subject', '$event_date', '$other_content', '$cross_dept_content', '$csv_file_path', '$image_file_path')";

// 檢查資料是否成功插入資料庫
if ($conn->query($sql) === TRUE) {
    $insert_success = true; // 插入成功標誌
} else {
    $insert_success = false; // 插入失敗標誌
}

// 關閉資料庫連線
$conn->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上傳結果</title>
    <style>
        body {
            margin: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            background: linear-gradient(135deg, #74b9ff, #81ecec);
            font-family: 'Noto Sans TC', sans-serif;
            color: #2d3436;
        }
        .result-box {
            background-color: #fff;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
            text-align: center;
            width: 90%;
            max-width: 400px;
        }
        h1 {
            margin-bottom: 20px;
            color: #0984e3;
        }
        p {
            margin: 10px 0;
            font-size: 18px;
        }
        .success {
            color: #00b894;
        }
        .failure {
            color: #d63031;
        }
        .countdown {
            font-weight: bold;
            color: #636e72;
        }
        .button {
            display: inline-block;
            margin-top: 20px;
            padding: 10px 20px;
            background-color: #0984e3;
            color: white;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            text-decoration: none;
            font-size: 18px;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #74b9ff;
        }
    </style>
</head>
<body>
    <div class="result-box">
        <h1>上傳結果</h1>
        <?php if ($insert_success): ?>
            <p class="success">資料儲存成功！</p>
            <?php if ($csv_file_path): ?>
                <p><a href="<?php echo $csv_file_path; ?>" download>點此下載 CSV 檔案</a></p>
            <?php endif; ?>
            <?php if ($image_file_path): ?>
                <p><a href="<?php echo $image_file_path; ?>" download>點此下載圖檔</a></p>
            <?php endif; ?>
        <?php else: ?>
            <p class="failure">資料儲存失敗。</p>
        <?php endif; ?>

        <p>將在 <span id="countdown">5</span> 秒內返回。</p>
        <a href="報帳.html" class="button">立即返回</a>
    </div>

    <script>
        let countdown = 5;
        const interval = setInterval(() => {
            document.getElementById('countdown').innerText = countdown;
            countdown--;
            if (countdown < 0) {
                clearInterval(interval);
                window.location.href = "報帳.html";
            }
        }, 1000);
    </script>
</body>
</html>

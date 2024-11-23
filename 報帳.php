<?php 
$servername = "localhost:3307"; 
$username = "root"; 
$password = " "; 
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
$conn->query("DROP TABLE IF EXISTS 報帳資料表");
$conn->query("CREATE TABLE IF NOT EXISTS 報帳資料表 ( 
    `count` INT(11) AUTO_INCREMENT PRIMARY KEY, 
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

// 顯示結果頁面
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
        <?php if ($csv_success): ?>
            <p class="success">CSV 檔案上傳成功！</p>
            <p><a href="<?php echo $csv_file_path; ?>" download>點此下載 CSV 檔案</a></p>
        <?php else: ?>
            <p class="failure">CSV 檔案上傳失敗。</p>
        <?php endif; ?>

        <?php if ($image_success): ?>
            <p class="success">圖檔上傳成功！</p>
            <p><a href="<?php echo $image_file_path; ?>" download>點此下載圖檔</a></p>
        <?php else: ?>
            <p class="failure">圖檔上傳失敗。</p>
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

<?php
$conn->close();
?>

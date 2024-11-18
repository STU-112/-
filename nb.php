<?php
// 設定數據庫連接資料
$db_host = "localhost:3307"; // 指定主機和端口
$db_id = "root";             // 資料庫用戶名
$db_pw = " ";                // 資料庫密碼（空白鍵）
$db_name = "nb";            // 資料庫名稱

// 連接到 MySQL
$db_link = mysqli_connect($db_host, $db_id, $db_pw);

// 檢查連接是否成功
if (!$db_link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 檢查資料庫是否已存在，若不存在則創建
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (!mysqli_query($db_link, $sql)) {
    die("創建資料庫失敗: " . mysqli_error($db_link) . "<br>");
}

// 選擇資料庫
mysqli_select_db($db_link, $db_name);

// 創建資料表
$create_table_sql = "CREATE TABLE IF NOT EXISTS 受款人 (
    單號 INT AUTO_INCREMENT PRIMARY KEY,
    受款人姓名 VARCHAR(20),
    電子郵件 VARCHAR(30),
    電話號碼 CHAR(10),
    身分證字號 CHAR(10),
    聯絡地址 CHAR(254),
    註冊日期 DATE,
    驗證 INT,
    公司名稱 VARCHAR(20)
)";

if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link) . "<br>");
}

// 初始化輸入變數
$姓名 = $電子郵件 = $電話號碼 = $身分證字號 = $聯絡地址 = $註冊日期 = $驗證 = $公司名稱 = "";
$成功提交 = false;

// 檢查是否有提交資料
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 取得 POST 資料
    $姓名 = $_POST['受款人姓名'];
    $電子郵件 = $_POST['電子郵件'];
    $電話號碼 = $_POST['電話號碼'];
    $身分證字號 = $_POST['身分證字號'];
    $聯絡地址 = $_POST['聯絡地址'];
    $註冊日期 = $_POST['註冊日期'];
    $驗證 = $_POST['驗證'];
    $公司名稱 = $_POST['公司名稱'];

    // 插入資料到資料庫
    $insert_record_sql = "INSERT INTO 受款人 (受款人姓名, 電子郵件, 電話號碼, 身分證字號, 聯絡地址, 註冊日期, 驗證, 公司名稱) 
    VALUES ('$姓名', '$電子郵件', '$電話號碼', '$身分證字號', '$聯絡地址', '$註冊日期', '$驗證', '$公司名稱')";

    if (mysqli_query($db_link, $insert_record_sql)) {
        $成功提交 = true; // 設置成功提交標誌
    } else {
        die("插入記錄失敗: " . mysqli_error($db_link) . "<br>");
    }
}
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>提交結果 - 台北市失親兒基金會</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(to bottom, #ffebcd, #ffdead);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .result-container {
            background-color: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 100%;
            box-sizing: border-box;
        }
        .success-message {
            color: green;
            text-align: center;
            margin-top: 20px;
        }
        .announcement {
            background-color: #ffcc99;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
            text-align: center;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="result-container">
        <div class="announcement">
            台北市失親兒基金會
        </div>

        <?php if ($成功提交): ?>
            <h1>提交成功!!</h1>
            <p>以下是您輸入的資料:</p>
            <p>受款人姓名: <?php echo htmlspecialchars($姓名); ?></p>
            <p>電子郵件: <?php echo htmlspecialchars($電子郵件); ?></p>
            <p>電話號碼: <?php echo htmlspecialchars($電話號碼); ?></p>
            <p>身分證字號: <?php echo htmlspecialchars($身分證字號); ?></p>
            <p>聯絡地址: <?php echo htmlspecialchars($聯絡地址); ?></p>
            <p>註冊日期: <?php echo htmlspecialchars($註冊日期); ?></p>
            <p>驗證: <?php echo htmlspecialchars($驗證); ?></p>
            <p>公司名稱: <?php echo htmlspecialchars($公司名稱); ?></p>
            <p class="success-message">感謝您的提交!</p>
        <?php else: ?>
            <h1>提交失敗</h1>
            <p>請重試.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
// 關閉資料庫連接
mysqli_close($db_link);
?>

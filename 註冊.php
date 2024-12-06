<?php 
$db_host = "localhost:3307"; // 指定主機和端口
$db_id = "root";             // 資料庫用戶名
$db_pw = "3307";                 // 資料庫密碼（可根據需求修改）
$db_name = "註冊";           // 資料庫名稱

// 連接資料庫
$db_link = mysqli_connect($db_host, $db_id, $db_pw);
if (!$db_link) {
    die("連接失敗: " . mysqli_connect_error());
}

// 創建資料庫（如果資料庫不存在）
$sql = "CREATE DATABASE IF NOT EXISTS $db_name";
if (!mysqli_query($db_link, $sql)) {
    die("創建資料庫失敗: " . mysqli_error($db_link));
}

// 選擇資料庫
mysqli_select_db($db_link, $db_name);

// 創建資料表（如果表不存在）
$create_table_sql = "CREATE TABLE IF NOT EXISTS 註冊資料表 (
    姓名 CHAR(30) NOT NULL,
    電話 CHAR(20),
    地址 CHAR(30),
    部門 CHAR(10) NOT NULL,
    職位 CHAR(10) NOT NULL,
    帳號 CHAR(20) NOT NULL UNIQUE,
    密碼 CHAR(60) NOT NULL,
    權限管理 CHAR(20) DEFAULT '經辦人',
    PRIMARY KEY (帳號)
)";

if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link));
}

// 插入記錄
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = mysqli_real_escape_string($db_link, $_POST['姓名']);
    $phone = mysqli_real_escape_string($db_link, $_POST['電話']);
    $address = mysqli_real_escape_string($db_link, $_POST['地址']);
    $department = mysqli_real_escape_string($db_link, $_POST['部門']);
    $position = mysqli_real_escape_string($db_link, $_POST['職位']);
    $username = mysqli_real_escape_string($db_link, $_POST['帳號']);
    $password = mysqli_real_escape_string($db_link, $_POST['密碼']); // 存儲明文密碼
    $permission = isset($_POST['權限管理']) ? mysqli_real_escape_string($db_link, $_POST['權限管理']) : '經辦人'; // 預設為經辦人

    // 檢查帳號是否已存在
    $check_user_sql = "SELECT 帳號 FROM 註冊資料表 WHERE 帳號 = '$username'";
    $result = mysqli_query($db_link, $check_user_sql);
    if (mysqli_num_rows($result) > 0) {
        echo "帳號已存在，請使用其他帳號註冊。<br>";
    } else {
        // 插入記錄
        $insert_record_sql = "INSERT INTO 註冊資料表 (姓名, 電話, 地址, 部門, 職位, 帳號, 密碼, 權限管理)
                              VALUES ('$name', '$phone', '$address', '$department', '$position', '$username', '$password', '$permission')";

        if (mysqli_query($db_link, $insert_record_sql)) {
            echo '
            <!DOCTYPE html>
            <html lang="zh-Hant">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>註冊成功</title>
                <style>
				
				
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f4f4f4;
                        margin: 0;
                        padding: 0;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                    }
                    .message-container {
                        text-align: center;
                        background-color: #fff;
                        padding: 20px;
                        border-radius: 10px;
                        box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
                    }
                    h1 {
                        color: #28a745;
                    }
                    .countdown {
                        font-size: 24px;
                        font-weight: bold;
                        color: #ff5722;
                    }
                </style>
            </head>
            <body>
                <div class="message-container">
                    <h1>註冊成功！</h1>
                    <p>您將在 <span id="countdown" class="countdown">5</span> 秒內自動跳轉到登入頁面。</p>
                </div>
                <script>
                    var countdown = document.getElementById("countdown");
                    var seconds = 5;
                    var interval = setInterval(function() {
                        seconds--;
                        countdown.textContent = seconds;
                        if (seconds <= 0) {
                            clearInterval(interval);
                            window.location.href = "登入.html";
                        }
                    }, 1000);
                </script>
            </body>
            </html>';
        } else {
            echo "插入記錄失敗: " . mysqli_error($db_link) . "<br>";
        }
    }
}

// 關閉連接
mysqli_close($db_link);
?>

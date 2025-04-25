<?php
session_start(); // 啟動瀏覽器暫存機制（用來記住登入狀態）

// 如果沒有登入，就跳出提醒並帶去登入頁
if (!isset($_SESSION['帳號'])) {
    echo "<script>alert('請先登入！'); window.location.href = '登入.html';</script>";
    exit();
}

// 取得目前登入的帳號
$帳號 = $_SESSION['帳號'];

/* -------- 連線到資料庫 -------- */
$servername = "localhost:3307"; // 主機位址
$username   = "root";           // 帳號
$password   = "3307";           // 密碼
$dbname_預支 = "基金會";          // 資料庫名稱

// 建立連線
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

// 若連線失敗就停止並顯示原因
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

/* -------- 查詢登入者的基本資料 -------- */
$職位查詢 = "
    SELECT 員工編號, 部門, 權限管理
    FROM 註冊資料表
    WHERE 帳號 = '$帳號'
    LIMIT 1
";
$職位_result_使用者 = $db_link_預支->query($職位查詢);

// 預先給變數一個空值，避免查不到資料時出現錯誤
$員工編號 = "";
$部門     = "";
$職位名稱 = "";
$上限     = 0;
$下限     = 0;

// 如果有查到資料，就把值存進變數
if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row      = $職位_result_使用者->fetch_assoc();
    $員工編號 = $row["員工編號"];
    $部門     = $row["部門"];
    $職位名稱 = $row["權限管理"];
}

/* -------- 查詢該職位對應的金額上下限 -------- */
$範圍_sql = "
    SELECT 上限, 下限
    FROM 職位設定表
    WHERE 職位名稱 = '$職位名稱'
    LIMIT 1
";
$範圍_result = $db_link_預支->query($範圍_sql);

if ($範圍_result && $範圍_result->num_rows > 0) {
    $範圍_data = $範圍_result->fetch_assoc();
    $上限 = $範圍_data["上限"];
    $下限 = $範圍_data["下限"];
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>申請頁面</title>

    <!-- 網頁配色與版面設定 -->
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            width: 100%;
            height: 100%;
            font-family: "Noto Sans TC", Arial, sans-serif;
            background-color: #f5d3ab; /* 淺杏色背景 */
            color: #5a4a3f;             /* 深咖字體 */
        }

        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 70px;
            text-align: center;
            height: 100%;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-shadow: 2px 2px 5px rgba(0,0,0,0.2);
        }

        /* 公用按鈕樣式 */
        .btn {
            width: 320px;
            padding: 15px;
            margin: 15px 0;
            background-color: #5a4a3f;
            color: #f5d3ab;
            border: none;
            border-radius: 50px;
            font-size: 1.2rem;
            text-decoration: none;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .btn:hover {
            background-color: #f5d3ab;
            color: #5a4a3f;
            border: 2px solid #5a4a3f;
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0,0,0,0.3);
        }

        /* 頁首橫幅 */
        .banner {
            width: 100%;
            background: linear-gradient(to bottom, #fbe3c9, #f5d3ab);
            color: #5a3d2b;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .banner .right { display: flex; gap: 20px; align-items: center; }

        .banner a {
            color: #5a3d2b;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2em;
        }
        .banner a:hover { color: #007bff; }
    </style>
</head>
<body>

<!-- 頁首：顯示部門、員工編號、登入者帳號與登出連結 -->
<div class="banner">
    <div class="right">
        <?php echo htmlspecialchars($部門); ?> - <?php echo htmlspecialchars($員工編號); ?>
    </div>
    <div class="right">
        <span>歡迎，<?php echo htmlspecialchars($帳號); ?>！</span>
        <a href="登出.php">登出</a>
    </div>
</div>

<!-- 主要內容：四個功能按鈕 -->
<div class="container">
    <h1>線上申請表單</h1>

    <!-- 按鈕：預支 -->
    <a href="7html.php" class="btn">預支申請</a>

    <!-- 按鈕：支出核銷／報帳 -->
    <a href="0228html.php" class="btn">支出核銷 / 報帳</a>

    <!-- 按鈕：查看紀錄 -->
    <a href="申請紀錄.php" class="btn">查看申請紀錄</a>

    <!-- 按鈕：輸出圖表 -->
    <a href="0307html.php" class="btn">輸出圖表</a>

    <div class="footer">
        &copy; 2024 財團法人台北市失親兒福利基金會
    </div>
</div>

</body>
</html>

<?php
session_start(); // 啟動 Session
if (!isset($_SESSION['帳號'])) {
    echo "<script>alert('請先登入！'); window.location.href = '登入.html';</script>";
    exit();
}
$帳號 = $_SESSION['帳號']; // 獲取登入的帳號

// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到資料庫
$dbname_預支 = "預支";
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

$dbname_review = "Review_comments";
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

$dbname_註冊 = "註冊"; 
$db_link_註冊 = new mysqli($servername, $username, $password, $dbname_註冊);

$dbname_職位設定 = "職位";
$db_link_職位設定 = new mysqli($servername, $username, $password, $dbname_職位設定);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 Review_comments 資料庫失敗: " . $db_link_review->connect_error);
}

if ($db_link_註冊->connect_error) { 
    die("註冊資料庫連線失敗: " . $db_link_註冊->connect_error); 
}

if ($db_link_職位設定->connect_error) { 
    die("職位設定連線失敗: " . $db_link_職位設定->connect_error); 
}

// 取得登入者資訊
$帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 員工編號, 部門, 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link_註冊->query($職位查詢);

$員工編號 = "";
$部門 = "";
$職位名稱 = "";
$上限 = 0;
$下限 = 0;

if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $員工編號 = $row["員工編號"];
    $部門 = $row["部門"];
    $職位名稱 = $row["權限管理"];
}

// 讀取對應職位的上限與下限
$範圍_sql = "SELECT 上限, 下限 FROM 職位設定表 WHERE 職位名稱 = '$職位名稱' LIMIT 1";
$範圍_result = $db_link_職位設定->query($範圍_sql);

if ($範圍_result && $範圍_result->num_rows > 0) {
    $範圍_data = $範圍_result->fetch_assoc();
    $上限 = $範圍_data["上限"];
    $下限 = $範圍_data["下限"];
}


?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>申請頁面</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
         body {
            height: 100%;
            width: 100%;
            font-family: "Noto Sans TC", Arial, sans-serif;
            background-color: #f5d3ab;
            color: #5a4a3f;
        }
        .container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100%;
            text-align: center;
            padding: 70px;
        }
        h1 {
            font-size: 2.5rem;
            margin-bottom: 30px;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
        }
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
            transition: all 0.3s ease-in-out;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.2);
        }
        .btn:hover {
            background-color: #f5d3ab;
            color: #5a4a3f;
            border: 2px solid #5a4a3f;
            transform: translateY(-5px);
            box-shadow: 0 6px 8px rgba(0, 0, 0, 0.3);
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9rem;
            opacity: 0.8;
        }
		.banner .overlay {
			position: absolute;
			top: 0;
			left: 0;
			right: 0;
			bottom: 0;
			background: rgba(0, 0, 0, 0.3); /* 使背景更加暗淡 */
			z-index: -1;
		}
		 .banner {
			width:100%;
            background: linear-gradient(to bottom, #fbe3c9, #f5d3ab); /* 漸層效果 */
            color: #5a3d2b;
            display: flex;
            justify-content: space-between;/* 左右對齊 */
            align-items: center;
            padding: 10px 20px;
			
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2); /* 陰影效果 */
        }
		
		
		
        .banner a {
            color: #5a3d2b;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.2em;
        }

        .banner a:hover {
    color: #007bff; /* 當滑鼠懸停時變換顏色 */
}


.banner .left {
    text-align: left;
	gap: 20px;
}
.banner .right {
    display: flex;
    gap: 20px; /* 按鈕間距 */
    align-items: center;
}

.banner a:hover {
    color: #007bff;
}
    </style>
</head>
<body>



<div class='banner'>
    <div class='right'><?php echo htmlspecialchars($部門); ?> - <?php echo htmlspecialchars($員工編號); ?></div>
    <div class='right'>
        <span>歡迎，<?php echo htmlspecialchars($帳號); ?>！</span> 
        <a href='登出.php'>登出</a>
    </div>
</div>






<div class="container">
    <h1>線上申請表單</h1>
    <a href="7html.php" class="btn">預支申請</a>
    <a href="0302html.php" class="btn">支出核銷 / 報帳</a>
    <a href="申請紀錄.php" class="btn">查看申請紀錄</a>
    <div class="footer">
        &copy; 2024 財團法人台北市失親兒福利基金會
    </div>
</div>
</body>
</html>
<?php
session_start(); // 啟動 Session
if (!isset($_SESSION['帳號'])) {
    echo "<script>alert('請先登入！'); window.location.href = '登入.html';</script>";
    exit();
}
$帳號 = $_SESSION['帳號']; // 獲取登入的帳號
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
            justify-content: flex-end;
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
    </style>
</head>
<body>
<div class="banner">
    <span>歡迎，<?php echo htmlspecialchars($帳號); ?>！</span> <!-- 顯示登入的帳號 -->
    <a href="登出.php" style="align-items: right;">登出</a>
</div>
<div class="container">
    <h1>線上申請表單</h1>
    <a href="ll1.php" class="btn">預支申請</a>
    <a href="綜合.html" class="btn">支出核銷 / 報帳</a>
    <a href="申請紀錄.php" class="btn">查看申請紀錄</a>
    <div class="footer">
        &copy; 2024 財團法人台北市失親兒福利基金會
    </div>
</div>
</body>
</html>
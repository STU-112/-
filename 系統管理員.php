
<?php
session_start(); // 啟動 Session
if (!isset($_SESSION['帳號'])) {
    echo "<script>alert('請先登入！'); window.location.href = '登入.html';</script>";
    exit();
}
$帳號 = $_SESSION['帳號']; // 獲取登入的帳號
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>系統管理員</title>
    <style>
        body {
		
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #d86e4a;
        }
        .button-container {
            display: flex;
            justify-content: center;
            gap: 20px;
            margin-top: 20px;
        }
        .button {
            background-color: #d86e4a;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }
        .button:hover {
            background-color: #c75d3a;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            padding: 10px;
            background-color: #f2f2f2;
        }
		
		
		.banner {
            
            background-color: #f2f2f2;
            color: #333;
            display: flex;
            justify-content: flex-end;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
        }
        .banner a {
            color: #5a3d2b;
            text-decoration: none;
            font-weight: bold;
			text-align:left;
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
    <a href="登出.php"	>登出</a>
</div>
    <div class="container">
        <h1>系統管理員</h1>

        <div class="button-container">
            <button class="button" onclick="location.href='管理註冊名單.php'">已註冊帳號密碼</button>
            <button class="button" onclick="location.href='註冊html.php'">新增使用者</button>
			<button class="button" onclick="location.href='新增職位設定.php'">職位設定</button>
        </div>
    </div>

    <div class="footer">
        <p>© 2024 台北市失親兒基金會</p>
    </div>

</body>
</html>

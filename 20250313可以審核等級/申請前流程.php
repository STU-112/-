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

        .banner {
            
            background-color: #f2f2f2;
            color: #333;
            display: flex;
            justify-content: flex-start;
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
			padding: 5px 20px;
        }
    </style>
	
	<div class='banner'>
        <a style='align-items: left;' onclick='history.back()'>◀</a>
		<div sytle='justify-content: flex-end;'>歡迎，" . htmlspecialchars($current_user) . "！</div>
    </div>
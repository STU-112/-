<?php
session_start(); // 先開 Session：瀏覽器才知道你有沒有登入

// 如果還沒登入，跳小視窗提醒，接著導回登入頁
if (!isset($_SESSION['帳號'])) {
    echo "<script>alert('請先登入！'); window.location.href = '登入.html';</script>";
    exit();
}

$帳號 = $_SESSION['帳號']; // 這裡把登入的帳號存進變數，之後可以顯示「歡迎 XXX」
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>申請頁面</title>

    <style>
    /* ===== 網頁整體排版與配色 ===== */
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
        width: 100%;
        height: 100%;
        font-family: "Noto Sans TC", Arial, sans-serif; /* 字型 */
        background-color: #f5d3ab; /* 淺杏色背景 */
        color: #5a4a3f;           /* 主要文字顏色 */
    }

    .container {                 /* 中央區塊：按鈕們 */
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
        text-shadow: 2px 2px 5px rgba(0,0,0,0.2);
    }

    .btn {                       /* 共用按鈕樣式 */
        width: 320px;
        padding: 15px;
        margin: 15px 0;
        background-color: #5a4a3f; /* 深咖底 */
        color: #f5d3ab;            /* 字體淺色 */
        border: none;
        border-radius: 50px;
        font-size: 1.2rem;
        text-decoration: none;
        cursor: pointer;
        transition: 0.3s;
        box-shadow: 0 4px 6px rgba(0,0,0,0.2);
    }
    .btn:hover {                 /* 滑過去的反差效果 */
        background-color: #f5d3ab;
        color: #5a4a3f;
        border: 2px solid #5a4a3f;
        transform: translateY(-5px);
        box-shadow: 0 6px 8px rgba(0,0,0,0.3);
    }

    .footer {                    /* 頁尾小字 */
        margin-top: 20px;
        font-size: 0.9rem;
        opacity: 0.8;
    }

    /* ===== 頁首橫幅 ===== */
    .banner {
        width: 100%;
        background: linear-gradient(to bottom, #fbe3c9, #f5d3ab); /* 上淺下深的漸層 */
        color: #5a3d2b;
        display: flex;
        justify-content: flex-start;  /* 左對齊，方便放返回箭頭 */
        align-items: center;
        padding: 10px 20px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.2);
    }

    .banner a {                  /* 橫幅裡的連結（返回鍵） */
        color: #5a3d2b;
        text-decoration: none;
        font-weight: bold;
        font-size: 1.2em;
        padding: 5px 20px;
    }
    .banner a:hover { color: #007bff; } /* 滑過變藍 */
    </style>
</head>

<body>
    <!-- 頁首：左邊一顆「返回」箭頭，右邊顯示歡迎詞 -->
    <div class='banner'>
        <a onclick='history.back()'>◀</a> <!-- 點一下就回上一頁 -->
        <div style='margin-left:auto;'>歡迎，<?php echo htmlspecialchars($帳號); ?>！</div>
    </div>

    <!-- 主要內容：之後可加表單或按鈕 -->
    <div class="container">
        <h1>線上申請表單</h1>

        <!-- 這裡可放多個連結按鈕，供使用者選擇功能
             範例：
             <a href="xxx.php" class="btn">某功能</a>
        -->

        <div class="footer">
            &copy; 2024 財團法人台北市失親兒福利基金會
        </div>
    </div>
</body>
</html>

<?php
session_start();

// 假設登入時已經在 session 裡設定：$_SESSION['帳號'] 與 $_SESSION['user_id']
// 這裡檢查是否登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 測試html.php");
    exit;
}

// 產生隨機 Token (用於防止他人只憑網址就能上傳)
$uploadToken = bin2hex(random_bytes(16));

// 存到 session，等等後端會和這個比對
$_SESSION['upload_token'] = $uploadToken;
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>上傳圖檔與 CSV 檔案</title>
    <style>
        :root {
            --primary-color: #4A90E2;
            --secondary-color: #FF6B6B;
            --background-color: #F0F4F8;
            --card-background-color: rgba(255, 255, 255, 0.95);
            --input-border-color: #CCCCCC;
            --input-focus-border-color: var(--primary-color);
            --button-background-color: var(--primary-color);
            --button-secondary-color: var(--secondary-color);
            --button-hover-background-color: #357ABD;
            --button-hover-secondary: #D64545;
            --text-color: #333333;
            --border-color: #DDDDDD;
            --font-family: 'Poppins', sans-serif;
        }

        body {
            font-family: var(--font-family);
            background-color: var(--background-color);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }

        .container {
            width: 100%;
            max-width: 800px;
            padding: 40px;
            background: var(--card-background-color);
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--border-color);
        }

        h1 {
            text-align: center;
            color: var(--primary-color);
            font-size: 2.5rem;
            margin-bottom: 30px;
        }

        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            font-weight: 600;
            margin-bottom: 10px;
            color: var(--text-color);
        }

        input[type="file"],
        input[type="text"] {
            padding: 12px;
            font-size: 1rem;
            border-radius: 8px;
            border: 1px solid var(--input-border-color);
            transition: border-color 0.3s, box-shadow 0.3s;
        }

        input[type="file"]:focus,
        input[type="text"]:focus {
            border-color: var(--input-focus-border-color);
            box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
            outline: none;
        }

        .button-container {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }

        button {
            padding: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s, transform 0.2s;
            width: 48%;
        }

        button[type="submit"] {
            background-color: var(--button-background-color);
        }

        button[type="submit"]:hover {
            background-color: var(--button-hover-background-color);
        }

        button.secondary {
            background-color: var(--button-secondary-color);
        }

        button.secondary:hover {
            background-color: var(--button-hover-secondary);
        }
    </style>
    <script>
        function handleUploadChoice(action) {
            if (action === 'no-upload') {
                document.getElementById('no-upload-form').submit();
            }
        }
    </script>
</head>
<body>
    <div class="container">
        <h1>上傳圖檔與 CSV 檔案</h1>
        <!-- 注意：action 指向後端處理檔測試.php -->
        <form action="測試.php" method="post" enctype="multipart/form-data">
            
            <!-- 隱藏欄位放入上面生成的 Token -->
            <input type="hidden" name="upload_token" value="<?= htmlspecialchars($uploadToken) ?>">

            <div class="form-group">
                <label for="image">選擇圖檔 (JPEG, PNG, GIF)：</label>
                <input type="file" name="image" id="image" accept="image/jpeg, image/png, image/gif" required>
            </div>
            <div class="form-group">
                <label for="csv">選擇 CSV 檔：</label>
                <input type="file" name="csv" id="csv" accept=".csv" required>
            </div>
            <div class="form-group">
                <label for="單據張數">單據張數：</label>
                <input type="text" id="單據張數" name="單據張數" placeholder="請輸入單據張數" required>
            </div>
            <div class="button-container">
                <button type="submit" name="upload">上傳</button>
                <button type="button" class="secondary" onclick="handleUploadChoice('no-upload')">不需要上傳</button>
            </div>
        </form>

        <!-- 不需要上傳時的表單 -->
        <form id="no-upload-form" action="測試.php" method="post" style="display: none;">
            <!-- 一樣要帶上 Token，避免外部直接POST進來 -->
            <input type="hidden" name="upload_token" value="<?= htmlspecialchars($uploadToken) ?>">
            <input type="hidden" name="no-upload" value="true">
        </form>
    </div>
</body>
</html>

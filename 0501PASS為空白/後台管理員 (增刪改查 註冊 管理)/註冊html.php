<?php  
// session_start();

// 檢查是否已登入
// if (!isset($_SESSION['帳號'])) {
    // header("Location: 註冊html.php");
    // exit;
// }

// 獲取用戶帳號
// $current_user = $_SESSION['帳號'];

$servername = "localhost:3307"; 
$username = "root"; 
$password = " "; 
$dbname_職位 = "基金會"; 

$db_link_職位 = new mysqli($servername, $username, $password, $dbname_職位);

// 檢查連線 
if ($db_link_職位->connect_error) { 
    die("職位資料庫連線失敗: " . $db_link_職位->connect_error); 
}

// 讀取職位選單
$職位_sql = "SELECT 職位名稱 FROM 職位";
$職位_result = $db_link_職位->query($職位_sql);

$職位選項 = "";
if ($職位_result->num_rows > 0) {
    while ($row = $職位_result->fetch_assoc()) {
        $職位選項 .= "<option value='" . htmlspecialchars($row["職位名稱"]) . "'>" . htmlspecialchars($row["職位名稱"]) . "</option>";
    }
}



// 讀取職位選單
$部門_sql = "SELECT 部門名稱 FROM 部門";
$部門_result = $db_link_職位->query($部門_sql);

$部門選項 = "";
if ($部門_result->num_rows > 0) {
    while ($row = $部門_result->fetch_assoc()) {
        $部門選項 .= "<option value='" . htmlspecialchars($row["部門名稱"]) . "'>" . htmlspecialchars($row["部門名稱"]) . "</option>";
    }
}

// 關閉資料庫連線
$db_link_職位->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>使用者註冊</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #FFF4C1 0%, #FFF8D7 50%, #FFFCEC 100%);
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 20px;
        }

        .form-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            max-width: 700px;
            width: 100%;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: bold;
        }

        input[type="text"], input[type="password"], select {
            width: 100%;
            padding: 14px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-sizing: border-box;
            font-size: 16px;
        }

        input[type="text"]:focus, input[type="password"]:focus, select:focus {
            border-color: #80bdff;
            outline: none;
            box-shadow: 0 0 5px rgba(128, 189, 255, 0.5);
        }

        .button-container {
            display: flex;
            justify-content: space-between;
        }

        button {
            width: 48%;
            padding: 14px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            transition: background-color 0.3s ease;
            color: #fff;
        }

        .submit-btn {
            background-color: #28a745;
        }

        .reset-btn {
            background-color: #dc3545;
        }

        button:hover {
            opacity: 0.9;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h1>新增使用者帳密</h1>
    <form action="註冊.php" method="POST">
        <label for="employee_id">員工編號:</label>
        <input type="text" id="employee_id" name="員工編號" placeholder="輸入員工編號" required>
        
        <label for="name">姓名:</label>
        <input type="text" id="name" name="姓名" placeholder="輸入姓名" required>

        <label for="phone">電話:</label>
        <input type="text" id="phone" name="電話" placeholder="輸入電話號碼" required>

        <label for="address">地址:</label>
        <input type="text" id="address" name="地址" placeholder="輸入地址">

        <label for="department">部門:</label>
        <select id="department" name="部門" required>
            <option value="">選擇部門</option>
            <?php echo $部門選項; ?>
        </select>

        <label for="position">職位:</label>
        <select id="position" name="職位" required>
            <option value="">選擇職位</option>
            <?php echo $職位選項; ?>
        </select>

        <label for="username">帳號:</label>
        <input type="text" id="username" name="帳號" placeholder="創建帳號" required>

        <label for="password">密碼:</label>
        <input type="password" id="password" name="密碼" placeholder="創建密碼" required oninput="checkPasswordStrength()">
        <div class="password-hint">密碼須包含大小寫字母、數字和特殊字符，長度至少 8 個字符。</div>
        <div id="password-strength" class="password-strength"></div>

        <div class="button-container">
            <button type="submit" class="submit-btn">提交</button>
            <button type="reset" class="reset-btn">清除</button>
        </div>
    </form>
</div>

<script>
    function checkPasswordStrength() {
        const passwordInput = document.getElementById('password');
        const strengthDisplay = document.getElementById('password-strength');
        const password = passwordInput.value;

        let strength = 0;
        if (password.length >= 8) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[\W_]/.test(password)) strength++; // 檢測特殊字符

        if (strength < 3) {
            strengthDisplay.textContent = '密碼強度：弱';
            strengthDisplay.className = 'password-strength weak';
        } else if (strength < 5) {
            strengthDisplay.textContent = '密碼強度：中';
            strengthDisplay.className = 'password-strength medium';
        } else {
            strengthDisplay.textContent = '密碼強度：強';
            strengthDisplay.className = 'password-strength strong';
        }
    }
</script>

</body>
</html>

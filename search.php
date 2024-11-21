<?php
// 連接資料庫
$server = 'localhost:3307'; // 伺服器名稱
$用戶名 = 'root'; // 用戶名
$密碼 = ' '; // 密碼
$資料庫 = '預支'; // 資料庫名稱

// 建立資料庫連線
$連接 = mysqli_connect($server, $用戶名, $密碼, $資料庫);

// 檢查連線是否成功
if (!$連接) {
    die("連接失敗: " . mysqli_connect_error());
}

// 初始化變數
$單號 = "";
$搜尋結果 = [];

// 搜尋邏輯
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['search'])) {
    $單號 = mysqli_real_escape_string($連接, $_POST['search']);
    $sql = "SELECT * FROM pay_table WHERE count LIKE '%$單號%'";
    $result = mysqli_query($連接, $sql);

    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // 過濾掉值為 NULL 或空字串的欄位
            $filteredRow = array_filter($row, function($value) {
                return !is_null($value) && $value !== '';
            });
            $搜尋結果[] = $filteredRow;
        }
    }

    // 清空搜尋欄位，避免重複結果
    $單號 = "";
}

// 關閉資料庫連線
mysqli_close($連接);
?>

<!DOCTYPE html>
<html lang="zh">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>單號搜尋</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 80%;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        h1 {
            text-align: center;
            color: #333;
        }
        form {
            margin-bottom: 20px;
            text-align: center;
        }
        input[type="text"] {
            padding: 8px;
            width: 300px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        input[type="submit"] {
            padding: 8px 16px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        input[type="submit"]:hover {
            background-color: #0056b3;
        }
        .result {
            margin-top: 20px;
        }
        .result-item {
            padding: 10px;
            background-color: #fafafa;
            border: 1px solid #ddd;
            margin-bottom: 10px;
        }
        .result-item p {
            margin: 5px 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>單號搜尋</h1>

    <!-- 搜尋表單 -->
    <form action="" method="POST">
        <input type="text" name="search" placeholder="輸入單號搜尋" value="<?= htmlspecialchars($單號) ?>" required>
        <input type="submit" value="搜尋">
    </form>

    <!-- 顯示搜尋結果 -->
    <div class="result">
        <?php if (!empty($搜尋結果)): ?>
            <?php foreach ($搜尋結果 as $item): ?>
                <div class="result-item">
                    <?php foreach ($item as $key => $value): ?>
                        <p><strong><?= htmlspecialchars($key) ?>:</strong> <?= htmlspecialchars($value) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
        <?php elseif ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
            <p>未找到符合的單號。</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>

<?php
// 連接資料庫
$server = 'localhost:3307'; // 伺服器名稱
$用戶名 = 'root'; // 用戶名
$密碼 = '3307'; // 密碼
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
    
    $sql = "
    SELECT 
        b.count AS 單號,
        b.受款人,
        b.填表日期,
        b.付款日期,
        s.支出項目,
        s.活動名稱,
        s.專案日期,
        s.獎學金人數,
        s.專案名稱,
        s.主題,
        s.獎學金日期,
        s.經濟扶助,
        s.其他項目,
        d.說明,
        p.支付方式,
        p.金額,
        p.簽收日,
        p.銀行郵局,
        p.分行,
        p.戶名,
        p.帳號,
        p.票號,
        p.到期日,
        p.結餘繳回
    FROM 
        基本資料 AS b
    LEFT JOIN 
        支出項目 AS s ON b.count = s.count
    LEFT JOIN 
        說明 AS d ON b.count = d.count
    LEFT JOIN 
        支付方式 AS p ON b.count = p.count
    WHERE b.count = ?";
    
    $stmt = mysqli_prepare($連接, $sql);
    mysqli_stmt_bind_param($stmt, 's', $單號); // 綁定單號
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($result && mysqli_num_rows($result) > 0) {
        while ($row = mysqli_fetch_assoc($result)) {
            // 過濾掉值為 NULL 或空字串的欄位
            $搜尋結果[] = array_filter($row, function($value) {
                return !is_null($value) && $value !== '';
            });
        }
    } else {
        $搜尋結果 = ["訊息" => "查無資料"];
    }
    
    mysqli_stmt_close($stmt);
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
    :root {
        /* 色彩定義 */
        --primary-color: #4A90E2; /* 天藍色 */
        --secondary-color: #50E3C2; /* 淺綠色 */
        --accent-color-1: #F5A623; /* 橙色 */
        --accent-color-2: #9013FE; /* 紫色 */
        --background-color: #F0F4F8; /* 淺灰背景 */
        --card-background-color: rgba(255, 255, 255, 0.95); /* 半透明白色 */
        --input-border-color: #CCCCCC; /* 淺灰邊框 */
        --input-focus-border-color: var(--primary-color); /* 聚焦邊框 */
        --button-background-color: var(--primary-color); /* 按鈕主色 */
        --button-hover-background-color: #357ABD; /* 按鈕懸停色 */
        --label-color: #333333; /* 深灰標籤 */
        --text-color: #333333; /* 深灰文字 */
        --border-color: #DDDDDD; /* 淺灰邊框 */
        --error-color: red; /* 錯誤訊息紅色 */
        --transition-speed: 0.3s;
        --font-family: 'Poppins', sans-serif;
    }

    * {
        box-sizing: border-box;
    }

    body {
        font-family: var(--font-family);
        background-color: var(--background-color);
        background-image: url('background-pattern.png'); /* 背景圖案 */
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center;
        padding: 20px;
        margin: 0;
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 100%;
        color: var(--text-color);
    }

    .container {
        width: 100%;
        max-width: 800px;
        padding: 40px;
        background: var(--card-background-color);
        border-radius: 20px;
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        border: 1px solid var(--border-color);
        backdrop-filter: blur(10px);
        position: relative;
        overflow: hidden;
    }

    .container::before {
        content: '';
        position: absolute;
        top: -50px;
        right: -50px;
        width: 150px;
        height: 150px;
        background: var(--accent-color-1);
        border-radius: 50%;
        opacity: 0.2;
    }

    .container::after {
        content: '';
        position: absolute;
        bottom: -50px;
        left: -50px;
        width: 150px;
        height: 150px;
        background: var(--accent-color-2);
        border-radius: 50%;
        opacity: 0.2;
    }

    h1 {
        text-align: center;
        color: var(--primary-color);
        margin-bottom: 40px;
        font-size: 50px; /* 增大標題字體大小 */
        font-weight: 700;
    }

    form {
        display: flex;
        flex-direction: column;
        gap: 30px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    .form-group label {
        margin-bottom: 10px;
        font-weight: 600;
        color: var(--label-color); /* 保持標籤顏色為深灰 */
        font-size: 1.1rem;
    }

    input,
    select,
    textarea {
        padding: 16px 18px;
        font-size: 1rem;
        border-radius: 10px;
        border: 1px solid var(--input-border-color);
        background-color: #FFFFFF;
        transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
        width: 100%;
    }

    input::placeholder,
    select option,
    textarea::placeholder {
        color: #AAAAAA;
    }

    input:focus,
    select:focus,
    textarea:focus {
        border-color: var(--input-focus-border-color);
        box-shadow: 0 0 8px rgba(74, 144, 226, 0.5);
        outline: none;
    }

    input[type="checkbox"],
    input[type="radio"] {
        width: auto;
        margin-right: 10px;
    }

    .form-group .option-group {
        display: flex;
        align-items: center;
    }

    .form-group .option-group label {
        margin-right: 20px;
        font-weight: 500;
    }

    .conditional-group {
        display: none;
        padding: 25px;
        border: 1px solid var(--border-color);
        border-radius: 10px;
        background-color: #FFFFFF;
        margin-top: 20px;
        transition: all var(--transition-speed) ease;
    }

    button {
        padding: 16px 18px;
        font-size: 1.1rem;
        font-weight: 600;
        color: #FFFFFF;
        background-color: var(--button-background-color);
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: background-color var(--transition-speed), transform 0.2s;
        width: 100%;
    }

    button:hover {
        background-color: var(--button-hover-background-color);
        transform: translateY(-3px);
    }

    /* 統一勾選欄位的樣式 */
    .form-group input[type="checkbox"],
    .form-group input[type="radio"] {
        margin-right: 10px;
    }

    .form-group .option-group {
        display: flex;
        align-items: center;
    }

    .form-group .option-group label {
        margin-right: 20px;
        font-weight: 500;
    }

    /* 錯誤訊息樣式 */
    .error-message {
        color: var(--error-color);
        font-size: 0.9rem;
        margin-top: 5px;
        display: none;
    }

    /* 響應式設計 */
    @media (max-width: 768px) {
        .container {
            padding: 40px 30px;
        }

        h1 {
            font-size: 2rem; /* 調整小螢幕下的標題字體大小 */
        }
    }

    @media (max-width: 480px) {
        .container {
            padding: 10px 20px;
        }

        h1 {
            font-size: 1.8rem; /* 進一步調整標題字體大小 */
        }
    }

    /* 模態框樣式 */
    .modal {
        display: none; /* 隱藏模態框 */
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        overflow: auto;
        background-color: rgba(0,0,0,0.5); /* 半透明背景 */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .modal-content {
        background-color: #fff;
        padding: 20px;
        border-radius: 10px;
        width: 90%;
        max-width: 400px;
        text-align: center;
        position: relative;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
    }

    .modal-content h2 {
        margin-top: 0;
        color: var(--primary-color);
        font-size: 1.5rem;
    }

    .modal-content p {
        color: var(--text-color);
        margin: 20px 0;
        font-size: 1rem;
    }

    .modal-content .modal-buttons {
        display: flex;
        justify-content: center;
    }

    .modal-content button {
        padding: 10px 20px;
        font-size: 1rem;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        margin: 0 10px;
    }
	.form-row {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }

    .modal-content .close-btn {
        background-color: var(--button-background-color);
        color: #fff;
    }

    .modal-content .close-btn:hover {
        background-color: var(--button-hover-background-color);
    }

    .success-modal {
        /* 與一般模態框相同樣式 */
    }

    /* 新增錯誤邊框樣式 */
    .input-error {
        border-color: var(--error-color) !important;
        box-shadow: 0 0 8px rgba(255, 0, 0, 0.2);
    }
     </style>
</head>
<body>
    <div class="container">
        <h1>搜尋單號</h1>
        <form method="POST" action="">
            <input type="text" name="search" placeholder="輸入單號" value="<?php echo htmlspecialchars($單號); ?>" required>
            <button type="submit">搜尋</button>
        </form>
        <div>
            <h2>搜尋結果</h2>
			<form id="paymentForm" action="核銷.php" method="POST">
<?php if (!empty($搜尋結果)): ?>
    <table border="1">        
        <tbody>
            <?php foreach ($搜尋結果 as $row): ?>
                <?php foreach ($row as $key => $value): ?>
                    <tr>					
                       <th><?php echo htmlspecialchars($key); ?></th>					   
                        <th><?php echo htmlspecialchars($value);?></th>
                    </tr>
                <?php endforeach; ?>                
            <?php endforeach; ?>
        </tbody>
    </table>
	
	<?php
	echo "<div class='form-group'>
                        <label for='單號'>單號：<span class='required-star'>*</span></label>
                        <input type='text' id='單號' name='單號' value='" . htmlspecialchars($row['單號']) . "' required readonly>
                    </div>";?>		
				<div class="form-row">
               <div class="form-group">
                   <label for="金額">實支金額：<span class="required-star">*</span></label>
                   <input type="number" id="實支金額" name="實支金額" min="0" placeholder="請輸入預支後使用金額" required>
               </div>
               <div class="form-group">
                   <label for="金額">結餘：<span class="required-star">*</span></label>
                   <input type="number" id="結餘繳回" name="結餘繳回" min="0" placeholder="請輸入剩餘金額">
                            </div>
                        </div>
<?php else: ?>
    <p>請輸入單號進行搜尋。</p>
<?php endif; ?>
<button type="submit" id="submitBtn">提交表單</button>
      </form>

        </div>
    </div>
</body>
</html>
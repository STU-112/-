<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>查詢表單資料</title>
</head>
<body>

    <h1>查詢表單資料</h1>

    <!-- 搜尋單號表單 -->
    <form method="post" action="">
        <label for="單號">請輸入單號：</label>
        <input type="text" id="單號" name="單號" required>
        <button type="submit">查詢</button>
    </form>

    <?php
    // 資料庫連接
    $servername = "localhost:3307"; // 資料庫主機
    $username = "root"; // 資料庫使用者
    $password = "3307"; // 資料庫密碼
    $dbname = "預支"; // 資料庫名稱

    // 創建連接
    $conn = new mysqli($servername, $username, $password, $dbname);

    // 檢查連接
    if ($conn->connect_error) {
        die("連接失敗: " . $conn->connect_error);
    }

    // 檢查是否有搜尋請求
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $formNumber = $_POST['單號'];

        // 準備和綁定 SQL 查詢
        $stmt = $conn->prepare("
            SELECT 
                b.`count`,
                b.受款人,
                b.填表日期,
                b.付款日期,
                s.`count` AS 支出項目_count,
                s.支出項目,
                s.活動名稱,
                s.專案日期,
                s.獎學金人數,
                s.專案名稱,
                s.主題,
                s.獎學金日期,
                s.經濟扶助,
                s.其他項目,
                d.`count` AS 說明_count,
                d.說明,
                p.`count` AS 支付方式_count,
                p.支付方式,
                p.國字金額,
                p.國字金額_hidden,
                p.簽收金額,
                p.簽收人,
                p.簽收日,
                p.銀行郵局,
                p.分行,
                p.戶名,
                p.帳號,
                p.票號,
                p.到期日,
                p.預支金額
            FROM 
                基本資料 AS b
            LEFT JOIN 
                支出項目 AS s ON b.`count` = s.`count`
            LEFT JOIN 
                說明 AS d ON b.`count` = d.`count`
            LEFT JOIN 
                支付方式 AS p ON b.`count` = p.`count`
            WHERE 
                b.`count` = ?
        ");

        $stmt->bind_param("s", $formNumber); // 綁定變數到查詢
        $stmt->execute(); // 執行查詢
        $result = $stmt->get_result(); // 獲取結果集

        // 檢查是否有結果
        if ($result->num_rows > 0) {
            echo "<h2>查詢結果:</h2>";
            // 輸出每行資料
            while ($row = $result->fetch_assoc()) {
                echo "<div class='form-group'>
                        <label for='受款人'>受款人姓名：<span class='required-star'>*</span></label>
                        <input type='text' id='受款人' name='受款人' value='" . htmlspecialchars($row['受款人']) . "' required>
                    </div>";

                echo "<div class='form-group'>
                        <label for='填表日期'>填表日期：<span class='required-star'>*</span></label>
                        <input type='text' id='填表日期' name='填表日期' value='" . htmlspecialchars($row['填表日期']) . "' required>
                    </div>";

                echo "<div class='form-group'>
                        <label for='付款日期'>付款日期：<span class='required-star'>*</span></label>
                        <input type='text' id='付款日期' name='付款日期' value='" . htmlspecialchars($row['付款日期']) . "' required>
                    </div>";

                echo "<div class='form-group'>
                        <label for='支出項目'>支出項目：<span class='required-star'>*</span></label>
                        <input type='text' id='支出項目' name='支出項目' value='" . htmlspecialchars($row['支出項目']) . "' required>
                    </div>";

                echo "<div class='form-group'>
                        <label for='國字金額'>國字金額：<span class='required-star'>*</span></label>
                        <input type='text' id='國字金額' name='國字金額' value='" . htmlspecialchars($row['國字金額']) . "' required>
                    </div>";
            }
        } else {
            echo "找不到該單號的資料";
        }

        $stmt->close(); // 關閉預備語句
    }

    $conn->close(); // 關閉資料庫連接
    ?>
</body>
</html>

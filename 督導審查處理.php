<?php
// 建立資料庫連線
$servername = "localhost:3307"; 
$username = "root"; 
$password = "3307"; 
$dbname = "預支"; 

// 建立連線
$db_link = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($db_link->connect_error) { 
    die("連線失敗: " . $db_link->connect_error); 
}

// 檢查是否有表單提交
$search_count = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["count"])) {
    $search_count = $_POST["count"];
}

// 查詢資料
if (!empty($search_count)) {
    // 合併查詢語句
$sql = "
SELECT 
    b.`count`,
    b.受款人,
    b.填表日期,
	b.付款日期,
	s.`count`,
    s.支出項目,
	s.活動名稱,
	s.專案日期,
	s.獎學金人數,
	s.專案名稱,
	s.主題,
	s.獎學金日期,
	s.經濟扶助,
	s.其他項目 ,
	d.`count`,
    d.說明,
	p.`count`,
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
    支出項目 AS s ON b.`count` = s.`count`
LEFT JOIN 
    說明 AS d ON b.`count` = d.`count`
LEFT JOIN 
    支付方式 AS p ON b.`count` = p.`count`
WHERE 
    b.`count` = ?";

    $stmt = $db_link->prepare($sql);
    $stmt->bind_param("s", $search_count);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}

// 處理表單提交（通過或不通過）
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status']) && isset($_POST['serial_count'])) {
    $status = $_POST['status'];  // 審核狀態（通過/不通過）
    $opinion = $_POST['opinion'];  // 審核意見
    $serial_count = $_POST['serial_count'];  // 單號

    // 查詢金額
    $sql = "SELECT 國字金額 FROM pay_table WHERE count = ?";
    $stmt = $db_link->prepare($sql);
    $stmt->bind_param("s", $serial_count);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // 取得金額
    $amount = $row['國字金額'];

    // 這裡不再依金額判斷，而是統一傳送給出納
    $sql_update = "UPDATE pay_table SET status = ?, opinion = ?, next_audit = '出納' WHERE count = ?";

    // 更新資料庫
    $stmt_update = $db_link->prepare($sql_update);
    $stmt_update->bind_param("sss", $status, $opinion, $serial_count);
    $stmt_update->execute();

    // 完成後的跳轉或訊息
    if ($stmt_update->affected_rows > 0) {
        echo "<script>alert('審核已完成，資料已轉交給出納。'); window.location.href = '督導審核處理.php';</script>";
    } else {
        echo "<script>alert('更新失敗，請重試。');</script>";
    }
}


// 檢查是否有查詢結果並顯示資料
if ($result && $result->num_rows > 0) {
    // 欄位名稱與顯示名稱的對應
    $field_names = [
        "count" => "單號",
        "填表人" => "填表人",
		"受款人" => "受款人",
        "填表日期" => "填表日期",
        "付款日期" => "付款日期",
        "支出項目" => "支出項目",
        "專案日期" => "專案日期",
        "獎學金人數" => "獎學金人數",
        "專案名稱" => "專案名稱",
        "主題" => "主題",
        "獎學金日期" => "獎學金日期",
        "經濟扶助" => "經濟扶助",
        "其他項目" => "其他項目",
        "說明" => "說明",
        "支付方式" => "支付方式",
        "金額" => "金額",
        "簽收人" => "簽收人",
        "簽收日" => "簽收日",
        "銀行郵局" => "銀行/郵局",
        "分行" => "分行",
        "戶名" => "戶名",
        "帳號" => "帳號",
        "票號" => "票號",
        "到期日" => "到期日",
        "結餘繳回" => "結餘繳回"
    ];
    echo "
    <form method='post' action='督導審核意見.php'>
	<style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            height: 100%;
            width: 100%;
            font-family: 'Noto Sans TC', Arial, sans-serif;
            background: linear-gradient(to bottom, #e8dff2, #f5e8fc);
            color: #333;
        }
        /* 表格樣式 */
        table {
            width: 50%;
            margin: 20px auto;
            border-collapse: collapse;
            font-family: 'Segoe UI', Tahoma, sans-serif;
            background-color: #ffffff;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        table, th, td {
            border: 2px solid #e0e0e0;
        }
        th {
            background-color: #DEFFAC;
            color: black;
            font-weight: bold;
            text-align: center;
            padding: 12px;
        }
        td {
            text-align: left;
            padding: 12px;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:nth-child(odd) {
            background-color: #ffffff;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        caption {
            font-size: 1.6em;
            font-weight: bold;
            margin: 15px;
            color: #333;
        }
        textarea {
            width: 95%;
            height: 80px;
            margin: 15px auto;
            resize: none;
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 10px;
            font-size: 1em;
        }
        .button-container {
            text-align: center;
            margin-top: 15px;
        }
        button {
            padding: 10px 15px;
            margin: 5px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            color: white;
            background-color: #4CAF50;
        }
        button[type='button'] {
            background-color: #999;
        }
        button:hover {
            background-color: #45a049;
        }
        button[type='button']:hover {
            background-color: #666;
        }
		.banner {
            width: 100%;
            background: linear-gradient(to bottom, #e8dff2, #f5e8fc); /* 淡紫色漸層 */
            color: #333;
            display: flex;
           justify-content: flex-start;
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
    
    <table>
    <caption>檢視申請項目</caption>";
	 echo "
    <div class='banner'>
        <a style='align-items: left;' href='督導.php'>◀</a>
    </div>";

   while ($row = $result->fetch_assoc()) {
        foreach ($row as $key => $value) {
            if (!empty($value)) {
                // 如果欄位有對應名稱，使用對應名稱，否則使用原欄位名
                $display_name = isset($field_names[$key]) ? $field_names[$key] : $key;
                echo "<tr>
                    <th>" . htmlspecialchars($display_name) . "</th>
                    <td>" . htmlspecialchars($value) . "</td>
                </tr>";
            }
        }

        // 新增意見欄位
        echo "<tr>
            <td colspan='2'>
                <textarea name='opinion' placeholder='請輸入您的意見'></textarea>
            </td>
        </tr>
        <tr>
            <td colspan='2' style='text-align: center;'>
                <input type='hidden' name='serial_count' value='" . htmlspecialchars($row['count']) . "'>
				<button type='button' onclick='history.back()'>返回</button>
                <button type='submit' name='status' value='通過'>通過</button>
                <button type='submit' name='status' value='不通過'>不通過</button>
            </td>
        </tr>";
    }
    echo "</table>
    </form>";
} else {
    echo "<p>無法找到相關資料。</p>";
}

$db_link->close();
?>

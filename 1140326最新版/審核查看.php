<?php
// 建立資料庫連線
$servername = "localhost:3307"; 
$username = "root"; 
$password = "3307"; 
$dbname = "0228"; 

// 建立連線
$db_link = new mysqli($servername, $username, $password, $dbname);

// 檢查連線
if ($db_link->connect_error) { 
    die("連線失敗: " . $db_link->connect_error); 
}

// 檢查是否有表單提交
$search_count = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["受款人代號"])) {
    $search_count = $_POST["受款人代號"];
}



   
if (!empty($search_count)) {
    // 合併查詢語句 
	
	
    include '審查處理sql.php';
    
	
	if ($stmt = $db_link->prepare($sql)) {
        $stmt->bind_param("s", $search_count);
        $stmt->execute();
        $result = $stmt->get_result();
    } else {
        die("SQL 錯誤: " . $db_link->error);
    }
}


// 處理表單提交（通過或不通過）
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status']) && isset($_POST['serial_count'])) {
    $status = $_POST['status'];  // 審核狀態（通過/不通過）
    $opinion = $_POST['opinion'];  // 審核意見
    $serial_count = $_POST['serial_count'];  // 單號

    // 查詢金額
    $sql = "SELECT 金額 FROM 經辦人交易檔 WHERE 受款人代號 = ?";
    $stmt = $db_link->prepare($sql);
    $stmt->bind_param("s", $serial_count);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // 取得金額
    $amount = $row['金額'];

    // 這裡不再依金額判斷，而是統一傳送給出納
    $sql_update = "UPDATE 經辦人交易檔 SET status = ?, opinion = ?, next_audit = '出納' WHERE 受款人代號 = ?";

    // 更新資料庫
    $stmt_update = $db_link->prepare($sql_update);
    $stmt_update->bind_param("sss", $status, $opinion, $serial_count);
    $stmt_update->execute();

    // 完成後的跳轉或訊息
    if ($stmt_update->affected_rows > 0) {
        echo "<script>alert('審核已完成，資料已轉交給出納。'); window.location.href = '審核人.php';</script>";
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
        "簽收日" => "簽收日",
        "銀行郵局" => "銀行/郵局",
        "分行" => "分行",
        "戶名" => "戶名",
        "帳號" => "帳號",
        "票號" => "票號",
        "到期日" => "到期日",
    ];
    echo "
    <form method='post' action='審核人審核意見.php'>
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
			background: linear-gradient(to bottom, #e8dff2, #f5e8fc); /* 淡紫色漸層 */
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
	
    <div class='banner'>
        <a style='align-items: left;' onclick='history.back()'>◀</a>
    </div>
	
    
    <table>
    <caption>檢視申請項目</caption>";

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
    }
    echo "</table> 
	</form>";
} else {
    echo "<p>無法找到相關資料。</p>";
}


$stmt->close();
$db_link->close();

?>
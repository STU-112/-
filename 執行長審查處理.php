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
    $sql ="SELECT count,受款人,填表日期,付款日期,支出項目,活動名稱,專案日期,獎學金人數,專案名稱,主題,獎學金日期,經濟扶助,其他項目,說明,支付方式,國字金額,國字金額_hidden,簽收金額,簽收人,簽收日,銀行郵局,分行,戶名,帳號,票號,到期日,預支金額 FROM pay_table WHERE count = ?";
    $stmt = $db_link->prepare($sql);
    $stmt->bind_param("s", $search_count);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}

// 檢查是否有查詢結果並顯示資料
if ($result && $result->num_rows > 0) {
    // 欄位名稱與顯示名稱的對應
    $field_names = [
        "count" => "單號",
        "受款人" => "受款人",
        "填表日期" => "填表日期",
        "付款日期" => "付款日期",
        "支出項目" => "支出項目",
        "活動名稱" => "活動名稱",
        "專案日期" => "專案日期",
        "獎學金人數" => "獎學金人數",
        "專案名稱" => "專案名稱",
        "主題" => "主題",
        "獎學金日期" => "獎學金日期",
        "經濟扶助" => "經濟扶助",
        "其他項目" => "其他項目",
        "說明" => "說明",
        "支付方式" => "支付方式",
        "國字金額" => "金額",
        "國字金額_hidden" => "國字金額",
        "簽收金額" => "簽收金額",
        "簽收人" => "簽收人",
        "簽收日" => "簽收日",
        "銀行郵局" => "銀行/郵局",
        "分行" => "分行",
        "戶名" => "戶名",
        "帳號" => "帳號",
        "票號" => "票號",
        "到期日" => "到期日",
        "預支金額" => "預支金額"
    ];
    echo "
    <form method='post' action='執行長審核意見.php'>
    <style>
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
    </style>
    
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

        // 新增意見欄位
        echo "<tr>
            <td colspan='2'>
                <textarea name='opinion' placeholder='請輸入您的意見'></textarea>
            </td>
        </tr>
        <tr>
            <td colspan='2' class='button-container'>
                <input type='hidden' name='serial_count' value='" . htmlspecialchars($row["count"]) . "'>
                <button type='button' onclick='history.back()'>返回</button>
                <button type='submit' name='status' value='通過' onclick='return confirm(\"確定通過審核嗎？\");'>通過</button>
                <button type='submit' name='status' value='不通過' onclick='return confirm(\"確定不通過審核嗎？\");'>不通過</button>
            </td>
        </tr>";
    }

    echo "</table>
    </form>";
}
// 釋放結果集 
if ($result) {
    $result->free(); 
}

// 關閉連線 
$db_link->close(); 
?>

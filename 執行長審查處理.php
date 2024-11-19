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
$search_serial_count = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["count"])) {
    $search_serial_count = $_POST["count"];
}

// 查詢資料
$result = false;
if (!empty($search_serial_count)) {
    $sql = "SELECT count, 受款人, 填表日期, 付款日期, 支出項目, 活動名稱, 專案日期, 獎學金人數, 專案名稱, 主題, 獎學金日期, 經濟扶助, 其他項目, 說明, 支付方式, 國字金額_hidden, 金額, 簽收金額, 簽收人, 簽收日, 銀行郵局, 分行, 戶名, 帳戶, 票號, 到期日, 預收金額 FROM pay_table WHERE count = ?";
    $stmt = $db_link->prepare($sql);
    $stmt->bind_param("s", $search_serial_count);
    $stmt->execute();
    $result = $stmt->get_result();
}

if ($result && $result->num_rows > 0) {
    echo "
    <form method='post' action='執行長審核意見.php'>
    <style>
        table {
            width: 80%;
            margin: 20px auto;
            border-collapse: collapse;
            font-family: Arial, sans-serif;
        }
        table, th, td {
            border: 1px solid #ddd;
        }
        th, td {
            padding: 12px;
            text-align: center;
        }
        th {
            background-color: #f2f2f2;
            color: #333;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        tr:hover {
            background-color: #f1f1f1;
        }
        caption {
            font-size: 1.5em;
            margin: 10px;
            font-weight: bold;
        }
        textarea {
            width: 90%;
            height: 100px;
            margin: 10px 0;
        }
    </style>
    
    <table>
    <caption>檢視申請細目</caption>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>count</td><td>" . $row["count"] . "</td></tr>";
        echo "<tr><td>受款人</td><td>" . $row["受款人"] . "</td></tr>";
        echo "<tr><td>填表日期td><td>" . $row["填表日期"] . "</td></tr>";
        echo "<tr><td>付款日期</td><td>" . $row["付款日期"] . "</td></tr>";
        echo "<tr><td>支出項目</td><td>" . $row["支出項目"] . "</td></tr>";
        echo "<tr><td>活動名稱</td><td>" . $row["活動名稱"] . "</td></tr>";
        echo "<tr><td>專案日期</td><td>" . $row["專案日期"] . "</td></tr>";
        echo "<tr><td>獎學金人數</td><td>" . $row["獎學金人數"] . "</td></tr>";
        echo "<tr><td>專案名稱</td><td>" . $row["專案名稱"] . "</td></tr>";
        echo "<tr><td>主題</td><td>" . $row["主題"] . "</td></tr>";
        echo "<tr><td>獎學金日期</td><td>" . $row["獎學金日期"] . "</td></tr>";
        echo "<tr><td>經濟扶助</td><td>" . $row["經濟扶助"] . "</td></tr>";
        echo "<tr><td>其他項目</td><td>" . $row["其他項目"] . "</td></tr>";
        echo "<tr><td>說明</td><td>" . $row["說明"] . "</td></tr>";
		echo "<tr><td>支付方式</td><td>" . $row["支付方式"] . "</td></tr>";
		echo "<tr><td>國字金額_hidden</td><td>" . $row["國字金額_hidden"] . "</td></tr>";
		echo "<tr><td>金額</td><td>" . $row["金額"] . "</td></tr>";
		echo "<tr><td>簽收金額</td><td>" . $row["簽收金額"] . "</td></tr>";
		echo "<tr><td>簽收人</td><td>" . $row["簽收人"] . "</td></tr>";
		echo "<tr><td>簽收日</td><td>" . $row["簽收日"] . "</td></tr>";
		echo "<tr><td>銀行郵局</td><td>" . $row["銀行郵局"] . "</td></tr>";
		echo "<tr><td>分行</td><td>" . $row["分行"] . "</td></tr>";
		echo "<tr><td>戶名</td><td>" . $row["戶名"] . "</td></tr>";
		echo "<tr><td>帳戶</td><td>" . $row["帳戶"] . "</td></tr>";
		echo "<tr><td>票號</td><td>" . $row["票號"] . "</td></tr>";
		echo "<tr><td>到期日</td><td>" . $row["到期日"] . "</td></tr>";
		echo "<tr><td>預收金額</td><td>" . $row["預收金額"] . "</td></tr>";
		
		
		
        // 新增意見欄位
        echo "<tr><td>執行長審核意見</td><td>
            <textarea name='opinion' placeholder='請輸入您的意見'></textarea>
        </td></tr>";

        // 提交按鈕區塊
        echo "<tr><td colspan='2'>
            <input type='hidden' name='serial_count' value='" . $row["serial_count"] . "'>
            <input type='hidden' name='review_status' value=''>
            <button type='button' onclick='history.back()'>返回</button>
			
			
             <button type='submit' name='status' value='通過'>通過</button>
        <button type='submit' name='status' value='不通過'>不通過</button>
        </td></tr>";
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

<script>
// 設置審核狀態
function setStatus(status) {
    document.querySelector('input[name=\"review_status\"]').value = status;
}
</script>

<?php
// 建立資料庫連線 
$servername = "localhost:3307"; 
$username = "root"; 
$password = "3307"; 
$dbname = "op2"; 

// 建立連線 
$db_link = new mysqli($servername, $username, $password, $dbname); 

// 檢查連線 
if ($db_link->connect_error) { 
    die("連線失敗: " . $db_link->connect_error); 
}

// 檢查是否有表單提交
$search_serial_count = '';
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["serial_count"])) {
    $search_serial_count = $_POST["serial_count"];
}

// 查詢資料
if (!empty($search_serial_count)) {
    $sql = "SELECT serial_count, form_type, amount, fillDate, recipient, expenditure, projectName, paymentMethod, accountName, bankName, checkNumber, dueDate, reason FROM pay_table WHERE serial_count = ?";
    $stmt = $db_link->prepare($sql);
    $stmt->bind_param("s", $search_serial_count);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = false;
}

if ($result && $result->num_rows > 0) {
    echo "
    <form method='post' action='督導審核意見.php'>
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
    <caption>檢視申請項目</caption>";

    while ($row = $result->fetch_assoc()) {
        echo "<tr><td>單號</td><td>" . $row["serial_count"] . "</td></tr>";
        echo "<tr><td>表單</td><td>" . $row["form_type"] . "</td></tr>";
        echo "<tr><td>金額</td><td>" . $row["amount"] . "</td></tr>";
        echo "<tr><td>日期</td><td>" . $row["fillDate"] . "</td></tr>";
        echo "<tr><td>受款人</td><td>" . $row["recipient"] . "</td></tr>";
        echo "<tr><td>支出項目</td><td>" . $row["expenditure"] . "</td></tr>";
        echo "<tr><td>專案名稱</td><td>" . $row["projectName"] . "</td></tr>";
        echo "<tr><td>付款方式</td><td>" . $row["paymentMethod"] . "</td></tr>";
        echo "<tr><td>帳戶名稱</td><td>" . $row["accountName"] . "</td></tr>";
        echo "<tr><td>銀行名稱</td><td>" . $row["bankName"] . "</td></tr>";
        echo "<tr><td>支票號碼</td><td>" . $row["checkNumber"] . "</td></tr>";
        echo "<tr><td>到期日</td><td>" . $row["dueDate"] . "</td></tr>";
        echo "<tr><td>事由</td><td>" . $row["reason"] . "</td></tr>";
        
		
	
		
        // 新增意見欄位
       echo "<tr><td colspan='2'>
        <input type='hidden' name='serial_count' value='" . $row["serial_count"] . "'>
        <textarea name='opinion' placeholder='請輸入您的意見'></textarea><br>
        <button type='button' onclick='history.back()'>返回</button>
        <button type='submit' name='status' value='通過' onclick='return confirm(\"確定通過審核嗎？\");'>通過</button>
        <button type='submit' name='status' value='不通過' onclick='return confirm(\"確定不通過審核嗎？\");'>不通過</button>
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

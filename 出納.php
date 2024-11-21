<?php 
// 建立資料庫連線 
$servername = "localhost:3307"; // 資料庫伺服器名稱 
$username = "root"; // 資料庫使用者 
$password = "3307"; // 資料庫密碼 (單一空格) 
$dbname = "op2"; // 資料庫名稱 

// 建立連線 
$db_link = new mysqli($servername, $username, $password, $dbname); 

// 檢查連線 
if ($db_link->connect_error) { 
    die("連線失敗: " . $db_link->connect_error); 
} 

// 查詢金額在 1001 到 5000 之間的資料 
$sql = "SELECT serial_count, form_type, amount, fillDate, recipient FROM pay_table"; 

$result = $db_link->query($sql);

// 顯示資料 
if ($result && $result->num_rows > 0) { 
    echo "
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
            cursor: pointer; /* 指針變為手指 */
        }
        caption {
            font-size: 1.5em;
            margin: 10px;
            font-weight: bold;
        }
    </style>
    <script>
        // 控制按鈕的顯示和可用狀態
        function controlButtons(amount, userDepartment, department, approveBtn, rejectBtn) {
            if (amount <= 5000 && userDepartment === department) {
                // 金額 <= 5000 且部門相同，所有按鈕可用
                approveBtn.disabled = false;
                rejectBtn.disabled = false;
            } else if (amount > 5000 && userDepartment !== department) {
                // 金額 > 5000 且部門不同，只有上呈按鈕可用
                approveBtn.disabled = false;
                rejectBtn.disabled = true;
            } else if (amount > 5000) {
                // 金額 > 5000 且部門相同，只有上呈按鈕可用
                approveBtn.disabled = false;
                rejectBtn.disabled = true;
            } else if (amount <= 5000 && userDepartment !== department) {
                // 金額 <= 5000 且部門不同，允許通過和不通過
                approveBtn.disabled = false;
                rejectBtn.disabled = false;
            }
        }

        // 當按下「通過」按鈕時，顯示提示訊息
        function approveAction(amount, userDepartment, department) {
            if (amount <= 5000 && userDepartment === department) {
                alert('權限內，金額低於 5000 通過');
            } else if (amount > 5000 && userDepartment !== department) {
                alert('權限過大，跨部門上呈');
            } else if (amount > 5000) {
                alert('權限過大上呈');
            } else if (amount < 5000 && userDepartment !== department) {
                alert('低於 5000 塊以下，跨部門上呈');
            }
        }

        // 當按下「不通過」按鈕時，顯示提示訊息
        function rejectAction(amount) {
            if (amount <= 5000) {
                alert('金額低於 5000，但選擇不通過');
            }
        }

        // 行點擊事件，跳轉到指定頁面
        function redirectToPage() {
            window.location.href = 'testtest.php'; // 跳轉到 testtest.php
        }
</script>
    ";
    
    echo "<table>";
    echo "<caption>主任審核</caption>";
    
    // 顯示欄位名稱
    echo "<tr>";
    while ($field = $result->fetch_field()) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "<th>動作</th>"; // 增加一個動作欄位
    echo "</tr>";

    // 顯示每一行資料 
    while($row = $result->fetch_assoc()) { 
       echo "<tr onclick='redirectToPage()'>"; // 將點擊事件加在這裡
        echo "<td>" . $row["serial_count"] . "</td>"; 
        echo "<td>" . $row["form_type"] . "</td>";
        echo "<td>" . $row["amount"] . "</td>";
        echo "<td>" . $row["fillDate"] . "</td>";
        echo "<td>" . $row["recipient"] . "</td>"; 
        // 添加操作按鈕
        echo "<td>
		 <form method='post' action='明細.php'>
		<input type='hidden' name='審查' value='" . $row["serial_count"] . "'>
                <button type='submit'>審查</button>
		
              </td>";
        echo "</tr>"; 
    } 
    echo "</table>";
} else { 
    echo "<p style='text-align:center;'>無資料顯示</p>"; 
}

// 釋放結果集 
if ($result) {
    $result->free(); 
}

// 關閉連線 
$db_link->close(); 
?>
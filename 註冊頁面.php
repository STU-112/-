<?php  
// 資料庫連線設定
$servername = "localhost:3307"; // 資料庫伺服器名稱 
$username = "root"; // 資料庫使用者 
$password = "3307"; // 資料庫密碼 
$dbname = "老師註冊"; // 資料庫名稱

// 建立連線 
$db_link = new mysqli($servername, $username, $password, $dbname);

// 檢查連線 
if ($db_link->connect_error) { 
    die("連線失敗: " . $db_link->connect_error); 
} 

// 處理通過或不通過的操作
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['status_action'])) {
    $userId = $_POST['使用者id'];
    $status = $_POST['status_action']; // '通過' 或 '不通過'
    
    // 更新狀態的 SQL 語句
    $update_sql = "UPDATE 註冊資料表 SET 狀態 = ? WHERE 使用者id = ?";
    $stmt = $db_link->prepare($update_sql);
    $stmt->bind_param("si", $status, $userId); // 假設 狀態 是字符串類型，使用者id 是整數型
    if ($stmt->execute()) {
        echo "<script>alert('狀態更新成功！');</script>";
    } else {
        echo "<script>alert('錯誤: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// 查詢所有使用者資料
$sql = "SELECT 使用者id, 姓名, 電話, 地址, 部門, 職位, 帳號, 密碼, 狀態 FROM 註冊資料表";
$result = $db_link->query($sql);

// 顯示資料 
if ($result && $result->num_rows > 0) { 
    echo "
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100%;
            background-color: #f8f8f8;
            overflow-x: hidden;
        }
        table {
            width: 90%;
            max-width: 1200px;
            border-collapse: collapse;
            margin: 20px auto;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        caption {
            font-size: 2em;
            margin: 10px;
            font-weight: bold;
            color: #d86e4a;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border: 1px solid #ddd;
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
        tr {
            transition: background-color 0.3s;
        }
        input[type='text'],
        input[type='password'],
        select {
            width: 90%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #fff;
            color: #333;
            font-size: 14px;
            margin: 5px 0;
        }
        .center-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .center-buttons button {
            display: flex;
            padding: 6px 12px;
            font-size: 15px;
            color: white;
            background-color: #d86e4a;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s;
        }
        .center-buttons button:hover {
            background-color: #c75d3a;
        }

        .permission-column {
            width: 200px;
        }
        .position-column {
            width: 150px;
        }
    </style>";

    echo "<table>";
    echo "<caption>帳號密碼</caption>";
    
    echo "<tr>";
    while ($field = $result->fetch_field()) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "<th style='width: 10%';>狀態操作</th>";
    echo "</tr>";

    // 顯示資料
    while($row = $result->fetch_assoc()) { 
        echo "<tr>"; 
        echo "<td><input type='text' value='" . $row["使用者id"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["姓名"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["電話"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["地址"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["部門"] . "' readonly></td>"; 
        echo "<td class='position-column'><input type='text' value='" . $row["職位"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["帳號"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["密碼"] . "' readonly></td>"; // Change to text to show password
        
        echo "<td class='permission-column'>
                <form method='POST'>
                    <input type='hidden' name='使用者id' value='" . $row["使用者id"] . "'>
                    <button type='submit' name='status_action' value='通過' onclick='return confirm(\"確定要通過這個使用者嗎？\");'>通過</button>
                    <button type='submit' name='status_action' value='不通過' onclick='return confirm(\"確定要不通過這個使用者嗎？\");'>不通過</button>
                </form>
              </td>";
        
        echo "</tr>"; 
    } 
    echo "</table>";
} else { 
    echo "<tr><td colspan='10'>無資料顯示</td></tr>";
} 

$result->free(); 
$db_link->close(); 
?>


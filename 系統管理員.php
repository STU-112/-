<?php  
// Establishing database connection 
$servername = "localhost:3307"; // Database server name 
$username = "root"; // Database user 
$password = " "; // Database password (note: empty space was removed)
$dbname = "註冊"; // Database name

// Establishing connection 
$db_link = new mysqli($servername, $username, $password, $dbname);

// Check connection 
if ($db_link->connect_error) { 
    die("Connection failed: " . $db_link->connect_error); 
} 
// 刪除使用者
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $userId = $_POST['使用者id'];
    $delete_sql = "DELETE FROM 註冊資料表 WHERE 使用者id = ?";
    $stmt = $db_link->prepare($delete_sql);
    $stmt->bind_param("i", $userId); // 假設 使用者id 是整數型
    if ($stmt->execute()) {
        echo "<script>alert('使用者刪除成功！');</script>";
    } else {
        echo "<script>alert('錯誤: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}
$sql = "SELECT 使用者id, 姓名, 電話, 地址, 部門, 職位, 帳號, 密碼 FROM 註冊資料表";
$result = $db_link->query($sql);

// Display data 
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
            height: 100vh;
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
        select {
            appearance: none;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"16\" height=\"16\" fill=\"none\" viewBox=\"0 0 16 16\"><path fill=\"%23d86e4a\" d=\"M4.3 6.3L8 10l3.7-3.7L12 5l-4 4-4-4z\"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px center;
            background-size: 12px;
            transition: border-color 0.3s;
        }
        select:hover, select:focus {
            border-color: #d86e4a;
            outline: none;
        }
        .center-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }
        .center-buttons button {
            padding: 8px 12px;
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
    echo "<th style='width:10%'>權限設置</th>";
    echo "<th style='width:20%'>編輯</th>";
    echo "</tr>";

    while($row = $result->fetch_assoc()) { 
        echo "<tr>"; 
        echo "<td><input type='text' value='" . $row["使用者id"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["姓名"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["電話"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["地址"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["部門"] . "' readonly></td>"; 
        echo "<td class='position-column'><input type='text' value='" . $row["職位"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["帳號"] . "' readonly></td>"; 
        echo "<td><input type='text' value='" . $row["密碼"] . "' readonly></td>";
        
		 
		
        echo "<td class='permission-column'>
                <select disabled>
                    <option value='經辦人'>經辦人</option>
                    <option value='部門主管(督導)'>部門主管(督導)</option>
                    <option value='主任'>主任</option>
                    <option value='執行長'>執行長</option>
                    <option value='會計'>會計</option>
                    <option value='出納'>出納</option>
                    <option value='董事長'>董事長</option>
                </select>
              </td>";
        
        echo "<td>
                <div class='center-buttons'>
                    <button onclick='editRow(this)'>修改</button>
                    <button onclick='confirmRow(this)'>確定</button>
                    <form method='POST'>
                        <input type='hidden' name='使用者id' value='" . $row["使用者id"] . "'>
                        <button type='submit' name='delete_user' onclick='return confirm(\"你確定要刪除此使用者嗎？\");'>刪除帳號</button>
                    </form>
                </div>
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

<script>
function editRow(button) {
    const row = button.closest("tr");
    const inputs = row.querySelectorAll("input");
    inputs.forEach(input => {
        input.removeAttribute("readonly");
    });
    const select = row.querySelector("select");
    select.removeAttribute("disabled");
}

function confirmRow(button) {
    const row = button.closest("tr");
    const inputs = row.querySelectorAll("input");
    inputs.forEach(input => {
        input.setAttribute("readonly", "readonly");
    });
    const select = row.querySelector("select");
    select.setAttribute("disabled", "disabled");
}

function clearRow(button) {
    const row = button.closest("tr");
    const inputs = row.querySelectorAll("input[type='text']");
    inputs.forEach(input => {
        input.value = ""; // Clear the text inputs
    });
    const select = row.querySelector("select");
    if (select) {
        select.selectedIndex = 0; // Reset select to the first option
    }
}
</script>

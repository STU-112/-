<?php  
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 管理註冊名單.php");
    exit;
}
// 獲取用戶帳號
$current_user = $_SESSION['帳號'];
$servername = "localhost:3307"; 
$username = "root"; 
$password = " "; 

$dbname = "基金會";

// Establishing connection 
$db_link_註冊 = new mysqli($servername, $username, $password, $dbname);

// Check connection 
if ($db_link_註冊->connect_error) { 
    die("註冊 failed: " . $db_link_註冊->connect_error); 
}

// Check connection 
if ($db_link_註冊->connect_error) { 
    die("職位 failed: " . $db_link_註冊->connect_error); 
}

// Check connection 
if ($db_link_註冊->connect_error) { 
    die("部門 failed: " . $db_link_註冊->connect_error); 
}

// Update user details in the database
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_user'])) {
    $員工編號 = $_POST['員工編號'];
	$name = $_POST['姓名'];
    $phone = $_POST['電話'];
    $address = $_POST['地址'];
    $department = isset($_POST['部門']) ? $_POST['部門'] : '';
    $departmentupdown = isset($_POST['職位']) ? $_POST['職位'] : '';
    $account = $_POST['帳號'];
    $password = $_POST['密碼'];
    $permission = isset($_POST['權限管理']) ? $_POST['權限管理'] : '';

    $update_sql = "UPDATE 註冊資料表 SET 員工編號 = ?,姓名 = ?, 電話 = ?, 地址 = ?, 部門 = ?, 職位 = ?, 密碼 = ?, 權限管理 = ? WHERE 帳號 = ?";
    $stmt = $db_link_註冊->prepare($update_sql);
    $stmt->bind_param("sssssssss", $員工編號,$name, $phone, $address, $department, $departmentupdown   , $password, $permission, $account);
    
    if ($stmt->execute()) {
        echo "<script>alert('資料更新成功！');</script>";
    } else {
        echo "<script>alert('錯誤: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// 刪除使用者
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
    $account = $_POST['帳號'];
    $delete_sql = "DELETE FROM 註冊資料表 WHERE 帳號 = ?";
    $stmt = $db_link_註冊->prepare($delete_sql);
    $stmt->bind_param("s", $account);
    if ($stmt->execute()) {
        echo "<script>alert('使用者刪除成功！');</script>";
    } else {
        echo "<script>alert('錯誤: " . $stmt->error . "');</script>";
    }
    $stmt->close();
}

// 讀取帳號管理資料
$sql = "SELECT 員工編號, 姓名, 電話, 地址, 部門, 職位, 帳號, 密碼, 權限管理 FROM 註冊資料表";
$result = $db_link_註冊->query($sql);

// 讀取職位選單
$職位_sql = "SELECT 職位名稱 FROM 職位";
$職位_result = $db_link_註冊->query($職位_sql);

$職位選項 = "";

if ($職位_result->num_rows > 0 && $result && $result->num_rows > 0) {
    while ($row_職位 = $職位_result->fetch_assoc()) {
		 $職位選項 .= "<option value='" . $row_職位["職位名稱"] . "'>" . $row_職位["職位名稱"] . "</option>";
    }
	
	
	
	
$部門_sql = "SELECT 部門名稱 FROM 部門";
$部門_result = $db_link_註冊->query($部門_sql);

$部門選項 = "";

if ($部門_result->num_rows > 0 && $result && $result->num_rows > 0) {
    while ($row_部門 = $部門_result->fetch_assoc()) {
		 $部門選項 .= "<option value='" . $row_部門["部門名稱"] . "'>" . $row_部門["部門名稱"] . "</option>";
    }
	echo "
<style>
    body {
        font-family: Arial, sans-serif;
        background-color: #f0f2f5;
        margin: 0px;
    }

    table {
		margin: 00px;
        width: 100%;
        table-layout: auto; /* Allow columns to adjust based on content */
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #fff;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }

    th, td {
        border: 1px solid #ddd;
        padding: 12px;
        text-align: center;
        word-wrap: break-word; /* Ensure content wraps if necessary */
    }

    th {
        background-color: #007bff;
        color: #fff;
    }

    tr:nth-child(even) {
        background-color: #f2f2f2;
    }

    tr:hover {
        background-color: #f1f1f1;
    }

    caption {
        margin: 10px 0;
        font-size: 24px;
        font-weight: bold;
        color: #333;
    }

    .center-buttons {
        display: flex;
        justify-content: space-between; /* Spread buttons evenly */
        gap: 2px; /* Reduced spacing between buttons */
    }

    .center-buttons button {
        flex: 1; /* Allow buttons to shrink or grow as needed */
        padding: 8px 10px;
        font-size: 14px;
        font-weight: bold;
        color: #ffffff;
        background-color: #007bff;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.2s, transform 0.1s;
    }

    .center-buttons button:hover {
        background-color: #0056b3;
        transform: scale(1.02);
    }

    input[type='text'] {
        width: 90%;
        padding: 6px;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
    }

    select {
        padding: 6px;
        border-radius: 4px;
        border: 1px solid #ddd;
        width: 100%; /* Adjusted for better display */
        display: none; /* 隱藏選單，直到修改時顯示 */
    }
	
	
	.banner {
            
            background-color: #f2f2f2;
            color: #333;
            display: flex;
            justify-content: flex-start;
            align-items: center;
            padding: 10px 20px;
            box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
			
        }
        .banner a {
            color: #5a3d2b;
            text-decoration: none;
            font-weight: bold;
			text-align:left;
            font-size: 1.2em;
			padding: 5px 20px;
        }
	
</style>
";



    echo "<table>";
	echo "<div class='banner'>
        <a style='align-items: left;' onclick='history.back()'>◀</a>
		<div sytle='justify-content: flex-end;'>歡迎，" . htmlspecialchars($current_user) . "！</div>
    </div>
";
	
    echo "<caption>帳號管理資料</caption>";
	// echo "<div sytle='justify-content: flex-end;'>歡迎，" . htmlspecialchars($current_user) . "！</div>";
    echo "<tr>";
    while ($field = $result->fetch_field()) {
        echo "<th>" . $field->name . "</th>";
    }
    echo "<th style='min-width: 300px;'>編輯</th>"; // Ensure the last column is wide enough
    echo "</tr>";

    while($row = $result->fetch_assoc()) { 
        echo "<tr>"; 
        echo "<form method='POST'>";
        echo "<td><input type='text' name='員工編號' value='" . $row["員工編號"] . "' readonly></td>";
		echo "<td><input type='text' name='姓名' value='" . $row["姓名"] . "' readonly></td>"; 
        echo "<td><input type='text' name='電話' value='" . $row["電話"] . "' readonly></td>"; 
        echo "<td><input type='text' name='地址' value='" . $row["地址"] . "' readonly></td>"; 
        
        // 部門輸入框和下拉選單
        echo "<td><input type='text' name='部門_display' value='" . $row["部門"] . "' readonly>
               <select name='部門' disabled><option> " . $部門選項 . "</option></select></td>";
        
		
		echo "<td><input type='text' name='職位_display' value='" . $row["職位"] . "' readonly>
		<select name='職位' disabled><option> " . $職位選項 . "</option></select></td>";
		
		
        echo "<td><input type='text' name='帳號' value='" . $row["帳號"] . "' readonly></td>"; 
        echo "<td><input type='text' name='密碼' value='" . $row["密碼"] . "' readonly></td>";

			  echo "<td><input type='text' name='權限管理_display' value='" . $row["權限管理"] . "' readonly>
		<select name='權限管理' disabled><option> " . $職位選項 . "</option></select></td>";
		
        
        echo "<td>
                <div class='center-buttons'>
                    <button type='button' onclick='editRow(this)'>修改</button>
                    <button type='submit' name='update_user'>確定</button>
                    <button type='submit' name='delete_user' onclick='return confirm(\"你確定要刪除此使用者嗎？\");'>清除帳號</button>
                </div>
              </td>";
        echo "</form>";
        echo "</tr>"; 
} }
    echo "</table>";
} else { 
    echo "<tr><td colspan='10'>無資料顯示</td></tr>";
}


$result->free();
$職位_result->free(); 
$db_link_註冊->close();

?>

<script>
function editRow(button) {
    const row = button.closest("tr");
    const inputs = row.querySelectorAll("input");
    inputs.forEach(input => {
        input.removeAttribute("readonly");
    });
    
    const selects = row.querySelectorAll("select");
    selects.forEach(select => {
        select.removeAttribute("disabled");
        select.style.display = 'block'; // 顯示下拉選單
    });
    
    row.querySelector("input[name='部門_display']").style.display = 'none'; // 隱藏部門文字框
    row.querySelector("input[name='職位_display']").style.display = 'none'; // 隱藏職位文字框
    row.querySelector("input[name='權限管理_display']").style.display = 'none'; // 隱藏權限管理文字框
}

function confirmRow(button) {
    const row = button.closest("tr");
    const inputs = row.querySelectorAll("input");
    inputs.forEach(input => {
        input.setAttribute("readonly", "readonly");
    });
    
    const selects = row.querySelectorAll("select");
    selects.forEach(select => {
        select.setAttribute("disabled", "disabled");
        select.style.display = 'none'; // 隱藏下拉選單
    });
    
    const departmentInput = row.querySelector("input[name='部門_display']");
    const positionInput = row.querySelector("input[name='職位_display']");
    const permissionInput = row.querySelector("input[name='權限管理_display']");
    
    departmentInput.style.display = 'block'; // 顯示部門文字框
    positionInput.style.display = 'block'; // 顯示職位文字框
    permissionInput.style.display = 'block'; // 顯示權限管理文字框
    
    departmentInput.value = row.querySelector("select[name='部門']").value; // 更新部門文字框顯示選擇的值
    positionInput.value = row.querySelector("select[name='職位']").value; // 更新職位文字框顯示選擇的值
    permissionInput.value = row.querySelector("select[name='權限管理']").value; // 更新權限管理文字框顯示選擇的值
}
</script>

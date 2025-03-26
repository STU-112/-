<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 審核人審查紀錄.php");
    exit;
}
// 獲取用戶帳號
$current_user = $_SESSION['帳號'];

// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到 op2 資料庫
$dbname_預支 = "基金會";

$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}


// 初始化搜尋條件
$search_serial = isset($_GET['search_serial']) ? $_GET['search_serial'] : '';
$search_item = isset($_GET['search_item']) ? $_GET['search_item'] : '';

$sql_註冊 = "SELECT 員工編號 FROM 註冊資料表 WHERE 帳號 = ?";
$stmt = $db_link_預支->prepare($sql_註冊);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$stmt->bind_result($員工編號);
$stmt->fetch();
$stmt->close();

// 取得登入者資訊
$帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 員工編號, 部門, 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link_預支->query($職位查詢);

$員工編號 = "";
$部門 = "";
$職位名稱 = "";
$當前職位編號 = 0;

if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $員工編號 = $row["員工編號"];
    $部門 = $row["部門"];
    $職位名稱 = $row["權限管理"];
}

// 讀取當前職位的編號
$職位編號查詢 = "SELECT 編號 FROM 職位 WHERE 職位名稱 = '$職位名稱' LIMIT 1";
$職位編號結果 = $db_link_預支->query($職位編號查詢);

if ($職位編號結果 && $職位編號結果->num_rows > 0) {
    $row = $職位編號結果->fetch_assoc();
    $當前職位編號 = $row["編號"];
}

// 取得上一級職位名稱
$上一級職位名稱 = "";
$上一個職位查詢 = "SELECT 職位名稱 FROM 職位 WHERE 編號 > $當前職位編號 ORDER BY 編號 ASC LIMIT 1";
$上一個職位結果 = $db_link_預支->query($上一個職位查詢);

if ($上一個職位結果 && $上一個職位結果->num_rows > 0) {
    $row = $上一個職位結果->fetch_assoc();
    $上一級職位名稱 = $row["職位名稱"];
}

//where
$whereCondition = "s.金額 IS NOT NULL";

// **只有部門主管才要篩選部門**
if ($職位名稱 == "部門主管") {
    $whereCondition .= " AND r.部門 = '$部門'";
}

// 修正查詢條件，確保只顯示填表人為登入者的員工編號
// 查詢預支資料
$sql = "
SELECT 
b.* ,
s.*,
d.*
	
FROM 
    受款人資料檔 AS b
LEFT JOIN 
    經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
LEFT JOIN 	
    經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
LEFT JOIN 
    註冊資料表 AS r ON d.經辦代號 = r.員工編號
WHERE 
    $whereCondition
";	

// 構建 SQL 查詢
$search_year = isset($_GET['search_year']) ? $_GET['search_year'] : '';
$search_month = isset($_GET['search_month']) ? $_GET['search_month'] : '';
$search_day = isset($_GET['search_day']) ? $_GET['search_day'] : '';

// 檢查搜尋條件
if (!empty($search_year) || !empty($search_month) || !empty($search_day)) {
    $sql .= " AND `交易單號` LIKE '%A%{$search_year}%{$search_month}%{$search_day}%'";
}

if (!empty($search_item)) {
    $sql .= " AND `支出項目` = '$search_item'";
}

$result = $db_link_預支->query($sql);
// 顯示資料
if ($result && $result->num_rows > 0) {
    include '審核人style.php';
	

	
echo "<div class='banner'>
        <a style='align-items: left;' onclick='history.back()'>◀</a>
		<div sytle='justify-content: flex-end;'>歡迎，" . htmlspecialchars($current_user) . "！</div>
    </div>";
	

    echo "<table>";
    echo "<caption>申請紀錄</caption>";
    echo "<tr>";
    echo "<th>單號</th><th>填表人</th><th>受款人</th><th>金額</th><th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>";
    echo "</tr>";
	
	// 顯示搜尋表單<label>單號: <input type='text' name='search_serial' value='$search_serial'></label>
echo "
<form method='get' style='text-align: center; margin-bottom: 20px;'>
    
    <label>支出項目:
        <select name='search_item'>
            <option value=''>-- 全部 --</option>
            <option value='活動費用'" . ($search_item == '活動費用' ? " selected" : "") . ">活動費用</option>
            <option value='獎學金'" . ($search_item == '獎助學金' ? " selected" : "") . ">獎學金</option>
            <option value='經濟扶助'" . ($search_item == '經濟扶助' ? " selected" : "") . ">經濟扶助</option>
            <option value='其他'" . ($search_item == '其他' ? " selected" : "") . ">其他</option>
        </select>
    </label>
    <button type='submit'>搜尋</button>
	<div >
	    <label>
        <select name='search_year'>
            <option value=''>-- 選擇年 --</option>
            ";
            // 假設年份範圍是2020到2030，根據需要調整
            for ($year = 2020; $year <= 2030; $year++) {
                echo "<option value='$year'" . ($search_year == $year ? " selected" : "") . ">$year</option>";
            }
            echo "
        </select>年
    </label>
    <label>
        <select name='search_month'>
            <option value=''>-- 選擇月 --</option>";
            for ($month = 1; $month <= 12; $month++) {
                echo "<option value='" . str_pad($month, 2, '0', STR_PAD_LEFT) . "'" . ($search_month == str_pad($month, 2, '0', STR_PAD_LEFT) ? " selected" : "") . ">" . str_pad($month, 2, '0', STR_PAD_LEFT) . "</option>";
            }
            echo"
        </select>月
    </label>
    <label>
        <select name='search_day'>
            <option value=''>-- 選擇日 --</option>
            ";
            for ($day = 1; $day <= 31; $day++) {
                echo "<option value='" . str_pad($day, 2, '0', STR_PAD_LEFT) . "'" . ($search_day == str_pad($day, 2, '0', STR_PAD_LEFT) ? " selected" : "") . ">" . str_pad($day, 2, '0', STR_PAD_LEFT) . "</option>";
            }
            echo "
        </select>日
    </label>
	</div>
</form>
";

    // 顯示每一行資料 
    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["受款人代號"];

        
        $sql_review_opinion = "SELECT 狀態 FROM 部門主管審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_預支->query($sql_review_opinion);

        // 預設狀態
        $status = "<span style='color: orange;'>待審核</span>";

        // 判斷部門主管審核狀態
		
		
        if ($review_result && $review_result->num_rows > 0) {
            $review_row = $review_result->fetch_assoc();
            $opinion = $review_row["狀態"];

            // 根據部門主管審核意見判斷狀態
            if ($opinion == "通過") {
				// if ($row["金額"] < 1000) {
				// $status = "<span style='color: green;'>會計審核中</span>";}
                // else{
				$status = "<span style='color: green;'>主任審核中</span>";
                
                // 查詢主任審核意見
                $sql_director_opinion = "SELECT 狀態 FROM 主任審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                $director_result = $db_link_預支->query($sql_director_opinion);
                if ($director_result && $director_result->num_rows > 0) {
                    $director_row = $director_result->fetch_assoc();
                    if ($director_row["狀態"] == "通過") {
                        $status = "<span style='color: green;'>執行長審核中</span>";
                        
                        // 查詢執行長審核意見
                        $sql_executive_opinion = "SELECT 狀態 FROM 執行長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                        $executive_result = $db_link_預支->query($sql_executive_opinion);
                        if ($executive_result && $executive_result->num_rows > 0) {
                            $executive_row = $executive_result->fetch_assoc();
                            if ($executive_row["狀態"] == "通過") {
                                $status = "<span style='color: green;'>董事長審核中</span>";
                                
                                // 查詢董事長審核意見
                                $sql_chairman_opinion = "SELECT 狀態 FROM 董事長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                $chairman_result = $db_link_預支->query($sql_chairman_opinion);
                                if ($chairman_result && $chairman_result->num_rows > 0) {
                                    $chairman_row = $chairman_result->fetch_assoc();
                                    if ($chairman_row["狀態"] == "通過") {
                                        $status = "<span style='color: green;'>會計審核中</span>";
                                        
                                        // 查詢會計審核意見
                                        $sql_accounting_opinion = "SELECT 狀態 FROM 會計審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                        $accounting_result = $db_link_預支->query($sql_accounting_opinion);
                                        if ($accounting_result && $accounting_result->num_rows > 0) {
                                            $accounting_row = $accounting_result->fetch_assoc();
                                            if ($accounting_row["狀態"] == "通過") {
                                                $status = "<span style='color: green;'>出納審核中</span>";
                                                
                                                // 查詢出納審核意見
                                                $sql_cashier_opinion = "SELECT 狀態 FROM 出納審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                                $cashier_result = $db_link_預支->query($sql_cashier_opinion);
                                                if ($cashier_result && $cashier_result->num_rows > 0) {
                                                    $cashier_row = $cashier_result->fetch_assoc();
                                                    if ($cashier_row["狀態"] == "通過") {
                                                        $status = "<span style='color: green;'>審核通過</span>";
                                                    } else {
                                                        $status = "<span style='color: red;'>出納不通過</span>";
                                                    }
                                                }
                                            } else {
                                                $status = "<span style='color: red;'>會計不通過</span>";
                                            }
                                        }
                                    } else {
                                        $status = "<span style='color: red;'>董事長不通過</span>";
                                    }
                                }
                            } else {
                                $status = "<span style='color: red;'>執行長不通過</span>";
                            }
                        }
                    } else {
                        $status = "<span style='color: red;'>主任不通過</span>";
                    }
                }
            } else {
                $status = "<span style='color: red;'>部門主管不通過</span>";
            }
        }

        echo "<tr class='second-row'>";
 	
		echo "<tr class='second-row'>";
        echo "<td>" . ($row["交易單號"]) . "</td>";
        echo "<td>" . ($row["經辦代號"]) . "</td>";
        echo "<td>" . ($row["受款人代號"]) . "</td>";
        echo "<td>" . ($row["金額"]) . "</td>";
        echo "<td>" . ($row["填表日期"]) . "</td>";
        echo "<td>" . ($row["支出項目"]) . "</td>";
		
        echo "<td>" . $status . "</td>"; // 顯示審核狀態
        echo "<td>
		<div style='display: flex; justify-content: center; align-items: center; height: 100xp;'>
		<div style='display: flex; gap: 10px;'>
            <form method='post' action='審核查看.php'>
                <input type='hidden' name='受款人代號' value='" . $row["受款人代號"] . "'>
                <button type='submit' name='review'>查看</button>
            </form>
			
			<form method='post' action='意見.php'>
                <input type='hidden' name='受款人代號' value='" . $row["受款人代號"] . "'>
                <button type='submit' name='意見'>意見</button>
            </form>
			</div>
			</div>
			
        </td>";
        echo "</tr>";

        
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>沒有資料可顯示。</p>";
}	

// 關閉資料庫連線
$db_link_預支->close();

?>

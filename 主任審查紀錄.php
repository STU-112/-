<?php
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到 op2 資料庫
$dbname_預支 = "預支";
$dbname_review = "review_comments"; // 假設 review_comments 資料庫名稱
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 review_comments 資料庫失敗: " . $db_link_review->connect_error);
}






// 初始化搜尋條件
$search_serial = isset($_GET['search_serial']) ? $_GET['search_serial'] : '';
$search_item = isset($_GET['search_item']) ? $_GET['search_item'] : '';

// 合併查詢語句
$sql = "
SELECT 
    b.`count`,
    b.受款人,
    b.填表日期,
    s.支出項目,
    d.說明,
    p.金額
FROM 
    基本資料 AS b
LEFT JOIN 
    支出項目 AS s ON b.`count` = s.`count`
LEFT JOIN 
    說明 AS d ON b.`count` = d.`count`
LEFT JOIN 
    支付方式 AS p ON b.`count` = p.`count`
WHERE 
    p.金額 >= 1000";

// 加入搜尋條件
if (!empty($search_serial)) {
    $sql .= " AND `count` LIKE '%$search_serial%'";
}
if (!empty($search_item)) {
    $sql .= " AND `支出項目` = '$search_item'";
}

$result = $db_link_預支->query($sql);







// 顯示資料
if ($result && $result->num_rows > 0) {
    echo "
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
        .header {
            display: flex;
            background-color: rgb(220, 236, 245);
        }
        .header nav {
            text-align: right;
            width: 100%;
            font-size: 100%;
            text-indent: 10px;
        }
        .header nav a {
            font-size: 30px;
            color: rgb(39, 160, 130);
            text-decoration: none;
            display: inline-block;
            line-height: 52px;
        }
        .header nav a:hover {
             background-color: #ffaa00;
        }
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
		tr.second-row {
    background-color: white; /* 固定背景顏色 */
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

    </style>";
		
 echo "
    <div class='banner'>
        <a style='align-items: left;' href='主任.php'>◀</a>
    </div>";
	
	
	

    echo "<table>";
    echo "<caption>主任已審查紀錄</caption>";
    echo "<tr>";
    echo "<th>單號</th><th>受款人</th><th>金額</th><th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>";
    echo "</tr>";
	
	// 顯示搜尋表單
echo "
<form method='get' style='text-align: center; margin-bottom: 20px;'>
    <label>單號: <input type='text' name='search_serial' value='$search_serial'></label>
    <label>支出項目:
        <select name='search_item'>
            <option value=''>-- 全部 --</option>
            <option value='活動費用'" . ($search_item == '活動費用' ? " selected" : "") . ">活動費用</option>
            <option value='獎學金'" . ($search_item == '獎學金' ? " selected" : "") . ">獎學金</option>
            <option value='經濟扶助'" . ($search_item == '經濟扶助' ? " selected" : "") . ">經濟扶助</option>
            <option value='其他'" . ($search_item == '其他' ? " selected" : "") . ">其他</option>
        </select>
    </label>
    <button type='submit'>搜尋</button>
</form>
";

    // 顯示每一行資料 
    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["count"];

        // 查詢 Review_comments 資料庫中的督導審核意見
        $sql_review_opinion = "SELECT 狀態 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_review->query($sql_review_opinion);

        // 預設狀態
        $status = "<span style='color: orange;'>待審核</span>";

        // 判斷督導審核狀態
        if ($review_result && $review_result->num_rows > 0) {
            $review_row = $review_result->fetch_assoc();
            $opinion = $review_row["狀態"];

            // 根據督導審核意見判斷狀態
            if ($opinion == "通過") {
                $status = "<span style='color: green;'>主任審核中</span>";
                
                // 查詢主任審核意見
                $sql_director_opinion = "SELECT 狀態 FROM 主任審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                $director_result = $db_link_review->query($sql_director_opinion);
                if ($director_result && $director_result->num_rows > 0) {
                    $director_row = $director_result->fetch_assoc();
                    if ($director_row["狀態"] == "通過") {
                        $status = "<span style='color: green;'>執行長審核中</span>";
                        
                        // 查詢執行長審核意見
                        $sql_executive_opinion = "SELECT 狀態 FROM 執行長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                        $executive_result = $db_link_review->query($sql_executive_opinion);
                        if ($executive_result && $executive_result->num_rows > 0) {
                            $executive_row = $executive_result->fetch_assoc();
                            if ($executive_row["狀態"] == "通過") {
                                $status = "<span style='color: green;'>董事長審核中</span>";
                                
                                // 查詢董事長審核意見
                                $sql_chairman_opinion = "SELECT 狀態 FROM 董事長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                $chairman_result = $db_link_review->query($sql_chairman_opinion);
                                if ($chairman_result && $chairman_result->num_rows > 0) {
                                    $chairman_row = $chairman_result->fetch_assoc();
                                    if ($chairman_row["狀態"] == "通過") {
                                        $status = "<span style='color: green;'>會計審核中</span>";
                                        
                                        // 查詢會計審核意見
                                        $sql_accounting_opinion = "SELECT 狀態 FROM 會計審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                        $accounting_result = $db_link_review->query($sql_accounting_opinion);
                                        if ($accounting_result && $accounting_result->num_rows > 0) {
                                            $accounting_row = $accounting_result->fetch_assoc();
                                            if ($accounting_row["狀態"] == "通過") {
                                                $status = "<span style='color: green;'>出納審核中</span>";
                                                
                                                // 查詢出納審核意見
                                                $sql_cashier_opinion = "SELECT 狀態 FROM 出納審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                                $cashier_result = $db_link_review->query($sql_cashier_opinion);
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
                $status = "<span style='color: red;'>督導不通過</span>";
            }
        }

        echo "<tr class='second-row'>";
        echo "<td>" . $row["count"] . "</td>";
        echo "<td>" . $row["受款人"] . "</td>";
        echo "<td>" . $row["金額"] . "</td>";
        echo "<td>" . $row["填表日期"] . "</td>";
        echo "<td>" . $row["支出項目"] . "</td>";
        echo "<td>" . $status . "</td>"; // 顯示審核狀態
        echo "<td>
		<div style='display: flex; justify-content: center; align-items: center; height: 100xp;'>
		<div style='display: flex; gap: 10px;'>
            <form method='post' action='審核查看.php'>
                <input type='hidden' name='count' value='" . $row["count"] . "'>
                <button type='submit' name='review'>查看</button>
            </form>
			
			<form method='post' action='審核意見.php'>
                <input type='hidden' name='count' value='" . $row["count"] . "'>
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
$db_link_review->close();
?>

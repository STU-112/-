<?php
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";
// 連接到 預支 資料庫
$dbname_預支 = "預支";
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

// 連接到 Review_comments 資料庫
$dbname_review = "Review_comments";
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 Review_comments 資料庫失敗: " . $db_link_review->connect_error);
}


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
    p.金額  >= 50000";	

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
            justify-content: flex-end;
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
    <div class='banner' style='gap: 20px;'>
		<a href='董事長審查紀錄.php'>審查紀錄</a>
		<a href='登入.html'>登出</a>
    </div>";
    
    echo "<table>";
    echo "<caption>董事長審核</caption>";
    
    // 顯示欄位名稱
    echo "<tr>";
    echo "<th>單號</th><th>受款人</th><th>金額</th><th>督導意見</th><th>主任意見</th><th>執行長意見</th><th>審核狀態</th><th>操作</th>";
    echo "</tr>";
	
   // 顯示每一行資料 
while ($row = $result->fetch_assoc()) {
    $serial_count = $row["count"];

		// 查詢督導審核意見是否存在於 Review_comments 資料庫
		$sql_review_opinion1 = "SELECT 審核意見 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
		$review_result = $db_link_review->query($sql_review_opinion1);


		// 查詢主任審核意見是否存在
        $sql_director_opinion2 = "SELECT 審核意見 FROM 主任審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $director_result = $db_link_review->query($sql_director_opinion2);


		// 查詢執行長審核意見是否存在
        $sql_director_opinion3 = "SELECT 審核意見,狀態 FROM 執行長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $execution_result = $db_link_review->query($sql_director_opinion3);

// 查詢董事長審核意見是否存在
        $sql_director_opinion4 = "SELECT 審核意見 FROM 董事長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $execution_cashier = $db_link_review->query($sql_director_opinion4);





    // 檢查督導是否有審核意見，且主任尚未審核
    if ($review_result && $review_result->num_rows > 0) {
        $review_row = $review_result->fetch_assoc();
        $opinion1 = $review_row["審核意見"];
		
		
		
		// 檢查主任是否有審核意見，且執行長尚未審核
    if ($director_result && $director_result->num_rows > 0) {
        $review_row = $director_result->fetch_assoc();
        $opinion2 = $review_row["審核意見"];
		
		
		// 檢查執行長是否有審核意見，且董事長尚未審核
    if ($execution_result && $execution_result->num_rows > 0) {
        $review_row = $execution_result->fetch_assoc();
        $opinion3 = $review_row["審核意見"];
		$status = $review_row["狀態"]; 
				 
				 // 如果尚未有董事長的審核意見，則顯示這筆資料
        if ($execution_cashier && $execution_cashier->num_rows > 0) {
			continue;
        }
			$opinion4 = "<span style='color: orange;'>未審核</span>";
            if ($status == '通過') {


				 
				 
        

        echo "<tr class='second-row'>";
        echo "<td>" . $row["count"] . "</td>";
        echo "<td>" . $row["受款人"] . "</td>";
        echo "<td>" . $row["金額"] . "</td>";
        echo "<td>" . $opinion1 . "</td>";
        echo "<td>" . $opinion2 . "</td>";
        echo "<td>" . $opinion3 . "</td>";
		echo "<td>" . $opinion4 . "</td>";
        echo "<td>
            <form method='post' action='董事長審查處理.php'>
                <input type='hidden' name='count' value='" . $row["count"] . "'>
                <button type='submit' name='review'>審查</button>
            </form>
        </td>";
        echo "</tr>";
        }
    }
    }
}
}
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>無資料顯示</p>";
}

// 釋放結果集
if ($result) {
    $result->free();
}

// 關閉資料庫連線
$db_link_預支->close();
$db_link_review->close();
?>

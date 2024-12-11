<?php
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到 op2 資料庫
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
    p.國字金額
FROM 
    基本資料 AS b
LEFT JOIN 
    支出項目 AS s ON b.`count` = s.`count`
LEFT JOIN 
    說明 AS d ON b.`count` = d.`count`
LEFT JOIN 
    支付方式 AS p ON b.`count` = p.`count`
WHERE 
    p.國字金額 IS NOT NULL";

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
		<a href='出納審查紀錄.php'>審查紀錄</a>
		<a href='登入.html'>登出</a>
    </div>";
    echo "<table>";
    echo "<caption>出納審核</caption>";
    echo "<tr>";
    echo "<th>單號</th><th>受款人</th><th>金額</th><th>督導意見</th><th>主任意見</th><th>執行長意見</th><th>董事長意見</th><th>會計意見</th><th>操作</th>";
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["count"];
        $opinion1 = $opinion2 = $opinion3 = $opinion4 = "<span style='color: orange;'>無須審核</span>";

        // 查詢督導審核意見
        $sql_review_opinion1 = "SELECT 審核意見 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_review->query($sql_review_opinion1);

        // 查詢主任審核意見
        $sql_director_opinion2 = "SELECT 審核意見 FROM 主任審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $director_result = $db_link_review->query($sql_director_opinion2);

        // 查詢執行長審核意見
        $sql_exec_opinion3 = "SELECT 審核意見 FROM 執行長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $exec_result = $db_link_review->query($sql_exec_opinion3);

        // 查詢董事長審核意見
        $sql_chair_opinion4 = "SELECT 審核意見 FROM 董事長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $chair_result = $db_link_review->query($sql_chair_opinion4);
		
		// 查詢會計審核意見
        $sql_chair_opinion5 = "SELECT 審核意見 FROM 會計審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $accounting_result = $db_link_review->query($sql_chair_opinion5);



        // 檢查條件
        $show_row = false;
        if ($row["國字金額"] < 1000 && $review_result && $review_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
            $review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
			
			
			
        } elseif ($row["國字金額"] < 5000 && $review_result && $review_result->num_rows > 0 && $director_result && $director_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
            $review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
            $director_row = $director_result->fetch_assoc();
            $opinion2 = $director_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
			
			
			
        } elseif ($row["國字金額"] <= 50000 && $review_result && $review_result->num_rows > 0 && $director_result && $director_result->num_rows > 0 && $exec_result && $exec_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
			$review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
            $director_row = $director_result->fetch_assoc();
            $opinion2 = $director_row["審核意見"];
            $exec_row = $exec_result->fetch_assoc();
            $opinion3 = $exec_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
			
			
			
        } elseif ($row["國字金額"] > 50000 && $review_result && $review_result->num_rows > 0 && $director_result && $director_result->num_rows > 0 && $exec_result && $exec_result->num_rows > 0 && $chair_result && $chair_result->num_rows > 0 && $accounting_result && $accounting_result->num_rows > 0) {
			$review_row = $review_result->fetch_assoc();
            $opinion1 = $review_row["審核意見"];
            $director_row = $director_result->fetch_assoc();
            $opinion2 = $director_row["審核意見"];
            $exec_row = $exec_result->fetch_assoc();
            $opinion3 = $exec_row["審核意見"];
            $chair_row = $chair_result->fetch_assoc();
            $opinion4 = $chair_row["審核意見"];
			
			$chair_row = $accounting_result->fetch_assoc();
            $opinion5 = $chair_row["審核意見"];
            $show_row = true;
        }

        // 顯示符合條件的資料
        if ($show_row) {
            echo "<tr class='second-row'>";
            echo "<td>" . $row["count"] . "</td>";
            echo "<td>" . $row["受款人"] . "</td>";
            echo "<td>" . $row["國字金額"] . "</td>";
            echo "<td>" . $opinion1 . "</td>";
            echo "<td>" . $opinion2 . "</td>";
            echo "<td>" . $opinion3 . "</td>";
            echo "<td>" . $opinion4 . "</td>";
			echo "<td>" . $opinion5 . "</td>";
            echo "<td>
                <form method='post' action='會計審查處理.php'>
                    <input type='hidden' name='count' value='" . $row["count"] . "'>
                    <button type='submit' name='review'>審查</button>
                </form>
            </td>";
            echo "</tr>";
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

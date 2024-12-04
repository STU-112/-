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

// 查詢 op2 資料庫中的 pay_table 資料 
$sql = "SELECT `count`, 受款人, 支出項目, 填表日期, 國字金額, 國字金額_hidden 
        FROM pay_table WHERE 國字金額 IS NOT NULL";
$result = $db_link_預支->query($sql);

// 顯示資料
if ($result && $result->num_rows > 0) {
    echo "
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
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
            width: 1495px;
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
    <div class='banner'>
        <a style='align-items: right;' href='申請紀錄.php'>審查紀錄</a>
    </div>";

    echo "<table>";
    echo "<caption>督導審核</caption>";
    echo "<tr>";
    echo "<th>單號</th><th>受款人</th><th>金額</th><th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>";
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["count"];
        $sql_review_opinion = "SELECT 審核意見 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_review->query($sql_review_opinion);
        if ($review_result && $review_result->num_rows > 0) {
            $review_result->free();
            continue;
        }
        $opinion = "<span style='color: orange;'>未審核</span>";
        echo "<tr class='second-row'>";
        echo "<td>" . $row["count"] . "</td>";
        echo "<td>" . $row["受款人"] . "</td>";
        echo "<td>" . $row["國字金額"] . "</td>";
        echo "<td>" . $row["填表日期"] . "</td>";
        echo "<td>" . $row["支出項目"] . "</td>";
        echo "<td>" . $opinion . "</td>";
        echo "<td>
            <form method='post' action='督導審查處理.php'>
                <input type='hidden' name='count' value='" . $row["count"] . "'>
                <button type='submit' name='review'>審查</button>
            </form>
        </td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>無資料顯示</p>";
}

if ($result) {
    $result->free();
}

$db_link_預支->close();
$db_link_review->close();
?>

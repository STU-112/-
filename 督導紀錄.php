<?php
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到 op2 資料庫
$dbname_op2 = "op2";
$db_link_op2 = new mysqli($servername, $username, $password, $dbname_op2);

// 連接到 Review_comments 資料庫
$dbname_review = "Review_comments";
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

// 檢查資料庫連線
if ($db_link_op2->connect_error) {
    die("連線到 op2 資料庫失敗: " . $db_link_op2->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 Review_comments 資料庫失敗: " . $db_link_review->connect_error);
}

// 查詢 op2 資料庫中的 pay_table 資料 
$sql = "SELECT serial_count, form_type, amount, fillDate, recipient FROM pay_table";
$result = $db_link_op2->query($sql);

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
        }
        caption {
            font-size: 1.5em;
            margin: 10px;
            font-weight: bold;
        }
    </style>
    ";
    
    echo "<table>";
    echo "<caption>督導審核</caption>";
    
    // 顯示欄位名稱
    echo "<tr>";
    echo "<th>單號</th><th>表單</th><th>金額</th><th>日期</th><th>受款人</th><th>審核意見</th><th>操作</th>";
    echo "</tr>";

    // 顯示每一行資料 
    while ($row = $result->fetch_assoc()) {
        $serial_count = $row["serial_count"];

        // 查詢 Review_comments 資料庫中的督導審核意見
        $sql_review_opinion = "SELECT 審核意見 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_review->query($sql_review_opinion);
        
        // 若該筆資料已有審核意見，跳過該資料
        if ($review_result && $review_result->num_rows > 0) {
            continue;
        }

        // 顯示沒有審核意見的資料
        $opinion = "<span style='color: orange;'>未審核</span>";

        echo "<tr>";
        echo "<td>" . $row["serial_count"] . "</td>";
        echo "<td>" . $row["form_type"] . "</td>";
        echo "<td>" . $row["amount"] . "</td>";
        echo "<td>" . $row["fillDate"] . "</td>";
        echo "<td>" . $row["recipient"] . "</td>";
        echo "<td>" . $opinion . "</td>"; // 顯示審核意見
        echo "<td>
            <form method='post' action='督導審查處理.php'>
                <input type='hidden' name='serial_count' value='" . $row["serial_count"] . "'>
                <button type='submit' name='review'>審查</button>
            </form>
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
if (isset($review_result) && $review_result instanceof mysqli_result) {
    $review_result->free();
}

// 關閉資料庫連線
$db_link_op2->close();
$db_link_review->close();
?>

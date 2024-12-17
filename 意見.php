<?php
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到 review_comments 資料庫
$dbname_review = "review_comments"; // 假設 review_comments 資料庫名稱
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

// 檢查資料庫連線
if ($db_link_review->connect_error) {
    die("連線到 review_comments 資料庫失敗: " . $db_link_review->connect_error);
}

// 獲取 POST 的 count 值
$serial_count = $_POST['count'] ?? null;

if ($serial_count) {
    // 查詢各角色的審核意見
    $opinions = [
        "督導" => "未找到督導審核意見",
        "主任" => "未找到主任審核意見",
        "執行長" => "未找到執行長審核意見",
        "董事長" => "未找到董事長審核意見",
        "會計" => "未找到會計審核意見",
        "出納" => "未找到出納審核意見",
    ];

    foreach ($opinions as $role => $default_opinion) {
        $table_name = "{$role}審核意見";
        $sql = "SELECT 審核意見 FROM $table_name WHERE 單號 = '$serial_count'";
        $result = $db_link_review->query($sql);

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $opinions[$role] = htmlspecialchars($row["審核意見"]);
            $result->free(); // 確認結果集正確後釋放資源	
        }
    }

    // 顯示結果
    echo "<style>
        body {
            font-family: 'Noto Sans TC', Arial, sans-serif;
            background-color: #f5d3ab;
            color: #5a4a3f;
            text-align: center;
            margin: 20px;
        }
        .result {
            font-size: 24px;
            margin: 20px;
        }
        table {
            margin: 0 auto;
            border-collapse: collapse;
            width: 90%;
        }
        th, td {
            border: 1px solid #5a4a3f;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f2c94c;
        }
		
		
		

    </style>";

	

    echo "<div class='result'>";
    echo "<h2>單號: $serial_count</h2>";
    echo "<table>";
    echo "<tr>
        <th>督導審核意見</th>
        <th>主任審核意見</th>
        <th>執行長審核意見</th>
        <th>董事長審核意見</th>
        <th>會計審核意見</th>
        <th>出納審核意見</th>
    </tr>";
    echo "<tr>
        <td>{$opinions['督導']}</td>
        <td>{$opinions['主任']}</td>
        <td>{$opinions['執行長']}</td>
        <td>{$opinions['董事長']}</td>
        <td>{$opinions['會計']}</td>
        <td>{$opinions['出納']}</td>
    </tr>";
    echo "</table>";
    echo "<a href='申請紀錄.php'>返回</a>"; // 替換為你的返回頁面
    echo "</div>";
} else {
    echo "<p style='text-align:center;'>沒有選擇任何單號。</p>";
}

// 關閉資料庫連線
$db_link_review->close();
?>

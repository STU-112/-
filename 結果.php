<?php
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到預支資料庫
$dbname_預支 = "預支";
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

// 連接到 review_comments 資料庫
$dbname_review = "review_comments"; // 假設 review_comments 資料庫名稱
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 review_comments 資料庫失敗: " . $db_link_review->connect_error);
}

// 獲取 POST 的 count 值
$serial_count = $_POST['count'] ?? null;

if ($serial_count) {
    // 查詢督導的審核意見
    $sql_supervisor_opinion = "SELECT 審核意見 FROM 督導審核意見 WHERE 單號 = '$serial_count'";
    $supervisor_result = $db_link_review->query($sql_supervisor_opinion);
    $supervisor_opinion = "未找到督導審核意見"; // 預設值

    if ($supervisor_result && $supervisor_result->num_rows > 0) {
        $supervisor_row = $supervisor_result->fetch_assoc();
        $supervisor_opinion = htmlspecialchars($supervisor_row["審核意見"]);
    }

    // 查詢主任的審核意見
    $sql_director_opinion = "SELECT 審核意見 FROM 主任審核意見 WHERE 單號 = '$serial_count'";
    $director_result = $db_link_review->query($sql_director_opinion);
    $director_opinion = "未找到主任審核意見"; // 預設值

    if ($director_result && $director_result->num_rows > 0) {
        $director_row = $director_result->fetch_assoc();
        $director_opinion = htmlspecialchars($director_row["審核意見"]);
    }

    // 查詢執行長的審核意見
    $sql_executive_opinion = "SELECT 審核意見 FROM 執行長審核意見 WHERE 單號 = '$serial_count'";
    $executive_result = $db_link_review->query($sql_executive_opinion);
    $executive_opinion = "未找到執行長審核意見"; // 預設值

    if ($executive_result && $executive_result->num_rows > 0) {
        $executive_row = $executive_result->fetch_assoc();
        $executive_opinion = htmlspecialchars($executive_row["審核意見"]);
    }

    // 查詢董事長的審核意見
    $sql_chairman_opinion = "SELECT 審核意見 FROM 董事長審核意見 WHERE 單號 = '$serial_count'";
    $chairman_result = $db_link_review->query($sql_chairman_opinion);
    $chairman_opinion = "未找到董事長審核意見"; // 預設值

    if ($chairman_result && $chairman_result->num_rows > 0) {
        $chairman_row = $chairman_result->fetch_assoc();
        $chairman_opinion = htmlspecialchars($chairman_row["審核意見"]);
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
            width: 80%;
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
    echo "<tr><th>督導審核意見</th><th>主任審核意見</th><th>執行長審核意見</th><th>董事長審核意見</th></tr>";
    echo "<tr><td>$supervisor_opinion</td><td>$director_opinion</td><td>$executive_opinion</td><td>$chairman_opinion</td></tr>";
    echo "</table>";
    echo "<a href='申請紀錄.php'>返回</a>"; // 替換為你的返回頁面
    echo "</div>";

    // 釋放結果集
    $supervisor_result->free();
    $director_result->free();
    $executive_result->free();
    $chairman_result->free();
} else {
    echo "<p style='text-align:center;'>沒有選擇任何單號。</p>";
}

// 關閉資料庫連線
$db_link_預支->close();
$db_link_review->close();
?>

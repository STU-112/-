<?php
// 資料庫連線
$servername = "localhost:3307";
$username = "root";
$password = "3307";
$dbname_review = "基金會";
$db = new mysqli($servername, $username, $password, $dbname_review);

if ($db->connect_error) {
    die("連線失敗: " . $db->connect_error);
}

// 接收單號
$serial_count = $_POST['受款人代號'] ?? null;

if ($serial_count) {

    // 查詢所有資料表名稱中包含「審核意見」
    $sql_tables = "SHOW TABLES LIKE '%審核意見%'";
    $result_tables = $db->query($sql_tables);

    $opinions = [];

    if ($result_tables) {
        while ($table_row = $result_tables->fetch_array()) {
            $table_name = $table_row[0]; // 表名
            // 從表名擷取出角色，例如：「會計審核意見」 => 「會計」
            $role_name = str_replace('審核意見', '', $table_name);

            // 查詢該角色對此單號的審核意見
            $sql_opinion = "SELECT 審核意見 FROM `$table_name` WHERE 單號 = ?";
            $stmt = $db->prepare($sql_opinion);
            $stmt->bind_param("s", $serial_count);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows > 0) {
                $row = $res->fetch_assoc();
                $opinions[$role_name] = htmlspecialchars($row["審核意見"]);
            } else {
                $opinions[$role_name] = "<span style='color:#999'>未填</span>";
            }

            $stmt->close();
        }
    }

    // 顯示畫面
    echo "<style>

			 body {
           
            font-family: 'Noto Sans TC', Arial, sans-serif;
			background: linear-gradient(to bottom, #e8dff2, #f5e8fc); /* 淡紫色漸層 */
            color: #5a4a3f;
			text-align: center;
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
            background-color: #E6CAFF;
        }
    </style>";

    echo "<div>";
    echo "<h2>單號: $serial_count</h2>";
    echo "<table><tr>";

    // 動態產生表頭
    foreach ($opinions as $role => $opinion) {
        echo "<th>{$role}審核意見</th>";
    }
    echo "</tr><tr>";
    // 動態產生資料列
    foreach ($opinions as $role => $opinion) {
        echo "<td>{$opinion}</td>";
    }
    echo "</tr></table>";
    echo "<br><a href='javascript:history.back()'>返回</a>";
    echo "</div>";

} else {
    echo "<p style='text-align:center;'>⚠️ 未提供查詢單號。</p>";
}

$db->close();
?>
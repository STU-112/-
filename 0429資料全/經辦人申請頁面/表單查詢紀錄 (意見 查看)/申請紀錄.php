<?php 
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 申請紀錄.php");
    exit;
}

// 獲取用戶帳號
$current_user = $_SESSION['帳號'];

// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";
$dbname_預支 = "基金會";

// 連接到 基金會 資料庫
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);
if ($db_link_預支->connect_error) {
    die("連線到預支資料庫失敗: " . $db_link_預支->connect_error);
}

// 取得此使用者對應的「員工編號」
$sql_註冊 = "SELECT 員工編號 FROM 註冊資料表 WHERE 帳號 = ?";
$stmt = $db_link_預支->prepare($sql_註冊);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$stmt->bind_result($員工編號);
$stmt->fetch();
$stmt->close();

// 先從 GET 抓出查詢條件 (顯示用)
$search_serial = isset($_GET['search_serial']) ? $_GET['search_serial'] : '';
$search_item   = isset($_GET['search_item'])   ? $_GET['search_item']   : '';

/* ----------------------------------------------------------
   (1) 若使用者按下「下載 Excel」按鈕，則由這段程式負責匯出資料
   ---------------------------------------------------------- */
if (isset($_POST['export_excel'])) {

    // 從 POST 取得搜尋條件
    $search_serial_post = isset($_POST['search_serial']) ? $_POST['search_serial'] : '';
    $search_item_post   = isset($_POST['search_item'])   ? $_POST['search_item']   : '';

    // 建立與前端相同的查詢語句
    $sql_export = "
    SELECT 
        b.*,
        s.*,
        d.*
    FROM 
        受款人資料檔 AS b
    LEFT JOIN 
        經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
    LEFT JOIN 	
        經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
    WHERE 
        s.金額 IS NOT NULL
        AND d.經辦代號 = '$員工編號'
    ";
    if (!empty($search_serial_post)) {
        $sql_export .= " AND `交易單號` LIKE '%$search_serial_post%'";
    }
    if (!empty($search_item_post)) {
        $sql_export .= " AND `支出項目` = '$search_item_post'";
    }

    $result_export = $db_link_預支->query($sql_export);

    // 組合檔案名稱
    // 若搜尋支出項目為空，則以「全部」替代
    $支出項目名稱 = !empty($search_item_post) ? $search_item_post : "全部";
    // 組成檔名：申請紀錄_支出項目_年-月-日_當天下載時間.csv
    $download_filename = "申請紀錄_" . $支出項目名稱 . "_" . date('Y-m-d') . "_" . date('His') . ".csv";

// 設定標頭
header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet; charset=UTF-8");
header("Content-Disposition: attachment; filename=" . $download_filename);
header("Pragma: no-cache");
header("Expires: 0");

// 加上 BOM，避免中文亂碼
echo "\xEF\xBB\xBF";

// 輸出 Excel 標題
echo "交易單號\t填表人\t受款人\t金額\t填表日期\t支出項目\t審核狀態\n";

if ($result_export && $result_export->num_rows > 0) {
    while ($row_exp = $result_export->fetch_assoc()) {

            // 使用與網頁顯示相同的審核狀態邏輯
            $serial_count_exp = $row_exp["受款人代號"];
            $status_exp = "待審核"; // 預設狀態

            // 部門主管審核意見
            $sql_review_opinion = "SELECT 狀態 FROM 部門主管審核意見 WHERE 單號 = '$serial_count_exp' LIMIT 1";
            $review_result = $db_link_預支->query($sql_review_opinion);
            if ($review_result && $review_result->num_rows > 0) {
                $review_row = $review_result->fetch_assoc();
                $opinion = $review_row["狀態"];
                if ($opinion == "通過") {
                    $status_exp = "主任審核中";
                    // 主任審核意見
                    $sql_director_opinion = "SELECT 狀態 FROM 主任審核意見 WHERE 單號 = '$serial_count_exp' LIMIT 1";
                    $director_result = $db_link_預支->query($sql_director_opinion);
                    if ($director_result && $director_result->num_rows > 0) {
                        $director_row = $director_result->fetch_assoc();
                        if ($director_row["狀態"] == "通過") {
                            $status_exp = "執行長審核中";
                            // 執行長審核意見
                            $sql_executive_opinion = "SELECT 狀態 FROM 執行長審核意見 WHERE 單號 = '$serial_count_exp' LIMIT 1";
                            $executive_result = $db_link_預支->query($sql_executive_opinion);
                            if ($executive_result && $executive_result->num_rows > 0) {
                                $executive_row = $executive_result->fetch_assoc();
                                if ($executive_row["狀態"] == "通過") {
                                    $status_exp = "董事長審核中";
                                    // 董事長審核意見
                                    $sql_chairman_opinion = "SELECT 狀態 FROM 董事長審核意見 WHERE 單號 = '$serial_count_exp' LIMIT 1";
                                    $chairman_result = $db_link_預支->query($sql_chairman_opinion);
                                    if ($chairman_result && $chairman_result->num_rows > 0) {
                                        $chairman_row = $chairman_result->fetch_assoc();
                                        if ($chairman_row["狀態"] == "通過") {
                                            $status_exp = "會計審核中";
                                            // 會計審核意見
                                            $sql_accounting_opinion = "SELECT 狀態 FROM 會計審核意見 WHERE 單號 = '$serial_count_exp' LIMIT 1";
                                            $accounting_result = $db_link_預支->query($sql_accounting_opinion);
                                            if ($accounting_result && $accounting_result->num_rows > 0) {
                                                $accounting_row = $accounting_result->fetch_assoc();
                                                if ($accounting_row["狀態"] == "通過") {
                                                    $status_exp = "出納審核中";
                                                    // 出納審核意見
                                                    $sql_cashier_opinion = "SELECT 狀態 FROM 出納審核意見 WHERE 單號 = '$serial_count_exp' LIMIT 1";
                                                    $cashier_result = $db_link_預支->query($sql_cashier_opinion);
                                                    if ($cashier_result && $cashier_result->num_rows > 0) {
                                                        $cashier_row = $cashier_result->fetch_assoc();
                                                        if ($cashier_row["狀態"] == "通過") {
                                                            $status_exp = "審核通過";
                                                        } else {
                                                            $status_exp = "出納不通過";
                                                        }
                                                    }
                                                } else {
                                                    $status_exp = "會計不通過";
                                                }
                                            }
                                        } else {
                                            $status_exp = "董事長不通過";
                                        }
                                    }
                                } else {
                                    $status_exp = "執行長不通過";
                                }
                            }
                        } else {
                            $status_exp = "主任不通過";
                        }
                    }
                } else {
                    $status_exp = "部門主管不通過";
                }
            }

            echo 
            htmlspecialchars($row_exp["交易單號"])   . "\t" .
            htmlspecialchars($row_exp["經辦代號"])   . "\t" .
            htmlspecialchars($row_exp["受款人代號"]) . "\t" .
            htmlspecialchars($row_exp["金額"])       . "\t" .
            htmlspecialchars($row_exp["填表日期"])   . "\t" .
            htmlspecialchars($row_exp["支出項目"])   . "\t" .
            htmlspecialchars($status_exp)            . "\n";
    }
}
exit;
    // 匯出完成，結束程式執行，避免後續輸出 HTML
}
/* ----------------------------------------------------------
   (1) 若沒有按「下載 Excel」，則繼續顯示網頁內容
   ---------------------------------------------------------- */

// 組合用於前端顯示的 SQL
$sql = "
SELECT 
    b.*,
    s.*,
    d.*
FROM 
    受款人資料檔 AS b
LEFT JOIN 
    經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
LEFT JOIN 	
    經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
WHERE 
    s.金額 IS NOT NULL
    AND d.經辦代號 = '$員工編號'
";
if (!empty($search_serial)) {
    $sql .= " AND `交易單號` LIKE '%$search_serial%'";
}
if (!empty($search_item)) {
    $sql .= " AND `支出項目` = '$search_item'";
}

$result = $db_link_預支->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>申請紀錄</title>
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
            background-color: #f5d3ab;
            color: #5a4a3f;
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
            background-color: white;
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
            background: linear-gradient(to bottom, #fbe3c9, #f5d3ab);
            color: #5a3d2b;
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
            font-size: 1.2em;
            padding: 5px 20px;
        }
        .banner a:hover {
            color: #007bff;
        }
    </style>
</head>
<body>
    <div class="banner">
        <a style="align-items: left;" onclick="history.back()">◀</a>
        <div style="justify-content: flex-end;">歡迎，<?php echo htmlspecialchars($current_user); ?>！</div>
    </div>

    <?php
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        echo "<caption>申請紀錄</caption>";

        // 搜尋表單 (GET)
        echo "
        <form method='get' style='text-align: center; margin-bottom: 20px;'>
            <label>單號:
                <input type='text' name='search_serial' value='$search_serial'>
            </label>
            <label>支出項目:
                <select name='search_item'>
                    <option value=''>-- 全部 --</option>
                    <option value='W活動費用'" . ($search_item == 'W活動費用' ? " selected" : "") . ">W活動費用</option>
                    <option value='X獎學金'" . ($search_item == 'X獎學金' ? " selected" : "") . ">X獎學金</option>
                    <option value='Y經濟扶助'" . ($search_item == 'Y經濟扶助' ? " selected" : "") . ">Y經濟扶助</option>
                    <option value='Z其他'" . ($search_item == 'Z其他' ? " selected" : "") . ">Z其他</option>
                </select>
            </label>
            <button type='submit'>搜尋</button>
        </form>
        ";

        // 下載 Excel 按鈕 (POST) 並將搜尋條件以 hidden fields 傳入
        echo "
        <form method='post' style='text-align: center; margin-bottom: 20px;'>
            <input type='hidden' name='search_serial' value='$search_serial'>
            <input type='hidden' name='search_item'   value='$search_item'>
            <button type='submit' name='export_excel'>下載 Excel</button>
        </form>
        ";

        echo "<tr>";
        echo "<th>單號</th><th>填表人</th><th>受款人</th><th>金額</th><th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>";
        echo "</tr>";

        while ($row = $result->fetch_assoc()) {
            $serial_count = $row["受款人代號"];
            $status = "<span style='color: orange;'>待審核</span>";

            // 部門主管審核意見
            $sql_review_opinion = "SELECT 狀態 FROM 部門主管審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
            $review_result = $db_link_預支->query($sql_review_opinion);
            if ($review_result && $review_result->num_rows > 0) {
                $review_row = $review_result->fetch_assoc();
                $opinion = $review_row["狀態"];
                if ($opinion == "通過") {
                    $status = "<span style='color: green;'>主任審核中</span>";
                    // 主任審核意見
                    $sql_director_opinion = "SELECT 狀態 FROM 主任審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                    $director_result = $db_link_預支->query($sql_director_opinion);
                    if ($director_result && $director_result->num_rows > 0) {
                        $director_row = $director_result->fetch_assoc();
                        if ($director_row["狀態"] == "通過") {
                            $status = "<span style='color: green;'>執行長審核中</span>";
                            // 執行長審核意見
                            $sql_executive_opinion = "SELECT 狀態 FROM 執行長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                            $executive_result = $db_link_預支->query($sql_executive_opinion);
                            if ($executive_result && $executive_result->num_rows > 0) {
                                $executive_row = $executive_result->fetch_assoc();
                                if ($executive_row["狀態"] == "通過") {
                                    $status = "<span style='color: green;'>董事長審核中</span>";
                                    // 董事長審核意見
                                    $sql_chairman_opinion = "SELECT 狀態 FROM 董事長審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                    $chairman_result = $db_link_預支->query($sql_chairman_opinion);
                                    if ($chairman_result && $chairman_result->num_rows > 0) {
                                        $chairman_row = $chairman_result->fetch_assoc();
                                        if ($chairman_row["狀態"] == "通過") {
                                            $status = "<span style='color: green;'>會計審核中</span>";
                                            // 會計審核意見
                                            $sql_accounting_opinion = "SELECT 狀態 FROM 會計審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
                                            $accounting_result = $db_link_預支->query($sql_accounting_opinion);
                                            if ($accounting_result && $accounting_result->num_rows > 0) {
                                                $accounting_row = $accounting_result->fetch_assoc();
                                                if ($accounting_row["狀態"] == "通過") {
                                                    $status = "<span style='color: green;'>出納審核中</span>";
                                                    // 出納審核意見
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
            echo "<td>" . $row["交易單號"]    . "</td>";
            echo "<td>" . $row["經辦代號"]    . "</td>";
            echo "<td>" . $row["受款人代號"]  . "</td>";
            echo "<td>" . $row["金額"]        . "</td>";
            echo "<td>" . $row["填表日期"]    . "</td>";
            echo "<td>" . $row["支出項目"]    . "</td>";
            echo "<td>" . $status            . "</td>";
            echo "<td>
                    <div style='display: flex; justify-content: center; align-items: center; height: 100px;'>
                        <div style='display: flex; gap: 10px;'>
                            <form method='post' action='查看.php'>
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
</body>
</html>

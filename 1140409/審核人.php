<?php 
include '啟動Session.php';

// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到資料庫
$dbname_預支 = "基金會";
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("資料庫連線失敗");
}

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
$上一個職位查詢 = "SELECT 職位名稱 FROM 職位 WHERE 編號 < $當前職位編號 ORDER BY 編號 DESC LIMIT 1";
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
// 查詢預支資料
$sql = "
SELECT 
    b.受款人代號,
    s.交易單號,
    d.支出項目,
    d.填表日期,
    d.經辦代號,
    r.員工編號,
    s.金額
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

$result = $db_link_預支->query($sql);

// 顯示資料
include '審核人style.php';

echo "
<div class='banner'>
    <div class='left'>". htmlspecialchars($部門) ." - ". htmlspecialchars($員工編號) ."</div>
    <div class='right'>
        <span>歡迎，". htmlspecialchars($帳號) ."！</span> 
        <a href='審核人審查紀錄.php'>審查紀錄</a>
        <a href='登出.php'>登出</a>
    </div>
</div>";


echo "<table>";
echo "<caption>" . htmlspecialchars($職位名稱) . "審核</caption>";
echo "<tr>";
echo "<th>單號</th><th>受款人</th><th>金額</th><th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>";
echo "</tr>";

if ($result && $result->num_rows > 0) {
   while ($row = $result->fetch_assoc()) {
        $serial_count = $row["受款人代號"];

        // 檢查當前職位是否已審核過
        $sql_current_review = "SELECT 審核意見 FROM `{$職位名稱}審核意見` WHERE 單號 = '$serial_count' LIMIT 1";
        $current_review_result = $db_link_預支->query($sql_current_review);
        if ($current_review_result && $current_review_result->num_rows > 0) {
            continue;
        }

        $審核條件符合 = false;
        if ($職位名稱 == "部門主管") {
            $審核條件符合 = true;
        } else {
            $sql_review_opinion = "SELECT 狀態 FROM `{$上一級職位名稱}審核意見` WHERE 單號 = '$serial_count' LIMIT 1";
            $review_result = $db_link_預支->query($sql_review_opinion);
            if ($review_result && $review_result->num_rows > 0) {
                $review_data = $review_result->fetch_assoc();
                if ($review_data["狀態"] == "不通過") {
                    continue;
                } else {
                    $審核條件符合 = true;
                }
            }
        }

        if ($審核條件符合) {
            echo "<tr class='second-row'>";
            echo "<td>" . htmlspecialchars($row["交易單號"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["受款人代號"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["金額"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["填表日期"]) . "</td>";
            echo "<td>" . htmlspecialchars($row["支出項目"]) . "</td>";
            echo "<td style='color: orange;'>待審核</td>";
            echo "<td>
                <form method='post' action='審核人審查處理.php'>
                    <input type='hidden' name='受款人代號' value='" . htmlspecialchars($row["受款人代號"]) . "'>
                    <button type='submit' name='review'>審查</button>
                </form>
            </td>";
            echo "</tr>";
        }
    }
}

echo "</table>";
$db_link_預支->close();
?>
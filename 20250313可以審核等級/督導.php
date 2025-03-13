<?php
session_start(); // 開啟 Session

// 檢查使用者是否已登入
if (!isset($_SESSION["帳號"])) {
    header("Location: 登入.php"); // 如果沒有登入，跳轉到登入頁面
    exit();
}
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";


include '連線部分.php';


// 取得登入者資訊
$帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 員工編號, 部門, 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link_註冊->query($職位查詢);

$員工編號 = "";
$部門 = "";
$職位名稱 = "";
$上限 = 0;
$下限 = 0;

if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $員工編號 = $row["員工編號"];
    $部門 = $row["部門"];
    $職位名稱 = $row["權限管理"];
}

// 讀取對應職位的上限與下限
$範圍_sql = "SELECT 上限, 下限 FROM 職位設定表 WHERE 職位名稱 = '$職位名稱' LIMIT 1";
$範圍_result = $db_link_職位設定->query($範圍_sql);

if ($範圍_result && $範圍_result->num_rows > 0) {
    $範圍_data = $範圍_result->fetch_assoc();
    $上限 = $範圍_data["上限"];
    $下限 = $範圍_data["下限"];
}

$sql = "
SELECT 
b.受款人代號 ,
s.交易單號,
d.支出項目,
d.填表日期,
s.金額
FROM 
受款人資料檔 AS b
LEFT JOIN 
經辦人交易檔 AS s ON b.受款人代號 = s.受款人代號
LEFT JOIN 
經辦業務檔 AS d ON b.受款人代號 = d.受款人代號
WHERE 
s.金額 IS NOT NULL";




// 合併查詢語句
// $sql = "ON b.`count` = s.`count`
// SELECT 
    // b.`count`,
    // b.受款人,
    // b.填表日期,
    // s.支出項目,
    // d.說明,
    // p.金額
// FROM 
    // 基本資料 AS b
// LEFT JOIN 
    // 支出項目 AS s ON b.`count` = s.`count`
// LEFT JOIN 
    // 說明 AS d ON b.`count` = d.`count`
// LEFT JOIN 
    // 支付方式 AS p ON b.`count` = p.`count`
// WHERE 
    // p.金額 IS NOT NULL";

$result = $db_link_預支->query($sql);

// 顯示資料
if ($result && $result->num_rows > 0) {
   include '審核人style.php';
   
      
    echo "
<div class='banner'>
    <div class='left'>". htmlspecialchars($部門) ." - ". htmlspecialchars($員工編號) ."</div>
    <div class='right'>
        <span>歡迎，". htmlspecialchars($帳號) ."！</span> 
        <a href='督導審查紀錄.php'>審查紀錄</a>
        <a href='登出.php'>登出</a>
    </div>
</div>";

    echo "<table>";
    echo "<caption>督導審核</caption>";
    echo "<tr>";
    echo "<th>單號</th><th>受款人</th><th>填表日期</th><th>支出項目</th><th>金額</th><th>審核狀態</th><th>操作</th>";
	//  <th>金額</th>
    echo "</tr>";

    while ($row = $result->fetch_assoc()) {

        $serial_count = $row["受款人代號"];
        $sql_review_opinion = "SELECT 審核意見,狀態 FROM 督導審核意見 WHERE 單號 = '$serial_count' LIMIT 1";
        $review_result = $db_link_review->query($sql_review_opinion);
		
        if ($review_result && $review_result->num_rows > 0) {
            $review_result->free();
            continue;
        }
        $opinion = "<span style='color: orange;'>未審核</span>";
        echo "<tr class='second-row'>";
		echo "<td>" . $row["交易單號"] . "</td>";
		echo "<td>" . $row["受款人代號"] . "</td>";
		echo "<td>" . $row["填表日期"] . "</td>";
		echo "<td>" . $row["支出項目"] . "</td>";
		echo "<td>" . $row["金額"] . "</td>";
        echo "<td>" . $opinion . "</td>";
        echo "<td>
            <form method='post' action='督導審查處理.php'>
                <input type='hidden' name='受款人代號' value='" . $row["受款人代號"] . "'>
                <button type='submit' name='review'>審查</button>
            </form>
        </td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='text-align:center;'>無資料顯示</p>";
}
// 釋放結果與關閉連線
if ($result) {
    $result->free();
}
$db_link_預支->close();
$db_link_review->close();
$db_link_註冊->close();
$db_link_職位設定->close();
?>

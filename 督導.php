<?php

include '啟動Session.php';

// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到資料庫
$dbname_預支 = "預支";
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

$dbname_review = "Review_comments";
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

$dbname_註冊 = "註冊"; 
$db_link_註冊 = new mysqli($servername, $username, $password, $dbname_註冊);


// 連接到資料庫
$dbname_職位設定 = "職位設定";
$db_link_職位設定 = new mysqli($servername, $username, $password, $dbname_職位設定);


// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 Review_comments 資料庫失敗: " . $db_link_review->connect_error);
}

// 檢查連線 
if ($db_link_註冊->connect_error) { 
    die("註冊資料庫連線失敗: " . $db_link_註冊->connect_error); 
}

// 檢查連線 
if ($db_link_職位設定->connect_error) { 
    die("職位設定連線失敗: " . $db_link_職位設定->connect_error); 
}





// 讀取職位選單
// $範圍_sql = "SELECT 上限,下限 FROM 職位設定表";
// $範圍_result = $db_link_範圍->query($範圍_sql);




// 讀取職位選單
$職位_sql = "SELECT 職位名稱,上限,下限 FROM 職位設定表";
$職位_result = $db_link_註冊->query($職位_sql);



// 讀取登入者的職位名稱與部門
$帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 員工編號, 部門, 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link_註冊->query($職位查詢);

$員工編號 = "";
$部門 = "";
$職位名稱 = "";
if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $員工編號 = $row["員工編號"];
	$部門 = $row["部門"];
    $職位名稱 = $row["權限管理"];
}


// 讀取註冊資料表中的權限（這裡應該是權限管理，而不是職位名稱）
$職位_sql = "SELECT DISTINCT 權限管理 FROM 註冊資料表";
$職位_result = $db_link_註冊->query($職位_sql);

$職位選項 = "";
if ($職位_result->num_rows > 0) {
    while ($row = $職位_result->fetch_assoc()) {
        $職位選項 .= "<option value='" . htmlspecialchars($row["權限管理"]) . "'>" . htmlspecialchars($row["權限管理"]) . "</option>";
    }
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
    p.金額 IS NOT NULL";


$result = $db_link_預支->query($sql);

// 顯示資料
if ($result && $result->num_rows > 0) {
	
    include	'審核人style.php';
	
    echo "
    <div class='banner'>
        <div class='left'>". htmlspecialchars($部門) ."". htmlspecialchars($員工編號) ."</div>
        <div class='right'>
            <span>歡迎，". htmlspecialchars($帳號) ."！</span> 
            <a href='督導審查紀錄.php'>審查紀錄</a>
            <a href='登出.php'>登出</a>
        </div>
    </div>";


    echo "<table>";
    echo "<caption>" . htmlspecialchars($職位名稱) . "審核</caption>";
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
        echo "<td>" . $row["金額"] . "</td>";
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
$db_link_註冊->close();
?>

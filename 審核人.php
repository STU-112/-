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

$dbname_職位設定 = "職位設定";
$db_link_職位設定 = new mysqli($servername, $username, $password, $dbname_職位設定);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 Review_comments 資料庫失敗: " . $db_link_review->connect_error);
}

if ($db_link_註冊->connect_error) { 
    die("註冊資料庫連線失敗: " . $db_link_註冊->connect_error); 
}

if ($db_link_職位設定->connect_error) { 
    die("職位設定連線失敗: " . $db_link_職位設定->connect_error); 
}

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

// 讀取對應職位的上限、下限及職位編號
$範圍_sql = "SELECT 編號, 職位名稱, 上限, 下限 FROM 職位設定表 WHERE 職位名稱 = '$職位名稱' LIMIT 1";
$範圍_result = $db_link_職位設定->query($範圍_sql);

if ($範圍_result && $範圍_result->num_rows > 0) {
    $範圍_data = $範圍_result->fetch_assoc();
    $上限 = $範圍_data["上限"];
    $下限 = $範圍_data["下限"];
    $職位編號 = $範圍_data["編號"];  // 獲取職位編號
    $職位名稱 = $範圍_data["職位名稱"];  // 獲取職位名稱
}

// 查詢符合審核範圍的資料
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
    p.金額 BETWEEN $下限 AND $上限";  // 限制金額範圍

$result = $db_link_預支->query($sql);

// 顯示資料
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
echo "<caption>" . htmlspecialchars($職位名稱) . "審核</caption>";
echo "<tr>";
echo "<th>單號</th><th>受款人</th><th>金額</th><th>填表日期</th><th>支出項目</th><th>審核狀態</th><th>操作</th>";
echo "</tr>";

// 目前使用者的職位名稱
$當前職位名稱 = "" . htmlspecialchars($職位名稱) . ""; // 這裡要改成動態取得
$單號 = "2024010001"; // 這是要審核的單號

// 取得當前職位的編號
$sql_get_position = "SELECT 編號 FROM 職位列表 WHERE 職位名稱 = ?";
$stmt = $db_link->prepare($sql_get_position);
$stmt->bind_param("s", $當前職位名稱);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $當前編號 = floatval($row["編號"]); // 轉成數值處理

    // 找到前一個職位
    $sql_get_prev_position = "SELECT 職位名稱 FROM 職位列表 WHERE 編號 < ? ORDER BY 編號 DESC LIMIT 1";
    $stmt = $db_link->prepare($sql_get_prev_position);
    $stmt->bind_param("d", $當前編號);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $前一個職位 = $row["職位名稱"];

        // 查詢前一個職位是否已經審核
        $sql_check_review = "SELECT * FROM `{$前一個職位}審核意見` WHERE 單號 = ? LIMIT 1";
        $stmt = $db_link_review->prepare($sql_check_review);
        $stmt->bind_param("s", $單號);
        $stmt->execute();
        $review_result = $stmt->get_result();

        if ($review_result->num_rows > 0) {
            echo "<p style='color: green;'>前一個職位 ($前一個職位) 已完成審核，您可以繼續審核。</p>";
        } else {
            echo "<p style='color: red;'>前一個職位 ($前一個職位) 尚未完成審核，無法進行審核。</p>";
            exit;
        }
    } else {
        echo "<p style='color: green;'>沒有前一個職位，您可以直接審核。</p>";
    }
} else {
    echo "<p style='color: red;'>找不到您的職位資訊。</p>";
}


echo "</table>";

// 釋放結果與關閉連線
if ($result) $result->free();
$db_link_預支->close();
$db_link_review->close();
$db_link_註冊->close();
$db_link_職位設定->close();
?>

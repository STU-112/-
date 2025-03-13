<?php
// 資料庫連線參數
$servername = "localhost:3307";
$username = "root";
$password = "3307";

// 連接到 op2 資料庫
$dbname_預支 = "預支";
$dbname_review = "review_comments"; // 假設 review_comments 資料庫名稱
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

// 檢查資料庫連線
if ($db_link_預支->connect_error) {
    die("連線到 預支 資料庫失敗: " . $db_link_預支->connect_error);
}

if ($db_link_review->connect_error) {
    die("連線到 review_comments 資料庫失敗: " . $db_link_review->connect_error);
}
// 初始化搜尋條件
$search_serial = isset($_GET['search_serial']) ? $_GET['search_serial'] : '';
$search_item = isset($_GET['search_item']) ? $_GET['search_item'] : '';
?>
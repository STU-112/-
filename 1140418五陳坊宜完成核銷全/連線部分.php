<?php
// 連接到資料庫
$dbname_預支 = "0228";
$db_link_預支 = new mysqli($servername, $username, $password, $dbname_預支);

$dbname_review = "Review_comments";
$db_link_review = new mysqli($servername, $username, $password, $dbname_review);

$dbname_註冊 = "註冊"; 
$db_link_註冊 = new mysqli($servername, $username, $password, $dbname_註冊);

$dbname_職位設定 = "職位";
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
?>
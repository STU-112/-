<?php
session_start();

// 檢查是否登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}

// 資料庫連線參數
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = '3307'; // 若無密碼可留空字串
$db_name = '基金會';

// 連線到資料庫
$db_link = new mysqli($host, $db_user, $db_pass, $db_name);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// 建立 `職位` 資料表
$table_sql = "CREATE TABLE IF NOT EXISTS 職位 (
    編號 VARCHAR(10) PRIMARY KEY,  
    職位名稱 VARCHAR(50) NOT NULL,  
    上限 INT NOT NULL,  
    下限 INT NOT NULL  
) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($db_link->query($table_sql) === TRUE) {
    echo "職位資料表建立成功！<br>";
} else {
    die("建立職位資料表失敗：" . $db_link->error);
}
// 測試是否成功
$test_sql = "SELECT COUNT(*) FROM 職位";
$result = $db_link->query($test_sql);
if ($result) {
    echo "資料表驗證成功，準備使用！";
} else {
    echo "資料表驗證失敗：" . $db_link->error;
}



// 檢查 `基金會` 資料庫是否存在，若不存在則建立
$db_check_sql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($db_link->query($db_check_sql) === TRUE) {
    echo "資料庫 `$db_name` 檢查/建立成功！<br>";
} else {
    die("建立資料庫失敗：" . $db_link->error);
}



// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptId   = trim($_POST['編號']);
    $deptName = trim($_POST['職位名稱']);
	$up = trim($_POST['上限']);
	$down = trim($_POST['下限']);

    // 簡單檢查欄位
    if ($deptId === '' || $deptName === ''|| $up === ''|| $down === '') {
        $error_message = "編號或職位名稱不可空白";
        header("Location: 新增職位html.php?error=" . urlencode($error_message));
        exit;
    }

    // 檢查是否重複
    $check_sql = "SELECT COUNT(*) AS cnt
                  FROM 職位
                  WHERE 編號 = ? OR 職位名稱 = ?";
    $stmt_check = $db_link->prepare($check_sql);
    $stmt_check->bind_param('ss', $deptId, $deptName);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // 若已有相同編號或職位名稱
        $error_message = "此編號或職位名稱已存在，請重新輸入！";
        header("Location: 新增職位html.php?error=" . urlencode($error_message));
        exit;
    }

    // 新增到資料表
    $insert_sql = "INSERT INTO 職位 (編號, 職位名稱,上限,下限) VALUES (?, ?, ?, ?)";
    $stmt = $db_link->prepare($insert_sql);
    $stmt->bind_param('ssss', $deptId, $deptName, $up, $down);

    if ($stmt->execute()) {
        // 新增成功
        $stmt->close();
        $db_link->close();
        header('Location: 新增職位html.php');
        exit;
    } else {
        // 新增失敗
        $error_message = "新增失敗：" . $stmt->error;
        $stmt->close();
        $db_link->close();
        header("Location: 新增職位html.php?error=" . urlencode($error_message));
        exit;
    }
}

// 若直接用 GET 進來，導回列表
$db_link->close();
header('Location: 新增職位html.php');
exit;
?>

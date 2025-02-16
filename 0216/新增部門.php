<?php 
session_start();
// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}
$current_user = $_SESSION['帳號'];

// 資料庫連線參數
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = ' ';
$target_db = '部門設定';

// 先連線至 MySQL（不指定資料庫），以便建立資料庫（若尚未存在）
$temp_link = new mysqli($host, $db_user, $db_pass);
if ($temp_link->connect_error) {
    die("無法連線 MySQL：" . $temp_link->connect_error);
}
$create_db_sql = "CREATE DATABASE IF NOT EXISTS `$target_db`
                  CHARACTER SET utf8mb4
                  COLLATE utf8mb4_general_ci;";
$temp_link->query($create_db_sql);
$temp_link->close();

// 連線到「部門設定」資料庫
$db_link_部門 = new mysqli($host, $db_user, $db_pass, $target_db);
if ($db_link_部門->connect_error) {
    die("資料庫連線失敗：" . $db_link_部門->connect_error);
}

// 若資料表「部門」不存在，則建立它
$create_table_sql = "
    CREATE TABLE IF NOT EXISTS `部門` (
      `部門代號`  VARCHAR(50)  NOT NULL,
      `部門名稱`  VARCHAR(100) NOT NULL,
      `建立時間`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`部門代號`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$db_link_部門->query($create_table_sql);

// 處理表單提交（POST）
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $部門代號 = $_POST['部門代號'];
    $部門名稱 = $_POST['部門名稱'];

    // 檢查是否重複：同時不允許重複的部門代號或部門名稱
    $check_sql = "SELECT COUNT(*) AS cnt
                  FROM 部門
                  WHERE 部門代號 = ? 
                     OR 部門名稱 = ?;";
    $stmt_check = $db_link_部門->prepare($check_sql);
    $stmt_check->bind_param('ss', $部門代號, $部門名稱);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // 若重複，則導向回 HTML 並帶出錯誤訊息
        $error_message = "此部門代號或部門名稱已存在，請重新輸入！";
        header('Location: 新增部門html.php?error=' . urlencode($error_message));
        exit;
    } else {
        // 執行新增
        $insert_sql = "INSERT INTO 部門 (部門代號, 部門名稱) VALUES (?, ?)";
        $stmt = $db_link_部門->prepare($insert_sql);
        $stmt->bind_param('ss', $部門代號, $部門名稱);
        $stmt->execute();
        $stmt->close();

        // 新增完成後重新導向回 HTML 頁面
        header('Location: 新增部門html.php');
        exit;
    }
}
$db_link_部門->close();
?>

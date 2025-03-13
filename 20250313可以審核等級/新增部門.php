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
$db_name = '部門設定';

// 連線到資料庫
$db_link = new mysqli($host, $db_user, $db_pass, $db_name);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $deptId   = trim($_POST['部門代號']);
    $deptName = trim($_POST['部門名稱']);

    // 簡單檢查欄位
    if ($deptId === '' || $deptName === '') {
        $error_message = "部門代號或部門名稱不可空白";
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }

    // 檢查是否重複
    $check_sql = "SELECT COUNT(*) AS cnt
                  FROM 部門
                  WHERE 部門代號 = ? OR 部門名稱 = ?";
    $stmt_check = $db_link->prepare($check_sql);
    $stmt_check->bind_param('ss', $deptId, $deptName);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // 若已有相同部門代號或部門名稱
        $error_message = "此部門代號或部門名稱已存在，請重新輸入！";
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }

    // 新增到資料表
    $insert_sql = "INSERT INTO 部門 (部門代號, 部門名稱) VALUES (?, ?)";
    $stmt = $db_link->prepare($insert_sql);
    $stmt->bind_param('ss', $deptId, $deptName);

    if ($stmt->execute()) {
        // 新增成功
        $stmt->close();
        $db_link->close();
        header('Location: 新增部門html.php');
        exit;
    } else {
        // 新增失敗
        $error_message = "新增失敗：" . $stmt->error;
        $stmt->close();
        $db_link->close();
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }
}

// 若直接用 GET 進來，導回列表
$db_link->close();
header('Location: 新增部門html.php');
exit;
?>

<?php
session_start();

// (1) 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    // 若未登入，導向到「新增職位設定.php」或其他登入頁
    header("Location: 新增職位設定.php");
    exit;
}

// (2) 連線到「部門設定」資料庫
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = ' ';  // 若無密碼可為空字串
$target_db = '部門設定';

$db_link = new mysqli($host, $db_user, $db_pass, $target_db);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// (3) 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 取得表單欄位
    $部門代號 = trim($_POST['部門代號']);
    $部門名稱 = trim($_POST['部門名稱']);

    // 簡單檢查：不可空白
    if ($部門代號 === '' || $部門名稱 === '') {
        $error_message = "部門代號或部門名稱不可空白";
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }

    // 檢查是否重複：同時不允許重複的「部門代號」或「部門名稱」
    $check_sql = "SELECT COUNT(*) AS cnt
                  FROM 部門
                  WHERE 部門代號 = ? OR 部門名稱 = ?";
    $stmt_check = $db_link->prepare($check_sql);
    $stmt_check->bind_param('ss', $部門代號, $部門名稱);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // 若已有相同部門代號或名稱
        $error_message = "此部門代號或部門名稱已存在，請重新輸入！";
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }

    // 執行新增
    $insert_sql = "INSERT INTO 部門 (部門代號, 部門名稱) VALUES (?, ?)";
    $stmt_insert = $db_link->prepare($insert_sql);
    $stmt_insert->bind_param('ss', $部門代號, $部門名稱);

    if ($stmt_insert->execute()) {
        // 新增成功，導回列表
        $stmt_insert->close();
        $db_link->close();
        header('Location: 新增部門html.php');
        exit;
    } else {
        // 新增失敗 (例如資料庫錯誤)
        $error_message = "新增失敗：" . $stmt_insert->error;
        $stmt_insert->close();
        $db_link->close();
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }
}

// (4) 若直接以 GET 方式進入本檔案，可視需求做處理
$db_link->close();
header('Location: 新增部門html.php');
exit;
?>

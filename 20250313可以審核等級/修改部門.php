<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}

// 資料庫連線參數
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = '3307'; // 若無密碼可留空字串
$db_name   = '部門設定';

$db_link = new mysqli($host, $db_user, $db_pass, $db_name);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// 只接受 POST 提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldDeptId   = trim($_POST['oldDeptId']);   // 舊部門代號 (作為 WHERE 條件)
    $newDeptId   = trim($_POST['部門代號']);      // 新的部門代號
    $newDeptName = trim($_POST['部門名稱']);      // 新的部門名稱

    // 檢查必填欄位
    if ($oldDeptId === '' || $newDeptId === '' || $newDeptName === '') {
        $error_message = "部門代號或部門名稱不可空白";
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }

    // 檢查新的部門代號/部門名稱是否與其他部門重複（排除自己）
    $check_sql = "SELECT COUNT(*) AS cnt
                  FROM 部門
                  WHERE (部門代號 = ? OR 部門名稱 = ?)
                  AND 部門代號 <> ?";
    $stmt_check = $db_link->prepare($check_sql);
    $stmt_check->bind_param('sss', $newDeptId, $newDeptName, $oldDeptId);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $error_message = "修改失敗：新部門代號或名稱與已有部門重複！";
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }

    // 執行 UPDATE，同時更新建立時間為 CURRENT_TIMESTAMP
    $update_sql = "UPDATE 部門
                   SET 部門代號 = ?, 部門名稱 = ?, 建立時間 = CURRENT_TIMESTAMP
                   WHERE 部門代號 = ?";
    $stmt_update = $db_link->prepare($update_sql);
    $stmt_update->bind_param('sss', $newDeptId, $newDeptName, $oldDeptId);

    if ($stmt_update->execute()) {
        $stmt_update->close();
        $db_link->close();
        header('Location: 新增部門html.php');
        exit;
    } else {
        $error_message = "更新失敗：" . $stmt_update->error;
        $stmt_update->close();
        $db_link->close();
        header("Location: 新增部門html.php?error=" . urlencode($error_message));
        exit;
    }
}

$db_link->close();
header("Location: 新增部門html.php");
exit;
?>

<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 修改職位.php");
    exit;
}

// 資料庫連線參數
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = ' '; // 若無密碼可留空字串
$db_name   = '職位';

$db_link = new mysqli($host, $db_user, $db_pass, $db_name);
if ($db_link->connect_error) {
    die("資料庫連線失敗：" . $db_link->connect_error);
}

// 只接受 POST 提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oldDeptId   = trim($_POST['oldDeptId']);   // 舊編號 (作為 WHERE 條件)
    $newDeptId   = trim($_POST['編號']);      // 新的編號
    $newDeptName = trim($_POST['職位名稱']);  // 新的職位名稱
    $up = trim($_POST['上限']); 
    $down = trim($_POST['下限']);      

    // 檢查必填欄位
    if ($oldDeptId === '' || $newDeptId === '' || $up === '' || $down === '' || $newDeptName === '') {
        $error_message = "編號或職位名稱不可空白";
        header("Location: 新增職位html.php?error=" . urlencode($error_message));
        exit;
    }

    // 檢查新的編號/職位名稱是否與其他職位重複（排除自己）
    $check_sql = "SELECT COUNT(*) AS cnt
                  FROM 職位
                  WHERE (編號 = ? OR 職位名稱 = ?)
                  AND 編號 <> ?";
    $stmt_check = $db_link->prepare($check_sql);
    
    // 檢查 SQL 語句是否準備成功
    if ($stmt_check === false) {
        die("SQL 語句準備失敗：" . $db_link->error);
    }

    $stmt_check->bind_param('sss', $newDeptId, $newDeptName, $oldDeptId);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        $error_message = "修改失敗：新編號或名稱與已有職位重複！";
        header("Location: 新增職位html.php?error=" . urlencode($error_message));
        exit;
    }

    // 執行 UPDATE，同時更新建立時間為 CURRENT_TIMESTAMP
    $update_sql = "UPDATE 職位
                   SET 編號 = ?, 職位名稱 = ?,  上限 = ?, 下限 = ?
                   WHERE 編號 = ?";
    $stmt_update = $db_link->prepare($update_sql);
    
    // 檢查 SQL 語句是否準備成功
    if ($stmt_update === false) {
        die("SQL 語句準備失敗：" . $db_link->error);
    }

    // 確保所有變數都被正確定義
    $stmt_update->bind_param('sssss', $newDeptId, $newDeptName, $up, $down, $oldDeptId);

    if ($stmt_update->execute()) {
        $stmt_update->close();
        $db_link->close();
        header('Location: 新增職位html.php');
        exit;
    } else {
        $error_message = "更新失敗：" . $stmt_update->error;
        $stmt_update->close();
        $db_link->close();
        header("Location: 新增職位html.php?error=" . urlencode($error_message));
        exit;
    }
}

$db_link->close();
header("Location: 新增職位html.php");
exit;
?>

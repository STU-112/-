<?php
session_start(); // 啟動 Session

// 連接到資料庫
$db_host = "localhost:3307"; // 指定主機和端口
$db_id = "root";             // 資料庫用戶名
$db_pw = "3307";             // 資料庫密碼（請根據需要更新）
$db_name = "基金會";           // 資料庫名稱

// 連接資料庫
$連接 = new mysqli($db_host, $db_id, $db_pw, $db_name);

// 檢查連接是否成功
if ($連接->connect_error) {
    die("資料庫連接失敗: " . $連接->connect_error);
}

// 檢查是否有 POST 數據
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['db_id']) && isset($_POST['db_pw'])) {
        $帳號 = $_POST['db_id'];
        $密碼 = $_POST['db_pw'];

        // 設定靜態的 admin 帳號與密碼
        $admin_username = 'admin';
        $admin_password = '1';

        if ($帳號 === $admin_username && $密碼 === $admin_password) {
            $_SESSION['帳號'] = $帳號; // 儲存到 Session
            echo "<script>alert('登入成功，歡迎管理員！'); window.location.href = '系統管理員.php';</script>";
        } else {
            $帳號 = $連接->real_escape_string($帳號);
            $密碼 = $連接->real_escape_string($密碼);

            // 查詢使用者資料
            $select_sql = "SELECT * FROM 註冊資料表 WHERE 帳號 = '$帳號'";
            $帳號查詢 = $連接->query($select_sql);

            if ($帳號查詢->num_rows > 0) {
                $row = $帳號查詢->fetch_assoc();
                // 比對密碼
                if ($密碼 == $row['密碼']) {
                    $權限 = $row['權限管理'];
                    $_SESSION['帳號'] = $帳號; // 儲存到 Session

                    // 根據權限設定跳轉頁面
                    if ($權限 == '經辦人') {
                        $跳轉頁面 = '申請.php';
                    } else {
                        $跳轉頁面 = '審核人.php';
                    }

                    echo "<script>alert('登入成功！歡迎 $權限 $帳號'); window.location.href = '$跳轉頁面';</script>";
                } else {
                    echo "<script>alert('密碼錯誤!'); window.location.href = '登入.html';</script>";
                }
            } else {
                echo "<script>alert('帳號錯誤，請重新登入或尚未註冊，請重新註冊'); window.location.href = '登入.html';</script>";
            }
        }
    }
}
$連接->close();
?>

<?php
// 連接到資料庫
$db_host = "localhost:3307"; // 指定主機和端口
$db_id = "root";             // 資料庫用戶名
$db_pw = "3307";             // 資料庫密碼（請根據需要更新）
$db_name = "註冊";           // 資料庫名稱

// 連接資料庫
$連接 = new mysqli($db_host, $db_id, $db_pw, $db_name);

// 檢查連接是否成功
if ($連接->connect_error) {
    die("資料庫連接失敗: " . $連接->connect_error);
}

// 檢查是否有 POST 數據
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 確保表單輸入不為空
    if (isset($_POST['db_id']) && isset($_POST['db_pw'])) {
        // 從表單中獲取帳號與密碼
        $帳號 = $_POST['db_id'];
        $密碼 = $_POST['db_pw'];

        // 設定靜態的 admin 帳號與密碼
        $admin_username = 'admin';
        $admin_password = 'A1';

        // 檢查是否為 admin 帳號
        if ($帳號 === $admin_username && $密碼 === $admin_password) {
            // 如果是 admin，則調轉到管理員頁面
            echo "<script>alert('登入成功，歡迎管理員！'); window.location.href = '系統管理員.html';</script>";
        } else {
            // 檢查帳號是否存在於資料庫
            $帳號 = $連接->real_escape_string($帳號);
            $密碼 = $連接->real_escape_string($密碼);

            // 從資料庫查詢帳號
            $select_sql = "SELECT * FROM 註冊資料表 WHERE 帳號 = '$帳號'";
            $帳號查詢 = $連接->query($select_sql);

            // 檢查是否找到該帳號
            if ($帳號查詢->num_rows > 0) {
                // 帳號存在，繼續檢查密碼
                $row = $帳號查詢->fetch_assoc();
                if ($密碼 == $row['密碼']) {
                    // 密碼正確，顯示歡迎訊息並跳轉
                    $部門 = $row['部門'];
                    $職位 = $row['職位'];
                    echo "<script>alert('登入成功！歡迎來自 $部門 部門的職位代號 $職位 的使用者！'); window.location.href = '申請.html';</script>";
                } else {
                    // 密碼錯誤
                    echo "<script>alert('密碼錯誤!'); window.location.href = '登入.html';</script>";
                }
            } else {
                // 帳號不存在
                echo "<script>alert('帳號錯誤，請重新登入或尚未註冊，請重新註冊'); window.location.href = '登入.html';</script>";
            }
        }
    }
}

// 關閉資料庫連接
$連接->close(); 
?>

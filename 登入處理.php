<?php
session_start();
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $帳號 = $_POST['帳號'];
    $密碼 = $_POST['密碼'];

    // 假設帳號和密碼驗證成功
    // 檢查資料庫是否有此帳號，這裡用簡單的範例替代
    if ($帳號 === 'admin' && $密碼 === '1234') {
        $_SESSION['帳號'] = $帳號;
        
        // 生成唯一的登入 Token
        $token = bin2hex(random_bytes(32));
        $_SESSION['token'] = $token;

        // 可將 Token 儲存到資料庫中 (這裡以文件儲存為例)
        file_put_contents('tokens.txt', $帳號 . '|' . $token . "\n", FILE_APPEND);

        // 登入成功後跳轉
        header("Location: 系統首頁.php");
        exit();
    } else {
        echo "<script>alert('帳號或密碼錯誤'); window.location.href = '登入.html';</script>";
    }
}
?>

<?php
session_start();

// 檢查是否登入
if (!isset($_SESSION['帳號']) || !isset($_SESSION['token'])) {
    header("Location: 登入.html");
    exit();
}

// 檢查 Token 是否有效
$token = $_SESSION['token'];
$帳號 = $_SESSION['帳號'];

$tokens = file('tokens.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
$isValid = false;

foreach ($tokens as $line) {
    list($storedAccount, $storedToken) = explode('|', $line);
    if ($storedAccount === $帳號 && $storedToken === $token) {
        $isValid = true;
        break;
    }
}

if (!$isValid) {
    // Token 無效，強制登出
    session_unset();
    session_destroy();
    echo "<script>alert('登入驗證失敗，請重新登入'); window.location.href = '登入.html';</script>";
    exit();
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>系統首頁</title>
</head>
<body>
    <h1>歡迎，<?php echo htmlspecialchars($帳號); ?></h1>
    <a href="登出.php">登出</a>
</body>
</html>

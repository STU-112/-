<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

$host = 'localhost:3307';
$dbname = '基金會';
$username = 'root';
$password = '3307';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("❌ 資料庫連線失敗：" . $e->getMessage());
}

// 驗證傳入資料
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $單號 = $_POST['交易單號'] ?? '';
    $動作 = $_POST['action'] ?? '';
    $意見 = trim($_POST['意見'] ?? '');

    if (!$單號) {
        die("❌ 請提供交易單號！");
    }

    if (!in_array($動作, ['pass', 'reject'])) {
        die("❌ 無效的動作！");
    }

    $新狀態 = ($動作 === 'pass') ? '核銷完成' : '核銷不通過';

    try {
        $stmt = $pdo->prepare("UPDATE 經辦人交易檔 SET 審核狀態 = ?, 審核意見 = ? WHERE 交易單號 = ?");
        $stmt->execute([$新狀態, $意見, $單號]);

        echo "✅ 審核狀態已更新為：$新狀態";
        echo "<br><a href='verify_review_ui.php'>🔙 回到審核頁面</a>";
    } catch (Exception $e) {
        die("❌ 更新失敗：" . $e->getMessage());
    }
} else {
    die("請用 POST 方法提交表單。");
}
?>

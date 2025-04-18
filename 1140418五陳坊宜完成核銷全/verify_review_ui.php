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
    die('資料庫連接失敗：' . $e->getMessage());
}

$sql = "SELECT * FROM 經辦人交易檔 WHERE 審核狀態 = '核銷審核中' ORDER BY 交易單號 DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$records = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>核銷審核介面</title>
    <style>
        body { font-family: '微軟正黑體'; background: #f9f9f9; padding: 40px; }
        h2 { text-align: center; color: #333; }
        table { margin: 0 auto; border-collapse: collapse; width: 90%; background: white; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        th, td { padding: 12px; border: 1px solid #ccc; text-align: center; }
        th { background-color: #f2f2f2; }
        form { display: inline-block; margin: 0 5px; }
        button { padding: 6px 12px; border: none; border-radius: 5px; cursor: pointer; }
        .pass { background-color: #4CAF50; color: white; }
        .reject { background-color: #f44336; color: white; }
    </style>
</head>
<body>
<h2>待核銷審核表單</h2>

<?php if (count($records) === 0): ?>
    <p style="text-align:center;color:red;">目前沒有待核銷審核的表單。</p>
<?php else: ?>
<table>
    <tr>
        <th>交易單號</th>
        <th>金額</th>
        <th>實支金額</th>
        <th>結餘</th>
        <th>簽收日</th>
        <th>審核</th>
    </tr>
    <?php foreach ($records as $row): ?>
    <tr>
        <td><?= htmlspecialchars($row['交易單號']) ?></td>
        <td><?= htmlspecialchars($row['金額']) ?></td>
        <td><?= htmlspecialchars($row['實支金額']) ?></td>
        <td><?= htmlspecialchars($row['結餘']) ?></td>
        <td><?= htmlspecialchars($row['簽收日']) ?></td>
        <td>
            <form method="POST" action="rul_verify.php">
                <input type="hidden" name="交易單號" value="<?= $row['交易單號'] ?>">
                <button class="pass" name="action" value="pass">通過</button>
                <button class="reject" name="action" value="reject">不通過</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</body>
</html>

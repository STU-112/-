<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

// 取得篩選條件，若未選擇則為空字串
$expenseItem = isset($_GET['expenseItem']) ? trim($_GET['expenseItem']) : '';

// 資料庫連線參數
$host     = 'localhost:3307';
$dbname   = '0228';
$username = 'root';
$password = ' ';

try {
    // 建立 PDO 連線
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 基本 SQL：依「支出項目」分組，計算件數與總金額
    $sql = "
        SELECT 
            e.支出項目,
            COUNT(*) AS 件數,
            SUM(t.金額) AS 總金額
        FROM 經辦業務檔 e
        JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
    ";

    // 若有指定項目（不為空），則加上 WHERE 條件
    if (!empty($expenseItem)) {
        $sql .= " WHERE e.支出項目 = :expenseItem";
    }
    $sql .= " GROUP BY e.支出項目";

    $stmt = $pdo->prepare($sql);
    if (!empty($expenseItem)) {
        $stmt->bindValue(':expenseItem', $expenseItem);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("資料庫連線失敗：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>支出項目統計結果</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      padding: 20px;
    }
    table {
      border-collapse: collapse;
      margin-top: 20px;
    }
    table, th, td {
      border: 1px solid #ccc;
      padding: 8px;
    }
  </style>
</head>
<body>
  <h1>支出項目統計結果</h1>

  <?php if (!empty($expenseItem)): ?>
    <p>目前篩選項目：<?php echo htmlspecialchars($expenseItem); ?></p>
  <?php else: ?>
    <p>目前顯示：所有支出項目</p>
  <?php endif; ?>

  <?php if (count($results) > 0): ?>
    <table>
      <tr>
        <th>支出項目</th>
        <th>件數</th>
        <th>總金額</th>
      </tr>
      <?php foreach ($results as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['支出項目']); ?></td>
        <td><?php echo htmlspecialchars($row['件數']); ?></td>
        <td><?php echo htmlspecialchars($row['總金額']); ?></td>
      </tr>
      <?php endforeach; ?>
    </table>
  <?php else: ?>
    <p>尚無資料。</p>
  <?php endif; ?>
</body>
</html>

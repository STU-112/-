<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

// 取得篩選條件，若未選擇則為空字串（表示全部）
$expenseItem = isset($_GET['expenseItem']) ? trim($_GET['expenseItem']) : '';

$host     = 'localhost:3307';
$dbname   = '0228';
$username = 'root';
$password = ' ';

try {
    // 建立 PDO 連線
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // SQL：依支出項目分組統計件數與總金額
    $sql = "
        SELECT 
            e.支出項目,
            COUNT(*) AS 件數,
            SUM(t.金額) AS 總金額
        FROM 經辦業務檔 e
        JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
    ";
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
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body {
      font-family: 'Poppins', sans-serif;
      background: #F0F4F8;
      padding: 20px;
    }
    .container {
      max-width: 800px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 {
      text-align: center;
      color: #4A90E2;
      margin-bottom: 20px;
    }
    p {
      text-align: center;
      margin-bottom: 20px;
      font-size: 1.1rem;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      padding: 12px;
      border: 1px solid #ddd;
      text-align: center;
    }
    th {
      background-color: #4A90E2;
      color: #fff;
    }
    tr:nth-child(even) {
      background-color: #f9f9f9;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>支出項目統計結果</h1>
    <?php if (empty($expenseItem)): ?>
      <p>目前顯示：全部支出項目</p>
    <?php else: ?>
      <p>目前顯示：<?php echo htmlspecialchars($expenseItem); ?></p>
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
  </div>
</body>
</html>

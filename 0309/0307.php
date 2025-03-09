<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

// 取得篩選條件：若未選擇支出項目，則為空字串（表示全部）
$expenseItem = isset($_GET['expenseItem']) ? trim($_GET['expenseItem']) : '';

$host     = 'localhost:3307';
$dbname   = '0228';
$username = 'root';
$password = ' ';

try {
    // 建立 PDO 連線
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    /* 
       SQL 查詢：
       - 取出「填表日期」的月份（格式：YYYY-MM），命名為 統計月份
       - 依據統計月份分組，計算該月份內的件數 (COUNT(*)) 與總金額 (SUM(t.金額))
       - 若有篩選特定支出項目，則加上 WHERE 條件
    */
    $sql = "
        SELECT 
            DATE_FORMAT(e.填表日期, '%Y-%m') AS 統計月份,
            COUNT(*) AS 件數,
            SUM(t.金額) AS 總金額
        FROM 經辦業務檔 e
        JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
    ";
    if (!empty($expenseItem)) {
        $sql .= " WHERE e.支出項目 = :expenseItem";
    }
    $sql .= " GROUP BY 統計月份 ORDER BY 統計月份 ASC";

    $stmt = $pdo->prepare($sql);
    if (!empty($expenseItem)) {
        $stmt->bindValue(':expenseItem', $expenseItem);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 整理資料給 Chart.js 使用
    $labels = [];
    $counts = [];
    $sums   = [];
    foreach ($results as $row) {
        $labels[] = $row['統計月份'];
        $counts[] = (int)$row['件數'];
        $sums[]   = (float)$row['總金額'];
    }
    $labelsJson = json_encode($labels);
    $countsJson = json_encode($counts);
    $sumsJson   = json_encode($sums);

} catch (PDOException $e) {
    die("資料庫連線失敗：" . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>月度統計報表結果</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <!-- 載入 Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
    /* Chart canvas 樣式 */
    .chart-container {
      margin-top: 30px;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>月度統計報表結果</h1>
    <?php if (empty($expenseItem)): ?>
      <p>目前顯示：<strong>全部支出項目</strong></p>
    <?php else: ?>
      <p>目前顯示：<strong><?php echo htmlspecialchars($expenseItem); ?></strong></p>
    <?php endif; ?>

    <?php if (count($results) > 0): ?>
      <table>
        <tr>
          <th>統計月份</th>
          <th>件數</th>
          <th>總金額</th>
        </tr>
        <?php foreach ($results as $row): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['統計月份']); ?></td>
            <td><?php echo htmlspecialchars($row['件數']); ?></td>
            <td><?php echo htmlspecialchars($row['總金額']); ?></td>
          </tr>
        <?php endforeach; ?>
      </table>

      <div class="chart-container">
        <!-- 長條圖 -->
        <canvas id="barChart"></canvas>
      </div>

      <script>
        var ctx = document.getElementById('barChart').getContext('2d');
        var barChart = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: <?php echo $labelsJson; ?>,
            datasets: [
              {
                label: '件數',
                data: <?php echo $countsJson; ?>,
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
              },
              {
                label: '總金額',
                data: <?php echo $sumsJson; ?>,
                backgroundColor: 'rgba(153, 102, 255, 0.6)',
                borderColor: 'rgba(153, 102, 255, 1)',
                borderWidth: 1
              }
            ]
          },
          options: {
            responsive: true,
            scales: {
              y: {
                beginAtZero: true
              }
            },
            plugins: {
              title: {
                display: true,
                text: '每月統計 - 件數與總金額'
              }
            }
          }
        });
      </script>
    <?php else: ?>
      <p>尚無資料。</p>
    <?php endif; ?>
  </div>
</body>
</html>

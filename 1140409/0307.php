<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

/** 
 * 將數字格式化：保留兩位小數，若為整數則去掉 .00 
 */
function formatAmount($num) {
    $f = floatval($num);
    $formatted = number_format($f, 2);
    if (substr($formatted, -3) === '.00') {
        $formatted = substr($formatted, 0, -3);
    }
    return $formatted;
}

// 取得篩選條件
$expenseItem = isset($_GET['expenseItem']) ? trim($_GET['expenseItem']) : '全部';
$monthFilter = isset($_GET['monthFilter']) ? trim($_GET['monthFilter']) : '';

// 若支出項目為「全部」，視為空 => 不做支出項目篩選
if ($expenseItem === '全部') {
    $expenseItem = '';
}

// 判斷是否使用甜甜圈圖 (當「支出項目」與「月份」都沒指定時)
$useDoughnut = (empty($expenseItem) && empty($monthFilter));

// SQL 依狀況選擇查詢
if ($useDoughnut) {
    // 甜甜圈：查詢「支出項目, 件數, 金額」
    $sql = "
        SELECT
            e.支出項目,
            COUNT(*) AS 件數,
            SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
        FROM 經辦業務檔 e
        JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
        GROUP BY e.支出項目
        ORDER BY e.支出項目
    ";
} else {
    // 長條圖：查詢「統計月份, 件數, 金額」
    $sql = "
        SELECT
            DATE_FORMAT(e.填表日期, '%Y-%m') AS 統計月份,
            COUNT(*) AS 件數,
            SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
        FROM 經辦業務檔 e
        JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
        WHERE 1=1
    ";
    // 若有支出項目
    if (!empty($expenseItem)) {
        $sql .= " AND e.支出項目 = :expenseItem";
    }
    // 若有月份
    if (!empty($monthFilter)) {
        $sql .= " AND DATE_FORMAT(e.填表日期, '%Y-%m') = :monthFilter";
    }
    $sql .= " GROUP BY 統計月份 ORDER BY 統計月份 ASC";
}

// 執行查詢
try {
    $host     = 'localhost:3307';
    $dbname   = '基金會';
    $username = 'root';
    $password = '3307';
    $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8";
    $pdo = new PDO($dsn, $username, $password);
    // 啟用拋出例外模式
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $pdo->prepare($sql);
    if (!$useDoughnut) {
        if (!empty($expenseItem)) {
            $stmt->bindValue(':expenseItem', $expenseItem);
        }
        if (!empty($monthFilter)) {
            $stmt->bindValue(':monthFilter', $monthFilter);
        }
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("資料庫連線失敗：" . $e->getMessage());
}

// 整理資料供 Chart.js 使用
$labels = [];
$data1  = []; // 甜甜圈：金額；長條圖：件數
$data2  = []; // 長條圖：金額
foreach ($results as $row) {
    if ($useDoughnut) {
        // 若總金額欄位存在且是數字，否則預設 0
        $sumAmount = (isset($row['總金額']) && is_numeric($row['總金額'])) ? floatval($row['總金額']) : 0;
        $labels[] = $row['支出項目'];
        $data1[]  = $sumAmount;
    } else {
        $countVal = (isset($row['件數']) && is_numeric($row['件數'])) ? (int)$row['件數'] : 0;
        $sumVal   = (isset($row['總金額']) && is_numeric($row['總金額'])) ? floatval($row['總金額']) : 0;
        $labels[] = $row['統計月份'];
        $data1[]  = $countVal;
        $data2[]  = $sumVal;
    }
}
$labelsJson = json_encode($labels);
$data1Json  = json_encode($data1);
$data2Json  = json_encode($data2);
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>統計結果</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Poppins', sans-serif; background: #F0F4F8; padding: 20px; }
    .container {
      max-width: 700px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 12px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    h1 { text-align: center; color: #4A90E2; margin-bottom: 20px; }
    .filter-box-wrapper { text-align: center; margin-bottom: 20px; }
    .filter-box {
      display: inline-block;
      background: #e9f4fc;
      border: 1px solid #aed4f2;
      color: #357ABD;
      border-radius: 10px;
      padding: 10px 16px;
      font-size: 1rem;
      font-weight: 500;
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
    th { background-color: #4A90E2; color: #fff; }
    tr:nth-child(even) { background-color: #f9f9f9; }
    .chart-container { margin-top: 30px; }
  </style>
</head>
<body>
<div class="container">
  <h1>統計結果</h1>
  <div class="filter-box-wrapper">
    <div class="filter-box">
      <?php
      // 顯示篩選條件 => "支出項目：XXX - 月份：YYY"
      function getDisplayText($expenseItem, $monthFilter) {
          $itemText = empty($expenseItem) ? '全部' : htmlspecialchars($expenseItem);
          $monthText = empty($monthFilter) ? '全部' : htmlspecialchars($monthFilter);
          return "支出項目：{$itemText} - 月份：{$monthText}";
      }
      echo getDisplayText($expenseItem, $monthFilter);
      ?>
    </div>
  </div>
  <?php if (count($results) > 0): ?>
    <table>
      <tr>
        <?php if ($useDoughnut): ?>
          <th>支出項目</th>
          <th>件數</th>
          <th>金額</th>
        <?php else: ?>
          <th>統計月份</th>
          <th>件數</th>
          <th>總金額</th>
        <?php endif; ?>
      </tr>
      <?php foreach ($results as $row): ?>
        <tr>
          <?php if ($useDoughnut): ?>
            <td><?php echo htmlspecialchars($row['支出項目']); ?></td>
            <td><?php echo (isset($row['件數']) && is_numeric($row['件數'])) ? (int)$row['件數'] : 0; ?></td>
            <?php
            $amt = (isset($row['總金額']) && is_numeric($row['總金額'])) ? floatval($row['總金額']) : 0;
            echo '<td>NT$ ' . htmlspecialchars(formatAmount($amt)) . '</td>';
            ?>
          <?php else: ?>
            <td><?php echo htmlspecialchars($row['統計月份']); ?></td>
            <td><?php echo (isset($row['件數']) && is_numeric($row['件數'])) ? (int)$row['件數'] : 0; ?></td>
            <?php
            $amt = (isset($row['總金額']) && is_numeric($row['總金額'])) ? floatval($row['總金額']) : 0;
            echo '<td>NT$ ' . htmlspecialchars(formatAmount($amt)) . '</td>';
            ?>
          <?php endif; ?>
        </tr>
      <?php endforeach; ?>
    </table>
    <div class="chart-container">
      <canvas id="chartResult"></canvas>
    </div>
    <script>
      // JS 數字格式化：加逗號並去掉 .00（用於金額）
      function formatNumber(value) {
        let floatVal = parseFloat(value);
        let fixedVal = floatVal.toFixed(2);
        if (fixedVal.endsWith('.00')) {
          fixedVal = fixedVal.slice(0, -3);
        }
        return fixedVal.replace(/\B(?=(\d{3})+(?!\d))/g, ",");
      }
      var ctx = document.getElementById('chartResult').getContext('2d');
      var useDoughnut = <?php echo json_encode($useDoughnut); ?>;
      var chartType = useDoughnut ? 'doughnut' : 'bar';
      var labelsData = <?php echo $labelsJson; ?>;
      var data1 = <?php echo $data1Json; ?>;
      var data2 = <?php echo $data2Json; ?>;
      var datasets = [];
      
      if (useDoughnut) {
        // 甜甜圈圖只有一組 dataset (金額)
        datasets.push({
          label: '金額',
          data: data1,
          backgroundColor: [
            'rgba(75, 192, 192, 0.6)',
            'rgba(153, 102, 255, 0.6)',
            'rgba(255, 159, 64, 0.6)',
            'rgba(255, 205, 86, 0.6)',
            'rgba(201, 203, 207, 0.6)'
          ],
          borderColor: [
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(255, 205, 86, 1)',
            'rgba(201, 203, 207, 1)'
          ],
          borderWidth: 1
        });
      } else {
        // 長條圖：件數 + 總金額
        datasets.push({
          label: '件數',
          data: data1,
          backgroundColor: 'rgba(75, 192, 192, 0.6)',
          borderColor: 'rgba(75, 192, 192, 1)',
          borderWidth: 1,
          yAxisID: 'y'
        });
        datasets.push({
          label: '總金額',
          data: data2,
          backgroundColor: 'rgba(153, 102, 255, 0.6)',
          borderColor: 'rgba(153, 102, 255, 1)',
          borderWidth: 1,
          yAxisID: 'y1'
        });
      }
      
      var chartResult = new Chart(ctx, {
        type: chartType,
        data: {
          labels: labelsData,
          datasets: datasets
        },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: useDoughnut ? '支出項目佔比' : '每月統計 - 件數與總金額'
            },
            tooltip: {
              callbacks: {
                label: function(context) {
                  var rawValue = useDoughnut ? context.parsed : context.parsed.y;
                  var label = context.label || '';
                  if (useDoughnut) {
                    var total = context.dataset.data.reduce((sum, v) => sum + v, 0);
                    var percent = total ? ((rawValue / total) * 100).toFixed(2) + '%' : '0%';
                    return label + ': NT$ ' + formatNumber(rawValue) + ' (' + percent + ')';
                  } else {
                    if (context.dataset.label === '總金額') {
                      return label + ': NT$ ' + formatNumber(rawValue);
                    } else {
                      var intVal = Math.round(rawValue);
                      return label + ': ' + intVal.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                    }
                  }
                }
              }
            }
          },
          scales: useDoughnut ? {} : {
            y: {
              type: 'linear',
              display: true,
              position: 'left',
              beginAtZero: true,
              ticks: {
                callback: function(value) {
                  return parseInt(value, 10);
                }
              }
            },
            y1: {
              type: 'linear',
              display: true,
              position: 'right',
              beginAtZero: true,
              grid: { drawOnChartArea: false },
              ticks: {
                callback: function(value) {
                  return formatNumber(value);
                }
              }
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

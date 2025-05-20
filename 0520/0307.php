<?php
// 0307.php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

/** 格式化金額：保留兩位小數，.00 則去掉 */
function formatAmount($num) {
    $f = floatval($num);
    $formatted = number_format($f, 2);
    return substr($formatted, -3) === '.00'
        ? substr($formatted, 0, -3)
        : $formatted;
}

// 取得篩選參數
$rawItem     = $_GET['expenseItem']  ?? '全部';
$monthFilter = $_GET['monthFilter']  ?? '';
$expenseItem = $rawItem === '全部' ? '' : $rawItem;

// 判斷畫甜甜圈或長條圖
$useDoughnut = empty($expenseItem) && empty($monthFilter);

// 組主查詢 SQL
if ($useDoughnut) {
    $sql = "
    SELECT e.支出項目,
           COUNT(*) AS 件數,
           SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
    GROUP BY e.支出項目
    ORDER BY e.支出項目
    ";
    $params = [];
} else {
    $sql = "
    SELECT DATE_FORMAT(e.填表日期, '%Y-%m') AS 統計月份,
           COUNT(*) AS 件數,
           SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
    WHERE 1=1
    ";
    $params = [];

    if (!empty($expenseItem)) {
        if (strpos($expenseItem, '-') !== false) {
            list($mainCat, $subCat) = explode('-', $expenseItem, 2);
            if (trim($mainCat) === 'Y經濟扶助') {
                $sql .= " AND e.支出項目 LIKE :mainCat";
                $sql .= " AND e.經濟扶助 LIKE :subCat";
            } else {
                $sql .= " AND e.支出項目 LIKE :mainCat";
                $sql .= " AND e.專案活動 LIKE :subCat";
            }
            $params[':mainCat'] = "%{$mainCat}%";
            $params[':subCat']  = "%{$subCat}%";
        } else {
            $sql .= " AND e.支出項目 LIKE :mainCat";
            $params[':mainCat'] = "%{$expenseItem}%";
        }
    }
    if (!empty($monthFilter)) {
        $sql .= " AND DATE_FORMAT(e.填表日期, '%Y-%m') = :monthFilter";
        $params[':monthFilter'] = $monthFilter;
    }
    $sql .= " GROUP BY 統計月份 ORDER BY 統計月份 ASC";
}

try {
    // 建立 PDO 連線
    $pdo = new PDO("mysql:host=localhost:3307;dbname=基金會;charset=utf8", 'root', ' ');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 執行主查詢
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 如果是甜甜圈模式，再抓子類別明細
    if ($useDoughnut) {
        $detailSql = "
        SELECT e.支出項目 AS mainCat,
               CASE
                 WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
                 WHEN e.支出項目='W活動費用' THEN e.專案活動
                 ELSE NULL
               END AS subCat,
               COUNT(*) AS 件數,
               SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
        FROM 經辦業務檔 e
        JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
        WHERE e.支出項目 IN ('W活動費用','Y經濟扶助')
        GROUP BY mainCat, subCat
        ORDER BY mainCat, subCat
        ";
        $stmt2 = $pdo->prepare($detailSql);
        $stmt2->execute();
        $detailResults = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        // 按 mainCat 分組
        $detailMap = [];
        foreach ($detailResults as $d) {
            if ($d['subCat'] !== null) {
                $detailMap[$d['mainCat']][] = $d;
            }
        }
    }

} catch (PDOException $e) {
    die("資料庫錯誤：" . $e->getMessage());
}

// 整理 Chart.js 資料
$labels  = $counts = $amounts = $months = [];
foreach ($results as $r) {
    if ($useDoughnut) {
        $labels[]  = $r['支出項目'];
        $counts[]  = intval($r['件數']);
        $amounts[] = floatval($r['總金額']);
    } else {
        $months[]  = $r['統計月份'];
        $counts[]  = intval($r['件數']);
        $amounts[] = floatval($r['總金額']);
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>統計結果</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Poppins',sans-serif; background:#f4f7f9; padding:20px; }
    .container {
      max-width:760px;
      margin:0 auto;
      background:#fff;
      padding:30px;
      border-radius:12px;
      box-shadow:0 4px 12px rgba(0,0,0,0.05);
    }
    h1 { text-align:center; color:#3b6ea0; margin-bottom:20px; font-weight:600; }
    .filter-box-wrapper { text-align:center; margin-bottom:20px; }
    .filter-box {
      display:inline-block;
      background:#e3f2fd;
      border:1px solid:#bbdefb;
      color:#1e88e5;
      border-radius:8px;
      padding:8px 16px;
      font-size:1rem;
      font-weight:500;
    }
    table { width:100%; border-collapse:collapse; margin-top:20px; font-size:0.95rem; }
    th, td { padding:10px; border:1px solid:#ddd; text-align:center; }
    th { background:#3b6ea0; color:#fff; }
    tr:nth-child(even) { background:#f7f9fc; }
    .main-row { cursor:pointer; }
    .main-row:hover { background:#e8f4fb; }
    .sub-row { display:none; background:#fafafa; }
    .inner-table {
      width:100%; border-collapse:collapse; margin:10px 0;
    }
    .inner-table th, .inner-table td {
      padding:6px; border:1px solid:#ccc; text-align:center; font-size:0.9rem;
    }
    .inner-table th { background:#f0f0f0; }
    .chart-container { margin-top:30px; display:flex; justify-content:center; }
  </style>
</head>
<body>
  <div class="container">
    <h1>統計結果</h1>
    <div class="filter-box-wrapper">
      <div class="filter-box">
        <?php
          if ($useDoughnut) {
            echo '支出項目：全部 — 月份：全部';
          } else {
            $dispItem  = empty($expenseItem) ? '全部' : htmlspecialchars($rawItem);
            $dispMonth = empty($monthFilter) ? '全部' : htmlspecialchars($monthFilter);
            echo "支出項目：{$dispItem} — 月份：{$dispMonth}";
          }
        ?>
      </div>
    </div>

    <?php if (count($results)): ?>
      <table>
        <tr>
          <?php if ($useDoughnut): ?>
            <th>支出項目</th><th>件數</th><th>總金額</th>
          <?php else: ?>
            <th>統計月份</th><th>件數</th><th>總金額</th>
          <?php endif; ?>
        </tr>
        <?php foreach ($results as $r): ?>
          <tr class="main-row" data-main="<?= htmlspecialchars($useDoughnut ? $r['支出項目'] : $r['統計月份']) ?>">
            <?php if ($useDoughnut): ?>
              <td><?= htmlspecialchars($r['支出項目']) ?></td>
            <?php else: ?>
              <td><?= htmlspecialchars($r['統計月份']) ?></td>
            <?php endif; ?>
            <td><?= intval($r['件數']) ?></td>
            <td>NT$ <?= formatAmount($r['總金額']) ?></td>
          </tr>
          <?php if ($useDoughnut && isset($detailMap[$r['支出項目']])): ?>
          <tr class="sub-row" data-parent="<?= htmlspecialchars($r['支出項目']) ?>">
            <td colspan="3">
              <table class="inner-table">
                <tr><th>細項</th><th>件數</th><th>總金額</th></tr>
                <?php foreach ($detailMap[$r['支出項目']] as $d): ?>
                <tr>
                  <td><?= htmlspecialchars($d['subCat']) ?></td>
                  <td><?= intval($d['件數']) ?></td>
                  <td>NT$ <?= formatAmount($d['總金額']) ?></td>
                </tr>
                <?php endforeach; ?>
              </table>
            </td>
          </tr>
          <?php endif; ?>
        <?php endforeach; ?>
      </table>

      <div class="chart-container">
        <canvas id="chartResult"></canvas>
      </div>
    <?php else: ?>
      <p>尚無資料。</p>
    <?php endif; ?>
  </div>

  <script>
    // 主列點擊展開/收合子列
    document.querySelectorAll('.main-row').forEach(row => {
      row.addEventListener('click', () => {
        const key = row.getAttribute('data-main');
        document.querySelectorAll(`.sub-row[data-parent="${key}"]`)
          .forEach(sub => {
            sub.style.display = sub.style.display === 'table-row' ? 'none' : 'table-row';
          });
      });
    });

    // 繪製 Chart.js
    Chart.register(ChartDataLabels);
    const useDoughnut = <?= json_encode($useDoughnut) ?>;
    const labels      = <?= json_encode($useDoughnut ? $labels : $months) ?>;
    const counts      = <?= json_encode($counts) ?>;
    const amounts     = <?= json_encode($amounts) ?>;
    const palette     = ['#4e79a7','#f28e2c','#e15759','#76b7b2','#59a14f'];

    const datasets = useDoughnut
      ? [{
          label: '總金額',
          data: amounts,
          backgroundColor: palette,
          hoverOffset: 8,
          datalabels: {
            color: '#ffffff',
            textStrokeColor: '#000000',
            textStrokeWidth: 2,
            anchor: 'end',
            align: 'outside',
            formatter: (value, ctx) => {
              const idx = ctx.dataIndex;
              const count = counts[idx];
              const sum = amounts.reduce((a,b)=>a+b,0);
              const pct = ((value/sum)*100).toFixed(1);
              return `件數 ${count}\nNT$ ${value}`;
            },
            font: { weight: '600', size: 12 }
          }
        }]
      : [
          {
            label: '件數',
            data: counts,
            backgroundColor: 'rgba(78,121,167,0.6)',
            borderColor: 'rgba(78,121,167,1)',
            yAxisID: 'y',
            datalabels: {
              backgroundColor: 'rgba(255,255,255,0.8)',
              borderRadius: 4,
              color: '#000000',
              anchor: 'end',
              align: 'start',
              offset: 2,
              formatter: v => v,
              font: { weight: '500', size: 11 }
            }
          },
          {
            label: '總金額',
            data: amounts,
            backgroundColor: 'rgba(242,142,44,0.6)',
            borderColor: 'rgba(242,142,44,1)',
            yAxisID: 'y1',
            datalabels: {
              backgroundColor: 'rgba(255,255,255,0.8)',
              borderRadius: 4,
              color: '#000000',
              anchor: 'end',
              align: 'start',
              offset: 2,
              formatter: v => 'NT$ ' + v,
              font: { weight: '500', size: 11 }
            }
          }
        ];

    new Chart(
      document.getElementById('chartResult').getContext('2d'),
      {
        type: useDoughnut ? 'doughnut' : 'bar',
        data: { labels, datasets },
        options: {
          responsive: true,
          plugins: {
            title: {
              display: true,
              text: useDoughnut ? '支出項目佔比' : '每月 件數／總金額',
              font: { size: 16, weight: '600' }
            },
            datalabels: {}
          },
          scales: useDoughnut ? {} : {
            y: {
              type: 'linear',
              position: 'left',
              beginAtZero: true,
              title: { display: true, text: '件數' },
              ticks: { stepSize: 1, precision: 0 }
            },
            y1: {
              type: 'linear',
              position: 'right',
              beginAtZero: true,
              grid: { drawOnChartArea: false },
              title: { display: true, text: '總金額 (NT$)' },
              ticks: { callback: value => 'NT$ ' + value }
            }
          }
        }
      }
    );
  </script>
</body>
</html>

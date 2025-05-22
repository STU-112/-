<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

/** 格式化金額：加千分位、保留兩位小數，.00 則去掉 */
function formatAmount($num) {
    $f = floatval($num);
    $s = number_format($f, 2, '.', ',');
    return substr($s, -3) === '.00' ? substr($s, 0, -3) : $s;
}

// 取得篩選參數
$rawItem     = isset($_GET['expenseItem'])  ? $_GET['expenseItem']  : '全部';
$monthFilter = isset($_GET['monthFilter']) ? $_GET['monthFilter'] : '';
$expenseItem = ($rawItem === '全部') ? '' : $rawItem;
$useDoughnut = empty($expenseItem) && empty($monthFilter);
$detailItem  = isset($_GET['detailItem'])  ? $_GET['detailItem']  : null;

// 建立 PDO 連線
$pdo = new PDO("mysql:host=localhost:3307;dbname=基金會;charset=utf8", 'root', ' ');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// —— 細項頁面 ——
if ($detailItem) {
    // 細項彙總
    $sql1 = "
      SELECT
        CASE
          WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
          WHEN e.支出項目='W活動費用' THEN e.專案活動
          ELSE '其他'
        END AS subCat,
        COUNT(*) AS 件數,
        SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
      FROM 經辦業務檔 e
      JOIN 經辦人交易檔 t USING (業務代號)
      WHERE e.支出項目 = :mc
      GROUP BY subCat
      ORDER BY subCat
    ";
    $stmt1 = $pdo->prepare($sql1);
    $stmt1->bindValue(':mc', $detailItem);
    $stmt1->execute();
    $detailRows = $stmt1->fetchAll(PDO::FETCH_ASSOC);

    // 完整明細
    $sql2 = "
      SELECT
        CASE
          WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
          WHEN e.支出項目='W活動費用' THEN e.專案活動
          ELSE '其他'
        END AS subCat,
        e.業務代號, e.經辦代號, t.受款人代號, t.金額, e.填表日期
      FROM 經辦業務檔 e
      JOIN 經辦人交易檔 t USING (業務代號)
      WHERE e.支出項目 = :mc
      ORDER BY subCat, e.填表日期
    ";
    $stmt2 = $pdo->prepare($sql2);
    $stmt2->bindValue(':mc', $detailItem);
    $stmt2->execute();
    $fullRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

    // 分組
    $fullMap = [];
    foreach ($fullRows as $r) {
        $fullMap[$r['subCat']][] = $r;
    }
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($detailItem) ?> 統計</title>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Poppins',sans-serif;background:#eef6f4;padding:20px;}
    .container{max-width:760px;margin:0 auto;background:#fff;padding:30px;border-radius:12px;}
    h1{text-align:center;color:#3b6ea0;margin-bottom:20px;font-weight:600;}
    .summary-wrapper{text-align:center;margin-bottom:20px;}
    .filter-summary{
      display:inline-block;background:#e3f2fd;padding:8px 16px;
      border-radius:6px;color:#0277bd;font-weight:600;
    }
    table{width:100%;border-collapse:collapse;margin-bottom:20px;}
    th,td{padding:10px;border:1px solid #ddd;text-align:center;}
    th{background:#3b6ea0;color:#fff;}
    tr:nth-child(even){background:#f7f9fc;}
    .count-cell{cursor:pointer;color:#1e88e5;}
    .full-section{display:none;background:#fafafa;}
    .record-table{width:100%;border-collapse:collapse;margin-top:10px;}
    .record-table th,.record-table td{padding:6px;border:1px solid #ccc;text-align:center;}
    .record-table th{background:#f0f0f0;color:#555;}
  </style>
</head>
<body>
  <div class="container">
    <h1><?= htmlspecialchars($detailItem) ?> 統計</h1>
    <div class="summary-wrapper">
      <div class="filter-summary">
        支出項目：<?= htmlspecialchars($detailItem) ?> — 月份：<?= htmlspecialchars($monthFilter ?: '全部') ?>
      </div>
    </div>

    <?php if (count($detailRows) === 0): ?>
      <p style="text-align:center;color:#666;">此分類底下無資料。</p>
    <?php else: ?>
      <table>
        <tr><th>細項</th><th>件數</th><th>總金額</th></tr>
        <?php foreach ($detailRows as $d): $sub = $d['subCat']; ?>
        <tr class="summary-row" data-sub="<?= htmlspecialchars($sub) ?>">
          <td><?= htmlspecialchars($sub) ?></td>
          <td class="count-cell"><?= intval($d['件數']) ?></td>
          <td>NT$ <?= formatAmount($d['總金額']) ?></td>
        </tr>
        <tr class="full-section" data-parent="<?= htmlspecialchars($sub) ?>">
          <td colspan="3">
            <table class="record-table">
              <tr>
                <th>業務代號</th><th>經辦代號</th><th>受款人代號</th>
                <th>金額</th><th>填表日期</th>
              </tr>
              <?php if (isset($fullMap[$sub])): foreach ($fullMap[$sub] as $r): ?>
              <tr>
                <td><?= htmlspecialchars($r['業務代號']) ?></td>
                <td><?= htmlspecialchars($r['經辦代號']) ?></td>
                <td><?= htmlspecialchars($r['受款人代號']) ?></td>
                <td>NT$ <?= formatAmount($r['金額']) ?></td>
                <td><?= htmlspecialchars($r['填表日期']) ?></td>
              </tr>
              <?php endforeach; else: ?>
              <tr><td colspan="5" style="text-align:center;color:#666;">無更多資料</td></tr>
              <?php endif; ?>
            </table>
          </td>
        </tr>
        <?php endforeach; ?>
      </table>
    <?php endif; ?>
  </div>
  <div style="text-align:center;margin:20px;">
    <a href="javascript:history.back()" style="color:#2a7f7d;text-decoration:none;font-weight:600;">
      ← 回上一頁
    </a>
  </div>
  <script>
    document.querySelectorAll('.count-cell').forEach(function(cell) {
      cell.addEventListener('click', function() {
        var sub = this.closest('tr').getAttribute('data-sub');
        var sec = document.querySelector('.full-section[data-parent="' + sub + '"]');
        sec.style.display = (sec.style.display === 'table-row' ? 'none' : 'table-row');
      });
    });
  </script>
</body>
</html>
<?php
    exit;
}

// —— Summary + Chart 頁面 ——
if ($useDoughnut) {
    $sql = "
      SELECT e.支出項目 AS keyName,
             COUNT(*) AS 件數,
             SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
      FROM 經辦業務檔 e
      JOIN 經辦人交易檔 t USING (業務代號)
      GROUP BY e.支出項目
      ORDER BY e.支出項目
    ";
    $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} else {
    $sql = "
      SELECT DATE_FORMAT(e.填表日期,'%Y-%m') AS keyName,
             COUNT(*) AS 件數,
             SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
      FROM 經辦業務檔 e
      JOIN 經辦人交易檔 t USING (業務代號)
      WHERE 1=1
    ";
    $params = [];
    if ($expenseItem !== '') {
        if (strpos($expenseItem, '-') !== false) {
            list($mc, $sc) = explode('-', $expenseItem, 2);
            $sql .= " AND e.支出項目 = :mc AND (e.經濟扶助 LIKE :sc OR e.專案活動 LIKE :sc)";
            $params[':mc'] = $mc;
            $params[':sc'] = "%{$sc}%";
        } else {
            $sql .= " AND e.支出項目 = :mc";
            $params[':mc'] = $expenseItem;
        }
    }
    if ($monthFilter !== '') {
        $sql .= " AND DATE_FORMAT(e.填表日期,'%Y-%m') = :mf";
        $params[':mf'] = $monthFilter;
    }
    $sql .= " GROUP BY keyName ORDER BY keyName";
    $stmt = $pdo->prepare($sql);
    foreach ($params as $k => $v) {
        $stmt->bindValue($k, $v);
    }
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Chart.js 資料
$labels = $counts = $amounts = [];
foreach ($results as $r) {
    $labels[]  = $r['keyName'];
    $counts[]  = intval($r['件數']);
    $amounts[] = floatval($r['總金額']);
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
    *{margin:0;padding:0;box-sizing:border-box;}
    body{font-family:'Poppins',sans-serif;background:#f4f9f8;padding:20px;}
    .container{max-width:760px;margin:0 auto;background:#fff;padding:30px;border-radius:12px;}
    h1{text-align:center;color:#3b6ea0;margin-bottom:20px;font-weight:600;}
    .summary-wrapper{text-align:center;}
    .filter-summary{
      display:inline-block;background:#e3f2fd;padding:8px 16px;
      border-radius:6px;margin-bottom:20px;color:#0277bd;font-weight:600;
    }
    table{width:100%;border-collapse:collapse;font-size:0.95rem;}
    th,td{padding:10px;border:1px solid #ddd;text-align:center;}
    th{background:#3b6ea0;color:#fff;}
    tr:nth-child(even){background:#f7f9fc;}
    table a,table a:visited{color:inherit;text-decoration:none;}
    table a:hover{text-decoration:underline;}
    .no-data{text-align:center;color:#666;padding:40px 0;}
    .chart-container{
      margin-top:30px;display:flex;justify-content:center;
      padding:16px;border:1px solid #ddd;border-radius:8px;background:#fff;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>統計結果</h1>
    <div class="summary-wrapper">
      <div class="filter-summary">
        支出項目：<?= htmlspecialchars($expenseItem ?: '全部') ?> — 月份：<?= htmlspecialchars($monthFilter ?: '全部') ?>
      </div>
    </div>
    <?php if (empty($results)): ?>
      <p class="no-data">查無資料，請調整篩選條件後重試。</p>
    <?php else: ?>
      <table>
        <tr>
          <th><?= $useDoughnut ? '支出項目' : '月份' ?></th>
          <th>件數</th>
          <th>總金額</th>
        </tr>
        <?php foreach ($results as $r): ?>
        <tr>
          <td>
            <?php if ($useDoughnut): ?>
              <a href="?expenseItem=<?= urlencode($rawItem) ?>&monthFilter=<?= urlencode($monthFilter) ?>&detailItem=<?= urlencode($r['keyName']) ?>">
                <?= htmlspecialchars($r['keyName']) ?>
              </a>
            <?php else: ?>
              <?= htmlspecialchars($r['keyName']) ?>
            <?php endif; ?>
          </td>
          <td><?= intval($r['件數']) ?></td>
          <td>NT$ <?= formatAmount($r['總金額']) ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <div class="chart-container">
        <canvas id="chartResult"></canvas>
      </div>
    <?php endif; ?>
  </div>
  <div style="text-align:center;margin:20px;">
    <a href="javascript:history.back()" style="color:#2a7f7d;text-decoration:none;font-weight:600;">
      ← 回上一頁
    </a>
  </div>
  <script>
    Chart.register(ChartDataLabels);
    const useDoughnut = <?= json_encode($useDoughnut) ?>,
          labels      = <?= json_encode($labels) ?>,
          counts      = <?= json_encode($counts) ?>,
          amounts     = <?= json_encode($amounts) ?>,
          palette     = ['#4e79a7','#f28e2c','#e15759','#76b7b2','#59a14f'];

    const datasets = useDoughnut
      ? [{ label:'總金額', data:amounts, backgroundColor:palette, hoverOffset:8,
           datalabels:{ color:'#fff', textStrokeColor:'#000', textStrokeWidth:2,
             anchor:'end', align:'outside',
             formatter:(v,ctx)=>`件數 ${counts[ctx.dataIndex]}\nNT$ ${v.toLocaleString()}`
           }
        }]
      : [
        { label:'件數', data:counts,
          backgroundColor:'rgba(78,121,167,0.6)', borderColor:'rgba(78,121,167,1)',
          yAxisID:'y',
          datalabels:{ backgroundColor:'#fff', borderColor:'#ccc', borderWidth:1,
            borderRadius:4, padding:4, color:'#000',
            formatter:v=>v.toLocaleString(), anchor:'end', align:'start'
          }
        },
        { label:'總金額', data:amounts,
          backgroundColor:'rgba(242,142,44,0.6)', borderColor:'rgba(242,142,44,1)',
          yAxisID:'y1',
          datalabels:{ backgroundColor:'#fff', borderColor:'#ccc', borderWidth:1,
            borderRadius:4, padding:4, color:'#000',
            formatter:v=>'NT$ '+v.toLocaleString(), anchor:'end', align:'start'
          }
        }
      ];

    const options = {
      responsive:true,
      plugins:{ title:{ display:true,
        text: useDoughnut?'支出項目佔比':'每月 件數／總金額',
        font:{ size:16, weight:'600' }
      }},
      scales: useDoughnut?{}:{
        y:{ type:'linear', position:'left', beginAtZero:true,
           title:{ display:true, text:'件數' }, ticks:{ stepSize:1, precision:0 } },
        y1:{ type:'linear', position:'right', beginAtZero:true,
            grid:{ drawOnChartArea:false },
            title:{ display:true, text:'總金額 (NT$)' },
            ticks:{ callback:v=>'NT$ '+v.toLocaleString() }
        }
      }
    };

    if (!<?= json_encode(empty($results)) ?>) {
      new Chart(
        document.getElementById('chartResult').getContext('2d'),
        { type: useDoughnut?'doughnut':'bar', data:{ labels, datasets }, options }
      );
    }
  </script>
</body>
</html>

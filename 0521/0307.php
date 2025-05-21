<?php  
// 檔名：0307.php
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

// 判斷是否甜甜圈模式（無篩選項目、無月份）
$useDoughnut = empty($expenseItem) && empty($monthFilter);

try {
    // 建立 PDO 連線
    $pdo = new PDO("mysql:host=localhost:3307;dbname=基金會;charset=utf8", 'root', ' ');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($useDoughnut) {
        // 1) 主查詢：各支出項目匯總
        $sql = "
          SELECT
            e.支出項目 AS keyName,
            COUNT(*) AS 件數,
            SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
          FROM 經辦業務檔 e
          JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
          GROUP BY e.支出項目
          ORDER BY e.支出項目
        ";
        $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        // 2) 細項彙總
        $detailSql = "
          SELECT
            e.支出項目 AS mainCat,
            CASE
              WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
              WHEN e.支出項目='W活動費用' THEN e.專案活動
              ELSE '其他'
            END AS subCat,
            COUNT(*) AS 件數,
            SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
          FROM 經辦業務檔 e
          JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
          WHERE e.支出項目 IN ('W活動費用','Y經濟扶助')
          GROUP BY mainCat, subCat
          ORDER BY mainCat, subCat
        ";
        $detailResults = $pdo->query($detailSql)->fetchAll(PDO::FETCH_ASSOC);

        // 將細項彙總按 mainCat 分組
        $detailMap = [];
        foreach ($detailResults as $d) {
            $detailMap[$d['mainCat']][] = $d;
        }

        // 3) 完整明細：每個 subCat 下所有記錄
        $fullSql = "
          SELECT
            e.支出項目 AS mainCat,
            CASE
              WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
              WHEN e.支出項目='W活動費用' THEN e.專案活動
              ELSE '其他'
            END AS subCat,
            e.業務代號    AS 業務代號,
            e.經辦代號    AS 經辦代號,
            t.受款人代號  AS 受款人代號,
            t.金額        AS 金額,
            e.填表日期    AS 填表日期,
            t.審核狀態    AS 審核狀態
          FROM 經辦業務檔 e
          JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
          WHERE e.支出項目 IN ('W活動費用','Y經濟扶助')
          ORDER BY e.支出項目, subCat, e.填表日期
        ";
        $fullResults = $pdo->query($fullSql)->fetchAll(PDO::FETCH_ASSOC);

        // 按 subKey = mainCat-subCat 分組
        $fullMap = [];
        foreach ($fullResults as $r) {
            $key = $r['mainCat'] . '-' . $r['subCat'];
            $fullMap[$key][] = $r;
        }
    }
    else {
        // 非甜甜圈模式：依篩選條件分組
        $sql = "
          SELECT
            DATE_FORMAT(e.填表日期,'%Y-%m') AS keyName,
            COUNT(*) AS 件數,
            SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
          FROM 經辦業務檔 e
          JOIN 經辦人交易檔 t ON e.業務代號 = t.業務代號
          WHERE 1=1
        ";
        $params = [];

        if (!empty($expenseItem)) {
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
        if (!empty($monthFilter)) {
            $sql .= " AND DATE_FORMAT(e.填表日期,'%Y-%m') = :mf";
            $params[':mf'] = $monthFilter;
        }
        $sql .= " GROUP BY keyName ORDER BY keyName ASC";

        $stmt = $pdo->prepare($sql);
        foreach ($params as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    die("資料庫錯誤：" . $e->getMessage());
}

// 準備 Chart.js 資料
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
    body{font-family:'Poppins',sans-serif;background:#f4f7f9;padding:20px;}
    .container{max-width:760px;margin:0 auto;background:#fff;padding:30px;border-radius:12px;
               box-shadow:0 4px 12px rgba(0,0,0,0.05);}
    h1{text-align:center;color:#3b6ea0;margin-bottom:20px;font-weight:600;}
    table{width:100%;border-collapse:collapse;margin-top:20px;font-size:0.95rem;}
    th,td{padding:10px;border:1px solid:#ddd;text-align:center;}
    th{background:#3b6ea0;color:#fff;}
    tr:nth-child(even){background:#f7f9fc;}
    .count-cell, .sub-count{color:#1e88e5;cursor:pointer;}
    .detail-section, .record-section, .records2-section{display:none;background:#fafafa;}
    .record-table, .record2-table{width:100%;border-collapse:collapse;margin:10px 0;}
    .record-table th,.record-table td,
    .record2-table th,.record2-table td{padding:6px;border:1px solid:#ccc;text-align:center;font-size:0.9rem;}
    .record-table th, .record2-table th{background:#f0f0f0;color:#555;}
    .chart-container{margin-top:30px;display:flex;justify-content:center;}
  </style>
</head>
<body>
  <div class="container">
    <h1>統計結果</h1>

    <table>
      <tr>
        <th><?= $useDoughnut ? '支出項目' : '月份' ?></th>
        <th>件數</th>
        <th>總金額</th>
      </tr>
      <?php foreach ($results as $r):
        $key = $r['keyName'];
      ?>
      <!-- 主列 -->
      <tr class="main-row" data-key="<?= htmlspecialchars($key) ?>">
        <td><?= htmlspecialchars($key) ?></td>
        <td class="count-cell"><?= intval($r['件數']) ?></td>
        <td>NT$ <?= formatAmount($r['總金額']) ?></td>
      </tr>
      <?php if ($useDoughnut && isset($detailMap[$key])): ?>
      <!-- 細項彙總列 -->
      <tr class="detail-section" data-parent="<?= htmlspecialchars($key) ?>">
        <td colspan="3">
          <table class="record-table">
            <tr><th>細項</th><th>件數</th><th>總金額</th></tr>
            <?php foreach ($detailMap[$key] as $d):
              $subKey = $key . '-' . $d['subCat'];
            ?>
            <tr class="detail-row" data-detail="<?= htmlspecialchars($subKey) ?>">
              <td><?= htmlspecialchars($d['subCat']) ?></td>
              <td class="sub-count"><?= intval($d['件數']) ?></td>
              <td>NT$ <?= formatAmount($d['總金額']) ?></td>
            </tr>
            <!-- 完整明細列 -->
            <?php if (isset($fullMap[$subKey])): ?>
            <tr class="records2-section" data-parent2="<?= htmlspecialchars($subKey) ?>">
              <td colspan="3">
                <table class="record2-table">
                  <tr>
                    <th>業務代號</th><th>經辦代號</th><th>受款人代號</th>
                    <th>金額</th><th>填表日期</th><th>審核狀態</th>
                  </tr>
                  <?php foreach ($fullMap[$subKey] as $rec): ?>
                  <tr>
                    <td><?= htmlspecialchars($rec['業務代號']) ?></td>
                    <td><?= htmlspecialchars($rec['經辦代號']) ?></td>
                    <td><?= htmlspecialchars($rec['受款人代號']) ?></td>
                    <td><?= htmlspecialchars($rec['金額']) ?></td>
                    <td><?= htmlspecialchars($rec['填表日期']) ?></td>
                    <td><?= htmlspecialchars($rec['審核狀態']) ?></td>
                  </tr>
                  <?php endforeach; ?>
                </table>
              </td>
            </tr>
            <?php endif; ?>
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
  </div>

  <script>
    // 點擊件數顯示/隱藏細項彙總
    document.querySelectorAll('.count-cell').forEach(cell=>{
      cell.addEventListener('click',()=>{
        const key = cell.closest('tr').dataset.key;
        document.querySelector(`.detail-section[data-parent="${key}"]`).style.display =
          document.querySelector(`.detail-section[data-parent="${key}"]`).style.display==='table-row'
            ? 'none' : 'table-row';
      });
    });
    // 點擊細項件數顯示/隱藏完整明細
    document.querySelectorAll('.sub-count').forEach(cell=>{
      cell.addEventListener('click',()=>{
        const subKey = cell.closest('tr').dataset.detail;
        document.querySelector(`.records2-section[data-parent2="${subKey}"]`).style.display =
          document.querySelector(`.records2-section[data-parent2="${subKey}"]`).style.display==='table-row'
            ? 'none' : 'table-row';
      });
    });

    // 繪製 Chart.js
    Chart.register(ChartDataLabels);
    const useDoughnut = <?= json_encode($useDoughnut) ?>;
    const labels      = <?= json_encode($labels) ?>;
    const counts      = <?= json_encode($counts) ?>;
    const amounts     = <?= json_encode($amounts) ?>;
    const palette     = ['#4e79a7','#f28e2c','#e15759','#76b7b2','#59a14f'];

    const datasets = useDoughnut
      ? [{
          label:'總金額', data:amounts, backgroundColor:palette,
          hoverOffset:8,
          datalabels:{
            color:'#fff', textStrokeColor:'#000', textStrokeWidth:2,
            anchor:'end', align:'outside',
            formatter:(v,ctx)=>`件數 ${counts[ctx.dataIndex]}\nNT$ ${v}`,
            font:{weight:'600',size:12}
          }
        }]
      : [
          { label:'件數', data:counts, backgroundColor:'rgba(78,121,167,0.6)',
            borderColor:'rgba(78,121,167,1)', yAxisID:'y',
            datalabels:{anchor:'end',align:'start',formatter:v=>v,font:{weight:'500',size:11}}
          },
          { label:'總金額', data:amounts, backgroundColor:'rgba(242,142,44,0.6)',
            borderColor:'rgba(242,142,44,1)', yAxisID:'y1',
            datalabels:{anchor:'end',align:'start',formatter:v=>'NT$ '+v,font:{weight:'500',size:11}}
          }
        ];

    new Chart(document.getElementById('chartResult').getContext('2d'), {
      type: useDoughnut?'doughnut':'bar',
      data:{labels,datasets},
      options:{
        responsive:true,
        plugins:{
          title:{display:true,text:useDoughnut?'支出項目佔比':'每月 件數／總金額',font:{size:16,weight:'600'}},
          datalabels:{}
        },
        scales: useDoughnut?{}:{
          y:{type:'linear',position:'left',beginAtZero:true,title:{display:true,text:'件數'},ticks:{stepSize:1,precision:0}},
          y1:{type:'linear',position:'right',beginAtZero:true,grid:{drawOnChartArea:false},title:{display:true,text:'總金額 (NT$)'},ticks:{callback:v=>'NT$ '+v}}
        }
      }
    });
  </script>
</body>
</html>

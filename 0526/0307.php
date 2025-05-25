<?php
// 檔名：0307.php
error_reporting(E_ALL);
ini_set('display_errors',1);
date_default_timezone_set('Asia/Taipei');

// 資料庫連線
$db_host = "localhost";
$db_port = "3307";
$db_user = "root";
$db_pass = " ";
$db_name = "基金會";
$dsn     = "mysql:host={$db_host};port={$db_port};dbname={$db_name};charset=utf8mb4";
try {
  $pdo = new PDO($dsn, $db_user, $db_pass);
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("資料庫連線失敗: " . $e->getMessage());
}

// 數字格式化
function formatAmount($num) {
  $f = floatval($num);
  $s = number_format($f,2,'.',',');
  return substr($s,-3)==='.00' ? substr($s,0,-3) : $s;
}

// 取得篩選參數
$rawItem     = $_GET['expenseItem']  ?? '全部';
$monthFilter = $_GET['monthFilter']  ?? '';
$expenseItem = $rawItem==='全部'? '' : $rawItem;
$useDoughnut = empty($expenseItem) && empty($monthFilter);

// 支援 drill-down
$detailItem  = $_GET['detailItem']  ?? null;
$detailMonth = $_GET['detailMonth'] ?? null;

/* ====== (A) 支出項目→細項 (原有 detailItem) ====== */
if ($detailItem) {
  // 判斷是否為子分類
  if (strpos($detailItem,'-')!==false) {
    list($mainCat,$subCat) = explode('-',$detailItem,2);
    $filterMain     = $mainCat;
    $filterSubField = $mainCat==='Y經濟扶助'
      ? '經濟扶助'
      : ($mainCat==='W活動費用'? '專案活動' : null);
  } else {
    $filterMain     = $detailItem;
    $filterSubField = null;
  }

  // (A) 細項彙總
  $sql1 = "
    SELECT
      CASE
        WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
        WHEN e.支出項目='W活動費用' THEN e.專案活動
        ELSE '其他'
      END AS subCat,
      COUNT(*) AS 件數,
      SUM(CAST(REPLACE(t.金額,',','') AS DECIMAL(10,2))) AS 總金額
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t USING(業務代號)
    WHERE e.支出項目 = :mc"
    .($filterSubField?" AND e.`{$filterSubField}` = :sc":'')
    .($monthFilter?" AND DATE_FORMAT(e.填表日期,'%Y-%m') = :mf":'')
    ." GROUP BY subCat ORDER BY subCat";
  $stmt1 = $pdo->prepare($sql1);
  $stmt1->bindValue(':mc',$filterMain);
  if ($filterSubField) $stmt1->bindValue(':sc',$subCat);
  if ($monthFilter)   $stmt1->bindValue(':mf',$monthFilter);
  $stmt1->execute();
  $detailRows = $stmt1->fetchAll(PDO::FETCH_ASSOC);

  // (B) 完整明細
  $sql2 = "
    SELECT
      CASE
        WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
        WHEN e.支出項目='W活動費用' THEN e.專案活動
        ELSE '其他'
      END AS subCat,
      e.業務代號, e.經辦代號, t.受款人代號, t.金額, e.填表日期
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t USING(業務代號)
    WHERE e.支出項目 = :mc"
    .($filterSubField?" AND e.`{$filterSubField}` = :sc":'')
    .($monthFilter?" AND DATE_FORMAT(e.填表日期,'%Y-%m') = :mf":'')
    ." ORDER BY subCat, e.填表日期";
  $stmt2 = $pdo->prepare($sql2);
  $stmt2->bindValue(':mc',$filterMain);
  if ($filterSubField) $stmt2->bindValue(':sc',$subCat);
  if ($monthFilter)   $stmt2->bindValue(':mf',$monthFilter);
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
    <title><?=htmlspecialchars($detailItem)?> 統計</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
      *{margin:0;padding:0;box-sizing:border-box;}
      body{font-family:'Poppins',sans-serif;background:#eef6f4;padding:20px;}
      .container{max-width:760px;margin:0 auto;background:#fff;padding:30px;border-radius:12px;}
      h1{text-align:center;color:#3b6ea0;margin:20px 0;font-weight:600;}
      .summary-wrapper{text-align:center;margin-bottom:20px;}
      .filter-summary{display:inline-block;background:#e3f2fd;padding:8px 16px;border-radius:6px;color:#0277bd;font-weight:600;}
      .search-box{text-align:right;margin-bottom:10px;}
      .search-box input{padding:6px 8px;border:1px solid #ccc;border-radius:4px;width:80%;max-width:400px;}
      table{width:100%;border-collapse:collapse;margin-bottom:20px;}
      th,td{padding:10px;border:1px solid #ddd;text-align:center;}
      th{background:#3b6ea0;color:#fff;}
      tr:nth-child(even){background:#f7f9fc;}
      .count-cell{cursor:pointer;color:#1e88e5;}
      .full-section{display:none;}
      .record-table{width:100%;border-collapse:collapse;margin-top:10px;}
      .record-table th,.record-table td{padding:8px;border:1px solid #ccc;text-align:center;}
      .record-table th{background:#f0f0f0;color:#555;}
      @media print { .back-link{display:none!important;} }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="search-box">
        <label>關鍵字篩選：
          <input type="text" id="searchInput" placeholder="輸入關鍵字，以空格或逗號分隔">
        </label>
      </div>
      <h1><?=htmlspecialchars($detailItem)?> 統計</h1>
      <div class="summary-wrapper">
        <div class="filter-summary">
          支出項目：<?=htmlspecialchars($detailItem)?> — 月份：<?=htmlspecialchars($monthFilter?:'全部')?>
        </div>
      </div>
      <?php if (empty($detailRows)): ?>
        <p style="text-align:center;color:#666;">此分類底下無資料。</p>
      <?php else: ?>
        <table id="summaryTable">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAllSummary"></th>
              <th>細項</th>
              <th>件數</th>
              <th>總金額</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($detailRows as $d): $sub=$d['subCat']; ?>
            <tr class="summary-row" data-sub="<?=htmlspecialchars($sub)?>">
              <td><input type="checkbox" class="summary-select" data-sub="<?=htmlspecialchars($sub)?>" checked></td>
              <td><?=htmlspecialchars($sub)?></td>
              <td class="count-cell"><?=intval($d['件數'])?></td>
              <td>NT$ <?=formatAmount($d['總金額'])?></td>
            </tr>
            <tr class="full-section" data-parent="<?=htmlspecialchars($sub)?>">
              <td colspan="4">
                <table class="record-table">
                  <thead>
                    <tr>
                      <th></th>
                      <th>業務代號</th>
                      <th>經辦代號</th>
                      <th>受款人代號</th>
                      <th>金額</th>
                      <th>填表日期</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($fullMap[$sub] as $r): ?>
                    <tr>
                      <td><input type="checkbox" class="row-select" checked></td>
                      <td><?=htmlspecialchars($r['業務代號'])?></td>
                      <td><?=htmlspecialchars($r['經辦代號'])?></td>
                      <td><?=htmlspecialchars($r['受款人代號'])?></td>
                      <td>NT$ <?=formatAmount($r['金額'])?></td>
                      <td><?=htmlspecialchars($r['填表日期'])?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>
      <div class="back-link" style="text-align:center;margin:20px;">
        <a href="javascript:history.back()" style="color:#2a7f7d;text-decoration:none;font-weight:600;">
          ← 回上一頁
        </a>
      </div>
    </div>
    <script>
      // (A) 展開／收合
      document.querySelectorAll('.count-cell').forEach(cell=>{
        cell.addEventListener('click',()=>{
          const sub=cell.closest('tr').dataset.sub;
          const sec=document.querySelector(`.full-section[data-parent="${sub}"]`);
          sec.style.display=sec.style.display==='table-row'?'none':'table-row';
        });
      });
      // (B) 關鍵字篩選
      document.getElementById('searchInput').addEventListener('input',function(){
        const raw=this.value.trim().toLowerCase();
        document.querySelectorAll('.summary-row, .full-section').forEach(el=>el.style.display='');
        document.querySelectorAll('.record-table tbody tr').forEach(el=>el.style.display='');
        if(!raw) return;
        const terms=raw.split(/[\s,]+/).filter(t=>t);
        document.querySelectorAll('.record-table tbody tr').forEach(tr=>{
          tr.style.display=terms.some(t=>tr.textContent.toLowerCase().includes(t))?'':'none';
        });
        document.querySelectorAll('.summary-row').forEach(s=>{
          const sub=s.dataset.sub;
          const any=Array.from(document.querySelectorAll(`.full-section[data-parent="${sub}"] tbody tr`))
                        .some(r=>r.style.display==='');
          s.style.display=any?'':'none';
          const fs=document.querySelector(`.full-section[data-parent="${sub}"]`);
          if(fs) fs.style.display=any?'table-row':'none';
        });
      });
      // (C) 全選打勾
      document.getElementById('selectAllSummary').addEventListener('change',function(){
        document.querySelectorAll('.summary-select').forEach(cb=>cb.checked=this.checked);
        document.querySelectorAll('.row-select').forEach(cb=>cb.checked=this.checked);
      });
    </script>
  </body>
  </html>
  <?php
  exit;
}

/* ====== (B) 月份→細項 ====== */
if ($detailMonth) {
  // 細項彙總
  $sql1 = "
    SELECT
      CASE
        WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
        WHEN e.支出項目='W活動費用' THEN e.專案活動
        ELSE e.支出項目
      END AS subCat,
      COUNT(*)   AS 件數,
      SUM(CAST(REPLACE(t.金額,',','') AS DECIMAL(10,2))) AS 總金額
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t USING(業務代號)
    WHERE DATE_FORMAT(e.填表日期,'%Y-%m') = :dm"
    .($expenseItem!==''?" AND e.支出項目 = :mc":'') ."
    GROUP BY subCat ORDER BY subCat
  ";
  $stmt1 = $pdo->prepare($sql1);
  $stmt1->bindValue(':dm',$detailMonth);
  if($expenseItem!=='') $stmt1->bindValue(':mc',$expenseItem);
  $stmt1->execute();
  $detailRows = $stmt1->fetchAll(PDO::FETCH_ASSOC);

  // 完整明細
  $sql2 = "
    SELECT
      CASE
        WHEN e.支出項目='Y經濟扶助' THEN e.經濟扶助
        WHEN e.支出項目='W活動費用' THEN e.專案活動
        ELSE e.支出項目
      END AS subCat,
      e.業務代號, e.經辦代號, t.受款人代號, t.金額, e.填表日期
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t USING(業務代號)
    WHERE DATE_FORMAT(e.填表日期,'%Y-%m') = :dm"
    .($expenseItem!==''?" AND e.支出項目 = :mc":'') ."
    ORDER BY subCat, e.填表日期
  ";
  $stmt2 = $pdo->prepare($sql2);
  $stmt2->bindValue(':dm',$detailMonth);
  if($expenseItem!=='') $stmt2->bindValue(':mc',$expenseItem);
  $stmt2->execute();
  $fullRows = $stmt2->fetchAll(PDO::FETCH_ASSOC);

  // 分組
  $fullMap = [];
  foreach($fullRows as $r){
    $fullMap[$r['subCat']][] = $r;
  }
  ?>
  <!DOCTYPE html>
  <html lang="zh-Hant">
  <head>
    <meta charset="UTF-8">
    <title><?=htmlspecialchars($expenseItem?:'全部')?> — <?=htmlspecialchars($detailMonth)?> 細項</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
    <style>
      *{margin:0;padding:0;box-sizing:border-box;}
      body{font-family:'Poppins',sans-serif;background:#eef6f4;padding:20px;}
      .container{max-width:760px;margin:0 auto;background:#fff;padding:30px;border-radius:12px;}
      h1{text-align:center;color:#3b6ea0;margin:20px 0;font-weight:600;}
      .summary-wrapper{text-align:center;margin-bottom:20px;}
      .filter-summary{display:inline-block;background:#e3f2fd;padding:8px 16px;border-radius:6px;color:#0277bd;font-weight:600;}
      .search-box{text-align:right;margin-bottom:10px;}
      .search-box input{padding:6px 8px;border:1px solid #ccc;border-radius:4px;width:80%;max-width:400px;}
      table{width:100%;border-collapse:collapse;margin-bottom:20px;}
      th,td{padding:10px;border:1px solid #ddd;text-align:center;}
      th{background:#3b6ea0;color:#fff;}
      tr:nth-child(even){background:#f7f9fc;}
      .count-cell{cursor:pointer;color:#1e88e5;}
      .full-section{display:none;}
      .record-table{width:100%;border-collapse:collapse;margin-top:10px;}
      .record-table th,.record-table td{padding:8px;border:1px solid #ccc;text-align:center;}
      .record-table th{background:#f0f0f0;color:#555;}
      @media print { .back-link{display:none!important;} }
    </style>
  </head>
  <body>
    <div class="container">
      <div class="search-box">
        <label>關鍵字篩選：
          <input type="text" id="searchInput" placeholder="輸入關鍵字，以空格或逗號分隔">
        </label>
      </div>
      <h1><?=htmlspecialchars($expenseItem?:'全部')?> — <?=htmlspecialchars($detailMonth)?> 細項</h1>
      <div class="summary-wrapper">
        <div class="filter-summary">
          支出項目：<?=htmlspecialchars($expenseItem?:'全部')?> — 月份：<?=htmlspecialchars($detailMonth)?>
        </div>
      </div>
      <?php if(empty($detailRows)): ?>
        <p style="text-align:center;color:#666;">此月份底下無資料。</p>
      <?php else: ?>
        <table id="summaryTable">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAllSummary"></th>
              <th>細項</th>
              <th>件數</th>
              <th>總金額</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach($detailRows as $d): $sub=$d['subCat']; ?>
            <tr class="summary-row" data-sub="<?=htmlspecialchars($sub)?>">
              <td><input type="checkbox" class="summary-select" data-sub="<?=htmlspecialchars($sub)?>" checked></td>
              <td><?=htmlspecialchars($sub)?></td>
              <td class="count-cell"><?=intval($d['件數'])?></td>
              <td>NT$ <?=formatAmount($d['總金額'])?></td>
            </tr>
            <tr class="full-section" data-parent="<?=htmlspecialchars($sub)?>">
              <td colspan="4">
                <table class="record-table">
                  <thead>
                    <tr>
                      <th></th>
                      <th>業務代號</th>
                      <th>經辦代號</th>
                      <th>受款人代號</th>
                      <th>金額</th>
                      <th>填表日期</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach($fullMap[$sub] as $r): ?>
                    <tr>
                      <td><input type="checkbox" class="row-select" checked></td>
                      <td><?=htmlspecialchars($r['業務代號'])?></td>
                      <td><?=htmlspecialchars($r['經辦代號'])?></td>
                      <td><?=htmlspecialchars($r['受款人代號'])?></td>
                      <td>NT$ <?=formatAmount($r['金額'])?></td>
                      <td><?=htmlspecialchars($r['填表日期'])?></td>
                    </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif;?>
      <div class="back-link" style="text-align:center;margin:20px;">
        <a href="javascript:history.back()" style="color:#2a7f7d;text-decoration:none;font-weight:600;">
          ← 回上一頁
        </a>
      </div>
    </div>
    <script>
      // 同上 detailItem 中相同的 JS
      document.querySelectorAll('.count-cell').forEach(cell=>{cell.addEventListener('click',()=>{const sub=cell.closest('tr').dataset.sub;const sec=document.querySelector(`.full-section[data-parent="${sub}"]`);sec.style.display=sec.style.display==='table-row'?'none':'table-row';});});
      document.getElementById('searchInput').addEventListener('input',function(){const raw=this.value.trim().toLowerCase();document.querySelectorAll('.summary-row, .full-section').forEach(el=>el.style.display='');document.querySelectorAll('.record-table tbody tr').forEach(el=>el.style.display='');if(!raw)return;const terms=raw.split(/[\s,]+/).filter(t=>t);document.querySelectorAll('.record-table tbody tr').forEach(tr=>{tr.style.display=terms.some(t=>tr.textContent.toLowerCase().includes(t))?'':'none';});document.querySelectorAll('.summary-row').forEach(s=>{const sub=s.dataset.sub;const any=Array.from(document.querySelectorAll(`.full-section[data-parent="${sub}"] tbody tr`)).some(r=>r.style.display==='');s.style.display=any?'':'none';const fs=document.querySelector(`.full-section[data-parent="${sub}"]`);if(fs)fs.style.display=any?'table-row':'none';});});
      document.getElementById('selectAllSummary').addEventListener('change',function(){document.querySelectorAll('.summary-select').forEach(cb=>cb.checked=this.checked);document.querySelectorAll('.row-select').forEach(cb=>cb.checked=this.checked);});
    </script>
  </body>
  </html>
  <?php
  exit;
}

/* ====== (C) 摘要＋圖表 ====== */
if ($useDoughnut) {
  // 支出項目圓餅（未改）
  $sql = "
    SELECT
      e.支出項目 AS keyName,
      COUNT(*)   AS 件數,
      SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t USING (業務代號)
    GROUP BY e.支出項目
    ORDER BY e.支出項目
  ";
  $results = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
} else {
  // 月份摘要，件數加 detailMonth 連結
  $sql = "
    SELECT
      DATE_FORMAT(e.填表日期,'%Y-%m') AS keyName,
      COUNT(*)   AS 件數,
      SUM(CAST(REPLACE(t.金額, ',', '') AS DECIMAL(10,2))) AS 總金額
    FROM 經辦業務檔 e
    JOIN 經辦人交易檔 t USING (業務代號)
    WHERE 1=1
  ";
  $params = [];
  if ($expenseItem!=='') {
    if (strpos($expenseItem,'-')!==false) {
      list($mc,$sc)=explode('-',$expenseItem,2);
      $sql .= " AND e.支出項目 = :mc AND (e.經濟扶助 LIKE :sc OR e.專案活動 LIKE :sc)";
      $params[':mc']=$mc;
      $params[':sc']="%{$sc}%";
    } else {
      $sql .= " AND e.支出項目 = :mc";
      $params[':mc']=$expenseItem;
    }
  }
  if ($monthFilter!=='') {
    $sql .= " AND DATE_FORMAT(e.填表日期,'%Y-%m') = :mf";
    $params[':mf']=$monthFilter;
  }
  $sql .= " GROUP BY keyName ORDER BY keyName";
  $stmt = $pdo->prepare($sql);
  foreach($params as $k=>$v) $stmt->bindValue($k,$v);
  $stmt->execute();
  $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Chart 資料
$labels=$counts=$amounts=[];
foreach($results as $r){
  $labels[]  =$r['keyName'];
  $counts[]  =intval($r['件數']);
  $amounts[]=floatval($r['總金額']);
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
    .summary-wrapper{text-align:center;margin-bottom:20px;}
    .filter-summary{display:inline-block;background:#e3f2fd;padding:8px 16px;border-radius:6px;color:#0277bd;font-weight:600;}
    table{width:100%;border-collapse:collapse;font-size:0.95rem;margin-bottom:20px;}
    th,td{padding:10px;border:1px solid #ddd;text-align:center;}
    th{background:#3b6ea0;color:#fff;}
    tr:nth-child(even){background:#f7f9fc;}
    table a,table a:visited{color:inherit;text-decoration:none;}
    table a:hover{text-decoration:underline;}
    .no-data{text-align:center;color:#666;padding:40px 0;}
    .chart-container{margin-top:30px;display:flex;justify-content:center;padding:16px;border:1px solid #ddd;border-radius:8px;background:#fff;}
  </style>
</head>
<body>
  <div class="container">
    <h1>統計結果</h1>
    <div class="summary-wrapper">
      <div class="filter-summary">
        支出項目：<?=htmlspecialchars($expenseItem?:'全部')?> — 月份：<?=htmlspecialchars($monthFilter?:'全部')?>
      </div>
    </div>

    <?php if(empty($results)): ?>
      <p class="no-data">查無資料，請調整篩選條件後重試。</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th><?= $useDoughnut?'支出項目':'月份'?></th>
            <th>件數</th>
            <th>總金額</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($results as $r): ?>
          <tr>
            <td>
              <?php if($useDoughnut):?>
                <a href="?expenseItem=<?=urlencode($rawItem)?>&monthFilter=<?=urlencode($monthFilter)?>&detailItem=<?=urlencode($r['keyName'])?>">
                  <?=htmlspecialchars($r['keyName'])?>
                </a>
              <?php else:?>
                <?=htmlspecialchars($r['keyName'])?>
              <?php endif;?>
            </td>
            <td>
              <?php if(!$useDoughnut):?>
                <a href="?expenseItem=<?=urlencode($rawItem)?>&monthFilter=<?=urlencode($monthFilter)?>&detailMonth=<?=urlencode($r['keyName'])?>">
                  <?=intval($r['件數'])?>
                </a>
              <?php else:?>
                <?=intval($r['件數'])?>
              <?php endif;?>
            </td>
            <td>NT$ <?=formatAmount($r['總金額'])?></td>
          </tr>
          <?php endforeach;?>
        </tbody>
      </table>
      <div class="chart-container">
        <canvas id="chartResult"></canvas>
      </div>
    <?php endif;?>
  </div>

  <script>
    Chart.register(ChartDataLabels);
    const useDoughnut=<?=json_encode($useDoughnut)?>,
          labels     =<?=json_encode($labels)?>,
          counts     =<?=json_encode($counts)?>,
          amounts    =<?=json_encode($amounts)?>,
          palette    =['#4e79a7','#f28e2c','#e15759','#76b7b2','#59a14f'];

    const datasets = useDoughnut
      ? [{
          label:'總金額',data:amounts,backgroundColor:palette,hoverOffset:8,
          datalabels:{
            color:'#fff',textStrokeColor:'#000',textStrokeWidth:2,
            anchor:'end',align:'outside',
            formatter:(v,ctx)=>`件數 ${counts[ctx.dataIndex]}\nNT$ ${v.toLocaleString()}`
          }
        }]
      : [
          {
            label:'件數',data:counts,
            backgroundColor:'rgba(78,121,167,0.6)',borderColor:'rgba(78,121,167,1)',yAxisID:'y',
            datalabels:{backgroundColor:'#fff',borderColor:'#ccc',borderWidth:1,borderRadius:4,padding:4,color:'#000',formatter:v=>v.toLocaleString(),anchor:'end',align:'start'}
          },
          {
            label:'總金額',data:amounts,
            backgroundColor:'rgba(242,142,44,0.6)',borderColor:'rgba(242,142,44,1)',yAxisID:'y1',
            datalabels:{backgroundColor:'#fff',borderColor:'#ccc',borderWidth:1,borderRadius:4,padding:4,color:'#000',formatter:v=>'NT$ '+v.toLocaleString(),anchor:'end',align:'start'}
          }
        ];

    const options={
      responsive:true,
      plugins:{title:{display:true,text:useDoughnut?'支出項目佔比':'每月 件數／總金額',font:{size:16,weight:'600'}}},
      scales:useDoughnut?{}:{
        y:{type:'linear',position:'left',beginAtZero:true,title:{display:true,text:'件數'},ticks:{stepSize:1,precision:0}},
        y1:{type:'linear',position:'right',beginAtZero:true,grid:{drawOnChartArea:false},title:{display:true,text:'總金額 (NT$)'},ticks:{callback:v=>'NT$ '+v.toLocaleString()}}
      }
    };

    if(!<?=json_encode(empty($results))?>){
      new Chart(document.getElementById('chartResult').getContext('2d'),{type:useDoughnut?'doughnut':'bar',data:{labels,datasets},options});
    }
  </script>
</body>
</html>

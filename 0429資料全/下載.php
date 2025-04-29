<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

// 資料庫連線設定
$host     = 'localhost:3307';
$dbname   = '基金會';
$username = 'root';
$password = ' ';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 把交易檔和 uploads 表做 JOIN
    $sql = "
        SELECT 
            t.交易單號,
            t.受款人代號,
            t.業務代號,
            u.image_path,
            u.csv_path,
            u.upload_timestamp,
            u.單據張數
        FROM 經辦人交易檔 AS t
        JOIN uploads AS u ON t.交易單號 = u.交易單號
        ORDER BY u.upload_timestamp DESC
    ";
    $stmt = $pdo->query($sql);
    $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("資料庫連線失敗: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <title>已提交表單與附件下載</title>
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: #F0F4F8;
      padding: 20px;
    }
    .container {
      max-width: 900px;
      margin: auto;
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
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    th, td {
      border: 1px solid #DDD;
      padding: 10px;
      text-align: center;
    }
    th {
      background: #50E3C2;
      color: #fff;
    }
    a.download {
      color: #4A90E2;
      text-decoration: none;
      font-weight: 600;
    }
    a.download:hover {
      text-decoration: underline;
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>已提交表單與附件下載</h1>
    <?php if (empty($records)): ?>
      <p>目前尚無任何提交紀錄。</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>交易單號</th>
            <th>受款人代號</th>
            <th>業務代號</th>
            <th>上傳時間</th>
            <th>圖片檔案</th>
            <th>CSV 檔案</th>
            <th>單據張數</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($records as $row): ?>
            <tr>
              <td><?= htmlspecialchars($row['交易單號']) ?></td>
              <td><?= htmlspecialchars($row['受款人代號']) ?></td>
              <td><?= htmlspecialchars($row['業務代號']) ?></td>
              <td><?= htmlspecialchars($row['upload_timestamp']) ?></td>
              <td>
                <?php
                  // 可能有多個路徑，用逗號分隔
                  $images = explode(',', $row['image_path']);
                  foreach ($images as $img):
                    if (trim($img) !== '--' && is_file($img)):
                ?>
                  <a class="download" href="<?= htmlspecialchars($img) ?>" download>
                    <?= basename($img) ?>
                  </a><br/>
                <?php
                    endif;
                  endforeach;
                ?>
              </td>
              <td>
                <?php
                  $csvs = explode(',', $row['csv_path']);
                  foreach ($csvs as $csv):
                    if (trim($csv) !== '--' && is_file($csv)):
                ?>
                  <a class="download" href="<?= htmlspecialchars($csv) ?>" download>
                    <?= basename($csv) ?>
                  </a><br/>
                <?php
                    endif;
                  endforeach;
                ?>
              </td>
              <td><?= intval($row['單據張數']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</body>
</html>

<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

// 限制設定
ini_set('upload_max_filesize', '1024M');
ini_set('post_max_size', '1024M');
ini_set('max_execution_time', '600');
ini_set('memory_limit', '1024M');

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trxID       = $_POST['單號查詢'] ?? '';
    $actualPaid  = $_POST['實支金額'] ?? '';
    $balance     = $_POST['結餘'] ?? '';
    $date        = $_POST['簽收日'] ?? '';
    $invoiceCnt  = $_POST['單據張數'] ?? 0;

    if (!$trxID) {
        die("錯誤：缺少交易單號");
    }

    // 改寫後的新交易單號：原交易單號 + C
    $newTrxID = $trxID . 'C';

    // 建立上傳資料夾（若不存在）
    $baseFolder   = 'uploads/uploads/';
    $imageFolder  = $baseFolder . 'images/';
    $csvFolder    = $baseFolder . 'csv/';

    if (!is_dir($imageFolder)) mkdir($imageFolder, 0777, true);
    if (!is_dir($csvFolder)) mkdir($csvFolder, 0777, true);

    // (1) 處理圖片上傳
    $imagePaths = [];
    if (!empty($_FILES['image_files']['name'][0])) {
        foreach ($_FILES['image_files']['error'] as $i => $err) {
            if ($err === UPLOAD_ERR_OK) {
                $tmp  = $_FILES['image_files']['tmp_name'][$i];
                $name = uniqid() . '_' . basename($_FILES['image_files']['name'][$i]);
                $target = $imageFolder . $name;
                if (move_uploaded_file($tmp, $target)) {
                    $imagePaths[] = $target;
                }
            }
        }
    }
    $imagePath = count($imagePaths) ? implode(',', $imagePaths) : '--';

    // (2) 處理 CSV 檔上傳
    $csvPaths = [];
    if (!empty($_FILES['csv_files']['name'][0])) {
        foreach ($_FILES['csv_files']['error'] as $i => $err) {
            if ($err === UPLOAD_ERR_OK) {
                $tmp  = $_FILES['csv_files']['tmp_name'][$i];
                $name = uniqid() . '_' . basename($_FILES['csv_files']['name'][$i]);
                $target = $csvFolder . $name;
                if (move_uploaded_file($tmp, $target)) {
                    $csvPaths[] = $target;
                }
            }
        }
    }
    $csvPath = count($csvPaths) ? implode(',', $csvPaths) : '--';

    try {
        $pdo->beginTransaction();

        // 檢查新單號是否已存在
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM 經辦人交易檔 WHERE 交易單號 = ?");
        $stmtCheck->execute([$newTrxID]);
        if ($stmtCheck->fetchColumn() > 0) {
            throw new Exception("交易單號 {$newTrxID} 已存在，請勿重複送出。");
        }

        // 更新 經辦人交易檔 單號與欄位內容
        $stmt1 = $pdo->prepare("
            UPDATE 經辦人交易檔
            SET 
                交易單號 = ?, 
                實支金額 = ?, 
                結餘 = ?, 
                簽收日 = ?,
				審核狀態 = '核銷審核中'
            WHERE 交易單號 = ?
        ");
        $stmt1->execute([
            $newTrxID,
            $actualPaid ?: null,
            $balance ?: null,
            $date ?: null,
            $trxID
        ]);

        // 刪除 uploads 表中舊紀錄（預防重複）
        $pdo->prepare("DELETE FROM uploads WHERE 交易單號 = ?")->execute([$newTrxID]);

        // 新增 uploads 紀錄
        $stmt4 = $pdo->prepare("
            INSERT INTO uploads
            (交易單號, image_path, csv_path, 單據張數)
            VALUES (?, ?, ?, ?)
        ");
        $stmt4->execute([
            $newTrxID,
            $imagePath,
            $csvPath,
            intval($invoiceCnt)
        ]);

        $pdo->commit();

        echo "✅ 更新成功！<br>
            原始單號：{$trxID}<br>
            ➤ 新交易單號：<strong>{$newTrxID}</strong><br>
            ➤ 實支金額：{$actualPaid}<br>
            ➤ 結餘：{$balance}<br>
            ➤ 簽收日：{$date}<br>
            ➤ 圖片上傳：" . ($imagePath !== '--' ? '✔' : '✖') . "<br>
            ➤ 附件上傳：" . ($csvPath !== '--' ? '✔' : '✖');

    } catch (Exception $e) {
        $pdo->rollBack();
        die("❌ 更新失敗：" . $e->getMessage());
    }
} else {
    die("請使用 POST 提交表單。");
}
?>

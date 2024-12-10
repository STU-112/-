<?php
// agg.php

// 開啟錯誤報告（僅在開發階段使用，生產環境應關閉）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 資料庫連接資訊
$server = 'localhost:3307'; // 確認您的 MySQL 伺服器埠號
$username = 'root';
$password = ' '; // 如果無密碼，請使用空字串
$database = '預支';

// 連接到 MySQL
$connection = mysqli_connect($server, $username, $password, $database);

// 檢查連接
if (!$connection) {
    die("連接失敗: " . mysqli_connect_error());
}

// 生成帶有 'A' + 民國年 + 月 + 5位數序號的流水號函數
function generateSerialNumber($connection) {
    // 取得當前時間
    $now = new DateTime();
    $year = $now->format('Y') - 1911; // 民國年
    $month = str_pad($now->format('m'), 2, '0', STR_PAD_LEFT); // 月份，兩位數
    $prefix = "A{$year}{$month}"; // 前綴，如 A11311

    // 查詢當前月份的最大流水號
    $stmt = $connection->prepare("SELECT MAX(`申請單號`) AS max_count FROM `申請單號` WHERE `申請單號` LIKE CONCAT(?, '%')");
    $stmt->bind_param("s", $prefix);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['max_count']) {
        // 取得當前最大的序號並加一
        $last_serial = intval(substr($row['max_count'], strlen($prefix)));
        $new_serial = $last_serial + 1;
    } else {
        // 如果沒有紀錄，從 1 開始
        $new_serial = 1;
    }

    // 生成新的流水號，填充到 5 位數
    $serialNumber = $prefix . str_pad($new_serial, 5, '0', STR_PAD_LEFT);
    return $serialNumber;
}

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // 驗證必填字段
    $required_fields = ['填表日期', '受款人', '支出項目', '說明', '支付方式', '國字金額'];
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            die("請填寫所有必填字段。");
        }
    }

    // 取得表單數據並進行驗證
    $受款人 = $_POST['受款人'];
    $填表日期 = $_POST['填表日期'];
    $付款日期 = !empty($_POST['付款日期']) ? $_POST['付款日期'] : NULL;
    $支出項目 = $_POST['支出項目'];
    $活動名稱 = !empty($_POST['活動名稱']) ? $_POST['活動名稱'] : NULL;
    $專案日期 = !empty($_POST['專案日期']) ? $_POST['專案日期'] : NULL;
    $獎學金人數 = !empty($_POST['獎學金人數']) ? intval($_POST['獎學金人數']) : NULL;
    $專案名稱 = !empty($_POST['專案名稱']) ? $_POST['專案名稱'] : NULL;
    $主題 = !empty($_POST['主題']) ? $_POST['主題'] : NULL;
    $獎學金日期 = !empty($_POST['獎學金日期']) ? $_POST['獎學金日期'] : NULL;
    $經濟扶助 = !empty($_POST['經濟扶助']) ? $_POST['經濟扶助'] : NULL;
    $其他項目 = isset($_POST['其他項目']) ? implode(", ", $_POST['其他項目']) : NULL;
    $說明 = $_POST['說明'];
    $支付方式 = $_POST['支付方式'];
    $國字金額 = isset($_POST['國字金額']) ? $_POST['國字金額'] : '';
    $國字金額_hidden = isset($_POST['國字金額_hidden']) ? $_POST['國字金額_hidden'] : '';

    $簽收金額 = !empty($_POST['簽收金額']) ? $_POST['簽收金額'] : NULL;
    $簽收人 = !empty($_POST['簽收人']) ? $_POST['簽收人'] : NULL;
    $簽收日 = !empty($_POST['簽收日']) ? $_POST['簽收日'] : NULL;
    $銀行郵局 = !empty($_POST['銀行郵局']) ? $_POST['銀行郵局'] : NULL;
    $分行 = !empty($_POST['分行']) ? $_POST['分行'] : NULL;
    $戶名 = !empty($_POST['戶名']) ? $_POST['戶名'] : NULL;
    $帳號 = !empty($_POST['帳號']) ? $_POST['帳號'] : NULL;
    $票號 = !empty($_POST['票號']) ? $_POST['票號'] : NULL;
    $到期日 = !empty($_POST['到期日']) ? $_POST['到期日'] : NULL;
    $預支金額 = !empty($_POST['預支金額']) ? $_POST['預支金額'] : NULL;

    // 開始事務處理
    mysqli_begin_transaction($connection);

    try {
        // 插入 `申請單號`
        $申請單號 = generateSerialNumber($connection);
        $stmt = $connection->prepare("INSERT INTO `申請單號` (`申請單號`) VALUES (?)");
        $stmt->bind_param("s", $申請單號);
        if (!$stmt->execute()) {
            throw new Exception("插入 `申請單號` 失敗: " . $stmt->error);
        }
        $stmt->close();

        // 插入 `受款人`
        $stmt = $connection->prepare("INSERT INTO `受款人` (`名稱`, `填表日期`, `付款日期`, `國字金額`, `申請單號`)
                                      VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $受款人, $填表日期, $付款日期, $國字金額, $申請單號);
        if (!$stmt->execute()) {
            throw new Exception("插入 `受款人` 失敗: " . $stmt->error);
        }
        $受款人_id = $stmt->insert_id;
        $stmt->close();

        // 插入 `支出項目`
        $stmt = $connection->prepare("INSERT INTO `支出項目` (`名稱`, `說明`, `申請單號`)
                                      VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $支出項目, $說明, $申請單號);
        if (!$stmt->execute()) {
            throw new Exception("插入 `支出項目` 失敗: " . $stmt->error);
        }
        $支出項目_id = $stmt->insert_id;
        $stmt->close();

        // 插入 `支付方式`
        $stmt = $connection->prepare("INSERT INTO `支付方式` 
            (`方式名稱`, `簽收金額`, `簽收人`, `簽收日`, `國字金額_hidden`, `銀行郵局`, `分行`, `戶名`, `帳號`, `票號`, `到期日`, `預支金額`, `申請單號`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissssssssssd", $支付方式, $簽收金額, $簽收人, $簽收日, $國字金額_hidden, 
            $銀行郵局, $分行, $戶名, $帳號, $票號, $到期日, $預支金額, $申請單號);
        if (!$stmt->execute()) {
            throw new Exception("插入 `支付方式` 失敗: " . $stmt->error);
        }
        $支付方式_id = $stmt->insert_id;
        $stmt->close();

        // 插入 `付款紀錄`
        $stmt = $connection->prepare("INSERT INTO `付款紀錄` 
            (`申請單號`, `受款人_ID`, `填表日期`, `付款日期`, `支出項目_ID`, `支付方式_ID`, `國字金額`, `國字金額_hidden`, `預支金額`) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sissiisd", $申請單號, $受款人_id, $填表日期, $付款日期, 
            $支出項目_id, $支付方式_id, $國字金額, $國字金額_hidden, $預支金額);
        if (!$stmt->execute()) {
            throw new Exception("插入 `付款紀錄` 失敗: " . $stmt->error);
        }
        $stmt->close();

        // 插入 `檔案上傳`
        if (isset($_FILES['上傳檔案']) && $_FILES['上傳檔案']['error'] === UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['上傳檔案']['tmp_name'];
            $fileName = $_FILES['上傳檔案']['name'];
            $fileSize = $_FILES['上傳檔案']['size'];
            $fileType = $_FILES['上傳檔案']['type'];
            $fileNameCmps = pathinfo($fileName);
            $fileExtension = strtolower($fileNameCmps['extension']);

            // 設定允許的檔案類型
            $allowedfileExtensions = array('jpg', 'jpeg', 'png', 'pdf');

            if (in_array($fileExtension, $allowedfileExtensions)) {
                // 設定檔案的新名稱（例如使用流水號）
                $newFileName = $申請單號 . '_' . time() . '.' . $fileExtension;

                // 設定上傳目錄
                $uploadFileDir = './uploaded_files/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                $dest_path = $uploadFileDir . $newFileName;

                // 移動檔案到目標目錄
                if(move_uploaded_file($fileTmpPath, $dest_path)) {
                    // 插入檔案資訊到資料表
                    $stmt = $connection->prepare("INSERT INTO `檔案上傳` (`申請單號`, `檔案名稱`, `檔案路徑`)
                                                  VALUES (?, ?, ?)");
                    $stmt->bind_param("sss", $申請單號, $fileName, $dest_path);
                    if (!$stmt->execute()) {
                        throw new Exception("插入 `檔案上傳` 失敗: " . $stmt->error);
                    }
                    $stmt->close();
                } else {
                    throw new Exception("檔案移動失敗。");
                }
            } else {
                throw new Exception("不允許的檔案類型。僅允許上傳 JPG、JPEG、PNG 或 PDF 檔案。");
            }
        } else {
            throw new Exception("檔案上傳失敗或未上傳檔案。");
        }

        // 提交事務
        mysqli_commit($connection);

        // 返回成功響應
        echo "表單已成功提交!!";
    } catch (Exception $e) {
        // 發生錯誤時回滾事務
        mysqli_rollback($connection);
        // 返回錯誤響應
        die($e->getMessage());
    }
}

// 關閉資料庫連接
mysqli_close($connection);
?>

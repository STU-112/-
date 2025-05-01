<?php
include '啟動Session.php';

$servername = "localhost:3307";
$username = "root";
$password = " ";
$dbname = "基金會";

$db_link = new mysqli($servername, $username, $password, $dbname);
if ($db_link->connect_error) {
    die("連線失敗: " . $db_link->connect_error);
}

$帳號 = $_SESSION["帳號"];
$職位查詢 = "SELECT 權限管理 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$職位_result_使用者 = $db_link->query($職位查詢);
$職位名稱 = "未知職位";
if ($職位_result_使用者 && $職位_result_使用者->num_rows > 0) {
    $row = $職位_result_使用者->fetch_assoc();
    $職位名稱 = $row["權限管理"];
}

$職位編號查詢 = "SELECT 編號 FROM 職位 WHERE 職位名稱 = '$職位名稱' LIMIT 1";
$職位編號結果 = $db_link->query($職位編號查詢);
if ($職位編號結果 && $職位編號結果->num_rows > 0) {
    $row = $職位編號結果->fetch_assoc();
    $當前職位編號 = $row["編號"];
}

$下一個職位查詢 = "SELECT * FROM 職位 WHERE 編號 > $當前職位編號 ORDER BY 編號 ASC LIMIT 1";
$下一個職位結果 = $db_link->query($下一個職位查詢);
if ($下一個職位結果 && $下一個職位結果->num_rows > 0) {
    $row = $下一個職位結果->fetch_assoc();
    $審查者 = $row["職位名稱"];
} else {
    $審查者 = "無下一位審核者";
}

$職位名稱 = mysqli_real_escape_string($db_link, $職位名稱);
$table_name = "`" . $職位名稱 . "審核意見`";

$create_table_sql = "CREATE TABLE IF NOT EXISTS $table_name  (
    流水號 INT AUTO_INCREMENT PRIMARY KEY,
    單號 VARCHAR(20) UNIQUE,
    審核意見 TEXT NOT NULL,
    狀態 CHAR(10) NOT NULL,
    審核部門 VARCHAR(50) NOT NULL,
    職位名稱 VARCHAR(50) NOT NULL,
    審核時間 DATETIME NOT NULL
)";

if (!mysqli_query($db_link, $create_table_sql)) {
    die("創建資料表失敗: " . mysqli_error($db_link));
}

$帳號 = $_SESSION["帳號"];
$用戶查詢 = "SELECT 部門 FROM 註冊資料表 WHERE 帳號 = '$帳號' LIMIT 1";
$用戶結果 = $db_link->query($用戶查詢);
$審核部門 = ($用戶結果 && $用戶結果->num_rows > 0) ? $用戶結果->fetch_assoc()["部門"] : "未知";

$審核部門 = mysqli_real_escape_string($db_link, $審核部門);
$審核時間 = date("Y-m-d H:i:s");

function generateCodeWithSerial($prefix, $table, $column, $db_link) {
    $sql = "SELECT MAX($column) AS max_code FROM $table WHERE $column LIKE '{$prefix}%'";
    $result = $db_link->query($sql);
    if ($result && $row = $result->fetch_assoc()) {
        $max_code = $row['max_code'];
        if ($max_code) {
            $serial = (int)substr($max_code, strlen($prefix)) + 1;
        } else {
            $serial = 1;
        }
    } else {
        $serial = 1;
    }
    return $prefix . str_pad($serial, 3, '0', STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $number = mysqli_real_escape_string($db_link, $_POST['serial_count']);
    $opinion = mysqli_real_escape_string($db_link, $_POST['opinion']);
    $status = mysqli_real_escape_string($db_link, $_POST['status']);
    $交易單號 = mysqli_real_escape_string($db_link, $_POST['交易單號']);

    // 新增：產生帶流水號的受款人代號與業務代號
    $受款人代號 = generateCodeWithSerial($number . 'C', '受款人資料檔', '受款人代號', $db_link);
    $業務代號 = generateCodeWithSerial($number . 'C', '經辦業務檔', '業務代號', $db_link);

    $insert_record_sql = "INSERT INTO $table_name  (單號, 審核意見, 狀態, 審核部門, 職位名稱, 審核時間) 
                          VALUES ('$number', '$opinion', '$status', '$審核部門', '$審查者', '$審核時間')";

 if (mysqli_query($db_link, $insert_record_sql)) {
        echo "<p style='color: green;'>記錄已成功提交！3 秒後將返回頁面。</p>";
        echo "<script>setTimeout(function(){ window.location.href = '審核人.php'; }, 3000);</script>";

        // 讀取原始狀態
        $查詢原始狀態 = "SELECT 審核狀態 FROM 經辦人交易檔 WHERE 交易單號 = '$交易單號' LIMIT 1";
        $原始結果 = $db_link->query($查詢原始狀態);
        if ($原始結果 && $原始結果->num_rows > 0) {
            $row = $原始結果->fetch_assoc();
            $原始狀態 = $row["審核狀態"];
            $新狀態 = $原始狀態;

            // 根據審核結果與職位更新狀態
            if ($職位名稱 === '出納' && $status === '通過') {
                if ($原始狀態 === '預支審核中') {
                    $新狀態 = '預支完成審查';
                } elseif ($原始狀態 === '報帳審核中') {
                    $新狀態 = '報帳完成審查';
                } elseif ($原始狀態 === '核銷審核中') {
                    $新狀態 = '核銷完成審查';
                } else {
                    $新狀態 = '不通過';
                }
            } elseif ($職位名稱 !== '出納' && $status === '不通過') {
                if ($原始狀態 === '核銷審核中') {
                    $新狀態 = '核銷不通過';
                } else {
                    $新狀態 = '不通過';
                }
            }

            // 更新子單審核狀態
            $更新狀態_SQL = "UPDATE 經辦人交易檔 SET 審核狀態 = '$新狀態' WHERE 交易單號 = '$交易單號'";
            mysqli_query($db_link, $更新狀態_SQL);

            // 若為「核銷完成審查」，同步更新原單號為「核銷已完成」
            if ($新狀態 === '核銷完成審查') {
                if (preg_match("/^(A\d{12})C\d{3}$/", $交易單號, $matches)) {
                    $原單號 = $matches[1];
                    $更新原單_SQL = "UPDATE 經辦人交易檔
                                     SET 審核狀態 = '核銷已完成'
                                     WHERE 交易單號 = ? AND 審核狀態 = '核銷審核中'";
                    $stmt = $db_link->prepare($更新原單_SQL);
                    $stmt->bind_param("s", $原單號);
                    $stmt->execute();
                    $stmt->close();
                }
            }

            // 若不通過，更新原單號為「核銷未通過」
            if (preg_match("/^(A\d{12})C\d{3}$/", $交易單號, $matches) && $status === '不通過') {
                $原單號 = $matches[1];
                $更新原單_SQL = "UPDATE 經辦人交易檔
                                 SET 審核狀態 = '核銷未通過'
                                 WHERE 交易單號 = ? AND 審核狀態 = '核銷審核中'";
                $stmt = $db_link->prepare($更新原單_SQL);
                $stmt->bind_param("s", $原單號);
                $stmt->execute();
                $stmt->close();
            }
        }
    } else {
        if (mysqli_errno($db_link) == 1062) {
            echo "<p style='color: orange;'>插入失敗：該單號已存在。3 秒後將返回頁面。</p>";
            echo "<script>setTimeout(function(){ window.location.href = '審核人.php'; }, 3000);</script>";
        } else {
            echo "插入記錄失敗: " . mysqli_error($db_link);
        }
    }
}

mysqli_close($db_link);
?>

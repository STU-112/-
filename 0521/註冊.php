<?php 
session_start();

// 1. 資料庫連線
$db_host = "localhost:3307";
$db_user = "root";
$db_pass = " ";
$db_name = "基金會";

// 2. 連線並建立資料庫
$db = mysqli_connect($db_host, $db_user, $db_pass);
if (!$db) {
    die("連線失敗: " . mysqli_connect_error());
}
mysqli_set_charset($db, 'utf8mb4');
mysqli_query($db, "CREATE DATABASE IF NOT EXISTS {$db_name} DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
mysqli_select_db($db, $db_name);

// 3. 建表（結構不變，密碼欄位仍為 CHAR(255)）
$create_sql = "
CREATE TABLE IF NOT EXISTS 註冊資料表 (
  員工編號   CHAR(30)     NOT NULL,
  姓名       CHAR(30)     NOT NULL,
  電子郵件   VARCHAR(100) NOT NULL,
  電話       CHAR(20),
  地址       CHAR(30),
  部門       VARCHAR(100) NOT NULL,
  職位       VARCHAR(100) NOT NULL,
  帳號       CHAR(20)     NOT NULL,
  密碼       CHAR(255)    NOT NULL,
  權限管理   CHAR(20)     DEFAULT '經辦人',
  PRIMARY KEY (員工編號),
  UNIQUE KEY idx_email (電子郵件)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
mysqli_query($db, $create_sql);

// 4. 處理 POST
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $員工編號 = mysqli_real_escape_string($db, $_POST['員工編號']);
    $name     = mysqli_real_escape_string($db, $_POST['姓名']);
    $email    = mysqli_real_escape_string($db, $_POST['電子郵件']);
    $phone    = mysqli_real_escape_string($db, $_POST['電話']);
    $address  = mysqli_real_escape_string($db, $_POST['地址']);
    $dept     = mysqli_real_escape_string($db, $_POST['部門']);
    $pos      = mysqli_real_escape_string($db, $_POST['職位']);
    $user     = mysqli_real_escape_string($db, $_POST['帳號']);
    $raw_pw   = $_POST['密碼'];
    $confirm  = $_POST['密碼確認'];
    $perm     = '經辦人';

    // 密碼與確認密碼一致性檢查
    if ($raw_pw !== $confirm) {
        header("Location: 註冊html.php?error=" . urlencode("密碼與確認密碼不一致"));
        exit;
    }

    // 重複性檢查
    foreach (['員工編號'=>'員工編號', '電子郵件'=>'電子郵件', '帳號'=>'帳號'] as $field=>$col) {
        $value = ($field === '員工編號' ? $員工編號 : ($field === '電子郵件' ? $email : $user));
        $r = mysqli_query($db, "SELECT 1 FROM 註冊資料表 WHERE `{$col}`='$value' LIMIT 1");
        if (mysqli_num_rows($r) > 0) {
            header("Location: 註冊html.php?error=" . urlencode("重複欄位：{$field}"));
            exit;
        }
    }

    // **密碼以明碼形式儲存**
    $pw = mysqli_real_escape_string($db, $raw_pw);

    $ins = "
    INSERT INTO 註冊資料表
      (員工編號, 姓名, 電子郵件, 電話, 地址, 部門, 職位, 帳號, 密碼, 權限管理)
    VALUES
      ('$員工編號','$name','$email','$phone','$address','$dept','$pos','$user','$pw','$perm')
    ";
    if (mysqli_query($db, $ins)) {
        echo "<script>alert('註冊成功！');location.href='系統管理員.php';</script>";
        exit;
    } else {
        die("插入失敗: " . mysqli_error($db));
    }
}

mysqli_close($db);
?>

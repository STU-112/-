<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
date_default_timezone_set('Asia/Taipei');

$successMessages = [];
$errorMessages   = [];
$email           = '';
$message         = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. 取得並驗證輸入
    $email   = filter_input(INPUT_POST, 'email', FILTER_VALIDATE_EMAIL);
    $message = trim($_POST['message'] ?? '');

    if (!$email || $message === '') {
        $errorMessages[] = '請填寫正確的 Email 與訊息內容。';
    } else {
        // 2. 資料庫連線設定（密碼為三個空格）
        $db_host = "localhost:3307";
        $db_user = "root";
        $db_pass = " ";
        $db_name = "基金會";

        $db = new mysqli($db_host, $db_user, $db_pass, $db_name);
        if ($db->connect_error) {
            $errorMessages[] = 'DB 連線失敗：' . $db->connect_error;
        } else {
            $db->set_charset('utf8mb4');

            // 3. 確保資料表存在
            $db->query("
              CREATE TABLE IF NOT EXISTS `通知紀錄` (
                `id` INT AUTO_INCREMENT PRIMARY KEY,
                `email` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
              ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
            ");

            // 4. 寫入通知紀錄
            $stmt = $db->prepare("INSERT INTO `通知紀錄` (`email`,`message`) VALUES (?,?)");
            if ($stmt) {
                $stmt->bind_param('ss', $email, $message);
                if ($stmt->execute()) {
                    $successMessages[] = '通知紀錄已成功儲存！';
                } else {
                    $errorMessages[] = '系統錯誤：無法寫入紀錄。';
                }
                $stmt->close();
            } else {
                $errorMessages[] = '系統錯誤：準備語句失敗。';
            }

            // 5. 無錯誤才發送 Email
            if (empty($errorMessages)) {
                require __DIR__ . '/PHPMailer/src/Exception.php';
                require __DIR__ . '/PHPMailer/src/PHPMailer.php';
                require __DIR__ . '/PHPMailer/src/SMTP.php';

                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                try {
                    $mail->CharSet    = 'UTF-8';
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.gmail.com';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'wyz2005wyz@gmail.com';
                    $mail->Password   = 'vxkwavzrfdxyqbrn';
                    $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port       = 587;
                    $mail->AuthType   = 'LOGIN';
                    $mail->SMTPOptions = [
                        'ssl'=> [
                            'verify_peer'=>false,
                            'verify_peer_name'=>false,
                            'allow_self_signed'=>true
                        ]
                    ];

                    $mail->setFrom($mail->Username, '系統通知');
                    $mail->addAddress($email);
                    $mail->isHTML(false);
                    $mail->Subject = '您有一則新通知';
                    $mail->Body    = "您好，\n\n您收到以下訊息：\n\n{$message}\n\n--\n本郵件由系統自動發送";
                    $mail->send();

                    $successMessages[] = '通知已發送至 ' . htmlspecialchars($email, ENT_QUOTES) . '，請檢查信箱。';
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    $errorMessages[] = 'Email 發送失敗：' . $e->getMessage();
                }
            }

            $db->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <title>發送通知 Email</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      max-width: 500px;
      margin: 40px auto;
      padding: 0 10px;
    }
    h1 {
      text-align: center;
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-top: 10px;
      font-weight: bold;
    }
    input, textarea {
      width: 100%;
      padding: 8px;
      margin-top: 5px;
      box-sizing: border-box;
      border: 1px solid #ccc;
      border-radius: 4px;
    }
    button {
      margin-top: 15px;
      padding: 10px 20px;
      background-color: #007bff;
      color: #fff;
      border: none;
      border-radius: 4px;
      cursor: pointer;
    }
    button:hover {
      background-color: #0056b3;
    }
    .notification {
      padding: 15px;
      border-radius: 4px;
      margin-bottom: 20px;
    }
    .success {
      background-color: #d4edda;
      border: 1px solid #c3e6cb;
      color: #155724;
    }
    .error {
      background-color: #f8d7da;
      border: 1px solid #f5c6cb;
      color: #721c24;
    }
  </style>
</head>
<body>
  <h1>發送通知 Email</h1>

  <!-- 顯示訊息 -->
  <?php if (!empty($successMessages)): ?>
    <div class="notification success">
      <?php foreach ($successMessages as $msg): ?>
        <p><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($errorMessages)): ?>
    <div class="notification error">
      <?php foreach ($errorMessages as $msg): ?>
        <p><?php echo htmlspecialchars($msg, ENT_QUOTES); ?></p>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

  <!-- 唯一表單 -->
  <form method="post" action="MS.php">
    <label for="email">收件人 Email：</label>
    <input
      type="email"
      id="email"
      name="email"
      required
      value="<?php echo htmlspecialchars($email, ENT_QUOTES); ?>">

    <label for="message">訊息內容：</label>
    <textarea
      id="message"
      name="message"
      rows="5"
      required><?php echo htmlspecialchars($message, ENT_QUOTES); ?></textarea>

    <button type="submit">送出</button>
  </form>
</body>
</html>

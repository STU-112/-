<?php
// (1) 啟用 Session（如有需要）
session_start();

// (2) 定義/取得目前使用者 (範例：從 Session 取得)
$current_user = isset($_SESSION['username']) ? $_SESSION['username'] : '訪客';

// ---------------------------------------------------------
// (3) 連線並建置資料庫「部門設定」
// ---------------------------------------------------------
$host      = 'localhost:3307';  // 如果 XAMPP MySQL 埠號是 3307，就改為 'localhost:3307'
$db_user   = 'root';            // 預設 root
$db_pass   = '3307';                // 若無密碼多為空字串 "" (請勿保留空白)
$target_db = '部門設定';         // 你想使用的資料庫名稱

// 連線「不指定資料庫」，以便 CREATE DATABASE
$temp_link = new mysqli($host, $db_user, $db_pass);
if ($temp_link->connect_error) {
    die("無法連線 MySQL：" . $temp_link->connect_error);
}
// 嘗試建置資料庫 (如果不存在就建立)
$create_db_sql = "CREATE DATABASE IF NOT EXISTS `$target_db`
                  CHARACTER SET utf8mb4
                  COLLATE utf8mb4_general_ci;";
$temp_link->query($create_db_sql);
$temp_link->close();

// ---------------------------------------------------------
// (4) 連線到「部門設定」資料庫
// ---------------------------------------------------------
$db_link_部門 = new mysqli($host, $db_user, $db_pass, $target_db);
if ($db_link_部門->connect_error) {
    die("資料庫連線失敗：" . $db_link_部門->connect_error);
}

// ---------------------------------------------------------
// (5) 若表「部門」不存在，則先行建立 (含「部門代號」「部門名稱」)
// ---------------------------------------------------------
$create_table_sql = "
    CREATE TABLE IF NOT EXISTS `部門` (
      `部門代號`  VARCHAR(50)  NOT NULL,
      `部門名稱`  VARCHAR(100) NOT NULL,
      PRIMARY KEY (`部門代號`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$db_link_部門->query($create_table_sql);

// 用來存放重複提示訊息（若有）
$error_message = "";

// ---------------------------------------------------------
// (6) 若有表單提交，示範「新增部門」邏輯 + 防呆機制
// ---------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $部門代號 = $_POST['部門代號'];
    $部門名稱 = $_POST['部門名稱'];

    // 1. 檢查是否重複
    //    ★ 條件：表中已存在「相同部門代號」或「相同部門名稱」都不允許
    $check_sql = "SELECT COUNT(*) AS cnt
                  FROM 部門
                  WHERE 部門代號 = ? 
                     OR 部門名稱 = ?;";
    $stmt_check = $db_link_部門->prepare($check_sql);
    $stmt_check->bind_param('ss', $部門代號, $部門名稱);
    $stmt_check->execute();
    $stmt_check->bind_result($count);
    $stmt_check->fetch();
    $stmt_check->close();

    if ($count > 0) {
        // 代表已存在同名「部門代號」或「部門名稱」
        $error_message = "此部門代號或部門名稱已存在，請重新輸入！";
    } else {
        // 2. 若無重複，則執行 INSERT
        $insert_sql = "INSERT INTO 部門 (部門代號, 部門名稱) VALUES (?, ?)";
        $stmt = $db_link_部門->prepare($insert_sql);
        $stmt->bind_param('ss', $部門代號, $部門名稱);
        $stmt->execute();
        $stmt->close();

        // 3. 新增完成後重新導向回本頁 (避免重新整理時重複提交)
        header('Location: 新增部門html.php');
        exit;
    }
}

// ---------------------------------------------------------
// (7) 查詢部門資料
// ---------------------------------------------------------
$sql = "SELECT * FROM 部門";
$result = $db_link_部門->query($sql);
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <title>新增部門</title>
  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.gstatic.com" />
  <link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap"
    rel="stylesheet"
  />
  <style>
    /* 全局設定 */
    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }
    body {
      font-family: "Inter", "Noto Sans TC", sans-serif;
      background: linear-gradient(110deg, #e3f2fd 20%, #fafafa 80%);
      color: #333;
    }

    /* 版心容器 */
    .container {
      max-width: 900px;
      margin: 40px auto;
      padding: 40px;
      background-color: #fff;
      border-radius: 12px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
      overflow: hidden;
    }

    /* 頁面頂部橫幅 */
    .banner {
      background: linear-gradient(90deg, #42a5f5 0%, #1e88e5 100%);
      color: #fff;
      display: flex;
      align-items: center;
      padding: 12px 20px;
      box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.2);
      font-size: 1.1rem;
    }
    .banner a {
      color: #fff;
      text-decoration: none;
      font-weight: bold;
      font-size: 1.2rem;
      margin-right: 24px;
      cursor: pointer;
      transition: color 0.3s;
    }
    .banner a:hover {
      color: #fcecd6;
    }

    /* 標題區 */
    .form-header {
      text-align: center;
      margin-bottom: 30px;
    }
    .form-header h1 {
      font-size: 2.2rem;
      margin-bottom: 10px;
      font-weight: 600;
      color: #444;
    }
    .form-header p {
      color: #666;
      font-size: 1rem;
    }

    /* 錯誤訊息提示樣式 */
    .error-message {
      color: red;
      font-weight: 600;
      text-align: center;
      margin-bottom: 16px;
    }

    /* 表單設定 */
    form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      font-size: 1rem;
    }
    @media (max-width: 600px) {
      form {
        grid-template-columns: 1fr;
      }
    }
    .form-group {
      display: flex;
      flex-direction: column;
    }
    label {
      font-weight: 600;
      margin-bottom: 8px;
      color: #444;
    }
    input[type="text"] {
      padding: 14px;
      border: 1px solid #ddd;
      border-radius: 6px;
      font-size: 1rem;
      transition: border-color 0.3s, box-shadow 0.3s;
    }
    input[type="text"]:focus {
      outline: none;
      border-color: #90caf9;
      box-shadow: 0 0 3px rgba(66, 165, 245, 0.3);
    }

    /* 送出按鈕 */
    .submit-btn-container {
      grid-column: 1 / -1;
      text-align: center;
      margin-top: 20px;
    }
    input[type="submit"] {
      padding: 14px 36px;
      border: none;
      border-radius: 8px;
      background-color: #42a5f5;
      color: #fff;
      font-size: 1.1rem;
      font-weight: 600;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s;
    }
    input[type="submit"]:hover {
      background-color: #1e88e5;
      transform: translateY(-2px);
    }
    input[type="submit"]:active {
      transform: translateY(0);
    }

    /* 目前部門列表 */
    h2 {
      margin-top: 30px;
      font-size: 1.6rem;
      color: #444;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 10px;
      font-size: 1rem;
    }
    th, td {
      border: 1px solid #eee;
      padding: 14px 16px;
    }
    th {
      background-color: #eaf5ff;
      color: #444;
      text-align: left;
      font-size: 1rem;
    }
    td {
      background-color: #fff;
    }
    tbody tr:nth-child(even) {
      background-color: #f9f9f9;
    }
    tbody tr:hover {
      background-color: #f4f4f4;
    }
    .text-center {
      text-align: center;
    }

    /* 編輯按鈕樣式 */
    a.edit-btn, a.delete-btn {
      display: inline-block;
      border-radius: 5px;
      color: #fff;
      padding: 8px 16px;
      text-decoration: none;
      font-size: 1rem;
      transition: background-color 0.3s, transform 0.2s;
      margin-left: 8px;
    }
    a.edit-btn {
      background-color: #ffa726;
    }
    a.edit-btn:hover {
      background-color: #ff9100;
      transform: translateY(-2px);
    }
    a.delete-btn {
      background-color: #ff6b6b;
    }
    a.delete-btn:hover {
      background-color: #ff2e2e;
      transform: translateY(-2px);
    }
    a.edit-btn:active, a.delete-btn:active {
      transform: translateY(0);
    }

    .form-footer {
      margin-top: 20px;
      text-align: center;
      font-size: 0.9rem;
      color: #888;
    }
  </style>
</head>
<body>

<!-- 頁面頂部橫幅 -->
<div class="banner">
    <a onclick='history.back()'>◀</a>
    <span>歡迎，<?php echo htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8'); ?>！</span>
</div>

<div class="container">
    <div class="form-header">
      <h1>新增部門</h1>
      <p>請填寫以下資訊，並確認無誤後再送出</p>
    </div>

    <!-- 如果有錯誤訊息，顯示在表單上方 -->
    <?php if (!empty($error_message)) : ?>
      <div class="error-message"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- 表單：提交回本頁 -->
    <form action="新增部門html.php" method="POST">
      <!-- 部門代號 -->
      <div class="form-group">
        <label for="部門代號">部門代號<span style="color: red;">*</span></label>
        <input
          type="text"
          id="部門代號"
          name="部門代號"
          placeholder="請輸入部門代號"
          required
        />
      </div>
      <!-- 部門名稱 -->
      <div class="form-group">
        <label for="部門名稱">部門名稱<span style="color: red;">*</span></label>
        <input
          type="text"
          id="部門名稱"
          name="部門名稱"
          placeholder="請輸入部門名稱"
          required
        />
      </div>
      <!-- 送出按鈕 -->
      <div class="submit-btn-container">
        <input type="submit" value="送 出" />
      </div>
    </form>

    <!-- 目前部門列表 -->
    <h2>目前部門列表</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 70%;">部門代號 & 部門名稱</th>
                <th style="width: 30%;">編輯</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";

                    // 顯示「部門代號」 - 「部門名稱」
                    echo "<td>"
                         . htmlspecialchars($row["部門代號"], ENT_QUOTES, 'UTF-8')
                         . " - "
                         . htmlspecialchars($row["部門名稱"], ENT_QUOTES, 'UTF-8')
                         . "</td>";

                    // 修改 + 清除 按鈕
                    echo "<td class='text-center'>
                            <a class='edit-btn'
                               href='修改部門.php?部門代號=" . ($row["部門代號"]) . "'>
                                修改
                            </a>
                            <a class='delete-btn'
                               href='清除部門.php?部門代號=" . ($row["部門代號"]) . "'
                               onclick=\"return confirm('你確定要刪除此部門嗎？');\">
                                清除
                            </a>
                          </td>";

                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='2'>目前無部門資料</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <div class="form-footer">
      ※ 確認資料無誤後再點選送出按鈕
    </div>
</div>

</body>
</html>

<?php
// (8) 關閉資料庫連線（若有需要）
if (isset($db_link_部門)) {
    $db_link_部門->close();
}
?>

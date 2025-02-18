<?php
session_start();

// (A) 檢查使用者是否已登入（若需要）
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}
$current_user = $_SESSION['帳號'];

// (B) 資料庫連線參數
$host      = 'localhost:3307'; // 端口請依環境調整
$db_user   = 'root';
$db_pass   = ' ';               // 若沒密碼可為空字串
$target_db = '部門設定';

// (C) 先建立「部門設定」資料庫（若不存在）
$temp_link = new mysqli($host, $db_user, $db_pass);
if ($temp_link->connect_error) {
    die("無法連線 MySQL：" . $temp_link->connect_error);
}
$create_db_sql = "CREATE DATABASE IF NOT EXISTS `$target_db`
                  CHARACTER SET utf8mb4
                  COLLATE utf8mb4_general_ci;";
$temp_link->query($create_db_sql);
$temp_link->close();

// (D) 連線到「部門設定」資料庫
$db_link_部門 = new mysqli($host, $db_user, $db_pass, $target_db);
if ($db_link_部門->connect_error) {
    die("資料庫連線失敗：" . $db_link_部門->connect_error);
}

// (E) 建立資料表「部門」（若不存在）
$create_table_sql = "
    CREATE TABLE IF NOT EXISTS `部門` (
      `部門代號`  VARCHAR(50)  NOT NULL,
      `部門名稱`  VARCHAR(100) NOT NULL,
      `建立時間`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`部門代號`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";
$db_link_部門->query($create_table_sql);

// (F) 讀取目前全部門資料
$sql = "SELECT * FROM `部門` ORDER BY `建立時間` DESC";
$result = $db_link_部門->query($sql);

// 若有帶錯誤訊息，放到變數，便於表單顯示
$error_msg = isset($_GET['error']) ? $_GET['error'] : '';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <title>新增部門</title>
  <!-- 可自行替換成需要的字體 -->
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
    /* 表單設定（新增部門） */
    form.add-form {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
      font-size: 1rem;
    }
    @media (max-width: 600px) {
      form.add-form {
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
      vertical-align: middle;
    }
    th {
      background-color: #eaf5ff;
      color: #444;
      text-align: left;
      font-size: 1rem;
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
    /* 修改、刪除按鈕 */
    a.edit-btn, a.delete-btn {
      display: inline-block;
      border-radius: 5px;
      color: #fff;
      padding: 8px 16px;
      text-decoration: none;
      font-size: 1rem;
      transition: background-color 0.3s, transform 0.2s;
      margin-left: 4px;
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
    a.edit-btn:active,
    a.delete-btn:active {
      transform: translateY(0);
    }
    /* 表單頁尾 */
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
    <a onclick="history.back()">◀</a>
    <span>歡迎，<?php echo htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8'); ?>！</span>
</div>

<div class="container">
    <!-- 標題區 -->
    <div class="form-header">
      <h1>新增部門</h1>
      <p>請填寫以下資訊，並確認無誤後再送出</p>
    </div>

    <!-- 錯誤訊息（若有） -->
    <?php if ($error_msg !== ''): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <!-- 新增部門表單 -->
    <!-- 這裡的 action 指向「新增部門.php」，執行真正的新增動作 -->
    <form class="add-form" action="新增部門.php" method="POST">
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
      <div class="submit-btn-container">
        <input type="submit" value="新 增" />
      </div>
    </form>

    <!-- 部門列表 -->
    <h2>目前部門列表</h2>
    <table>
      <thead>
        <tr>
          <th style="width: 20%;">部門代號</th>
          <th style="width: 30%;">部門名稱</th>
          <th style="width: 20%;">建立時間</th>
          <th style="width: 30%;">編輯</th>
        </tr>
      </thead>
      <tbody>
      <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $deptId   = htmlspecialchars($row["部門代號"], ENT_QUOTES, 'UTF-8');
            $deptName = htmlspecialchars($row["部門名稱"], ENT_QUOTES, 'UTF-8');
            $deptTime = htmlspecialchars($row["建立時間"], ENT_QUOTES, 'UTF-8');
          ?>
          <tr>
            <td><?php echo $deptId; ?></td>
            <td><?php echo $deptName; ?></td>
            <td><?php echo $deptTime; ?></td>
            <td class="text-center">
              <!-- 這裡示範直接顯示「刪除」；若需要「修改」功能，可再自行加 Modal 或連結 -->
              <a class="delete-btn"
                 href="清除部門.php?部門代號=<?php echo urlencode($deptId); ?>"
                 onclick="return confirm('你確定要刪除此部門嗎？');">
                 刪除
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
        <tr><td colspan="4">目前無部門資料</td></tr>
      <?php endif; ?>
      </tbody>
    </table>

    <div class="form-footer">
      ※ 確認資料無誤後再點選送出按鈕
    </div>
</div>

</body>
</html>

<?php
// 關閉資料庫連線
if (isset($db_link_部門)) {
    $db_link_部門->close();
}
?>

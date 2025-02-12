<?php 
session_start();
// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}
$current_user = $_SESSION['帳號'];

// 資料庫連線參數
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = ' ';
$target_db = '部門設定';

    // 先連線至 MySQL（不指定資料庫），以便建立資料庫（若尚未存在）
    $temp_link = new mysqli($host, $db_user, $db_pass);
    if ($temp_link->connect_error) {
        die("無法連線 MySQL：" . $temp_link->connect_error);
    }
    $create_db_sql = "CREATE DATABASE IF NOT EXISTS `$target_db`
                      CHARACTER SET utf8mb4
                      COLLATE utf8mb4_general_ci;";
    $temp_link->query($create_db_sql);
    $temp_link->close();

    // 連線到「部門設定」資料庫
    $db_link_部門 = new mysqli($host, $db_user, $db_pass, $target_db);
    if ($db_link_部門->connect_error) {
        die("資料庫連線失敗：" . $db_link_部門->connect_error);
    }
    // 若資料表「部門」不存在，則建立它
    $create_table_sql = "
      CREATE TABLE IF NOT EXISTS `部門` (
        `部門代號`  VARCHAR(50)  NOT NULL,
        `部門名稱`  VARCHAR(100) NOT NULL,
        `建立時間`  TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`部門代號`)
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ";
    $db_link_部門->query($create_table_sql);

    // 查詢部門資料
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
    <a onclick="history.back()">◀</a>
    <span>歡迎，<?php echo htmlspecialchars($current_user, ENT_QUOTES, 'UTF-8'); ?>！</span>
</div>

<div class="container">
    <div class="form-header">
      <h1>新增部門</h1>
      <p>請填寫以下資訊，並確認無誤後再送出</p>
    </div>

    <!-- 如果有錯誤訊息，則從 URL 傳入（例如：?error=訊息） -->
    <?php if (isset($_GET['error']) && $_GET['error'] != ''): ?>
      <div class="error-message"><?php echo htmlspecialchars($_GET['error'], ENT_QUOTES, 'UTF-8'); ?></div>
    <?php endif; ?>

    <!-- 表單：送出到 新增部門.php 處理 -->
    <form action="新增部門.php" method="POST">
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
        <input type="submit" value="新 增" />
      </div>
    </form>


    <!-- 目前部門列表 -->
    <h2>目前部門列表</h2>
    <table>
        <thead>
            <tr>
                <th style="width: 50%;">部門代號 & 部門名稱</th>
                <th style="width: 20%;">建立時間</th>
                <th style="width: 30%;">編輯</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" 
                         . htmlspecialchars($row["部門代號"], ENT_QUOTES, 'UTF-8')
                         . " - "
                         . htmlspecialchars($row["部門名稱"], ENT_QUOTES, 'UTF-8')
                         . "</td>";
                    echo "<td>" . htmlspecialchars($row["建立時間"], ENT_QUOTES, 'UTF-8') . "</td>";
                    echo "<td class='text-center'>
                            <a class='edit-btn'
                               href='修改部門.php?部門代號=" . urlencode($row["部門代號"]) . "'>
                                修改
                            </a>
                            <a class='delete-btn'
                               href='清除部門.php?部門代號=" . urlencode($row["部門代號"]) . "'
                               onclick=\"return confirm('你確定要刪除此部門嗎？');\">
                                清除
                            </a>
                          </td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='3'>目前無部門資料</td></tr>";
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
if (isset($db_link_部門)) {
    $db_link_部門->close();
}
?>

<?php
session_start();

// (A) 檢查登入（若需要）
if (!isset($_SESSION['帳號'])) {
    header("Location: 新增職位設定.php");
    exit;
}
$current_user = $_SESSION['帳號'];

// (B) 資料庫連線參數
$host      = 'localhost:3307';
$db_user   = 'root';
$db_pass   = ' ';  // 若無密碼可為空字串
$db_name = '部門設定';

// (C) 建立「部門設定」資料庫（若不存在）
$temp_link = new mysqli($host, $db_user, $db_pass);
if ($temp_link->connect_error) {
    die("無法連線 MySQL：" . $temp_link->connect_error);
}
$create_db_sql = "
  CREATE DATABASE IF NOT EXISTS `$db_name`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;
";
$temp_link->query($create_db_sql);
$temp_link->close();

// (D) 連線到「部門設定」資料庫
$db_link_部門 = new mysqli($host, $db_user, $db_pass, $db_name);
if ($db_link_部門->connect_error) {
    die("資料庫連線失敗：" . $db_link_部門->connect_error);
}

// (E) 若資料表「部門」不存在，則建立它
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

// (G) 讀取從其他檔案可能傳來的錯誤訊息
$error_msg = isset($_GET['error']) ? $_GET['error'] : '';
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
    /* 彈窗(Modal) */
    .modal {
      display: none; /* 預設隱藏 */
      position: fixed;
      z-index: 999; 
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto; /* 如果內容太多可捲動 */
      background-color: rgba(0, 0, 0, 0.4); /* 半透明背景 */
    }
    .modal-content {
      background-color: #fff;
      margin: 10% auto;
      padding: 20px;
      border-radius: 8px;
      width: 80%;
      max-width: 400px;
      position: relative;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    /* 關閉按鈕（X） */
    .close-btn {
      position: absolute;
      top: 16px;
      right: 16px;
      background: none;
      border: none;
      font-size: 1.4rem;
      cursor: pointer;
      color: #999;
      transition: color 0.3s;
    }
    .close-btn:hover {
      color: #666;
    }
    .close-btn:focus {
      outline: none;
    }
    /* 彈窗內的表單 */
    .edit-form label {
      display: block;
      margin-top: 10px;
      font-weight: 600;
      color: #444;
    }
    .edit-form input[type="text"] {
      width: 100%;
      margin-top: 6px;
      padding: 8px;
      font-size: 1rem;
      border: 1px solid #ddd;
      border-radius: 6px;
    }
    .modal-submit-btn {
      margin-top: 20px;
      display: inline-block;
      padding: 10px 24px;
      background-color: #42a5f5;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: 1rem;
      cursor: pointer;
      transition: background-color 0.3s, transform 0.2s;
    }
    .modal-submit-btn:hover {
      background-color: #1e88e5;
      transform: translateY(-2px);
    }
    .modal-submit-btn:active {
      transform: translateY(0);
    }
    /* 表單頁尾 */
    .form-footer {
      margin-top: 20px;
      text-align: center;
      font-size: 0.9rem;
      color: #888;
    }

    /* =========================
       新增的置中＆美化樣式
       ========================= */
    /* 讓彈窗整體置中對齊 */
    .modal-content {
      text-align: center;               /* 文字置中 */
      display: flex;
      flex-direction: column;
      align-items: center;             /* 垂直置中 */
    }
    /* 讓內部表單也能顯示在中間 */
    .edit-form {
      width: 100%;
      max-width: 300px;                /* 控制表單寬度，避免過寬 */
      margin: 0 auto;                  /* 區塊置中 */
    }
    /* 讓 label 與其輸入框分行置中 */
    .edit-form label {
      display: block;
      margin: 12px 0 6px;
      font-weight: 600;
      text-align: center;              /* label 文字置中 */
    }
    /* 讓文字輸入欄位內文字也置中 */
    .edit-form input[type="text"] {
      text-align: center;              /* 輸入內容置中 */
    }
    /* 讓標題 “修改” 更顯眼 */
    .modal-content h2 {
      margin-top: 0;                   /* 去掉預設上邊距，與 close-btn 更接近 */
      font-size: 1.8rem;
      font-weight: 700;
      color: #333;
    }
    /* 微調「確定修改」按鈕的置中與間距 */
    .modal-submit-btn {
      margin: 20px auto 0;            /* 置中並與輸入框拉開距離 */
      display: block;
    }
  </style>
  <script>
  // 全域變數：目前正在編輯的「舊部門代號」
  let currentOldDeptId = "";

  // 顯示修改視窗（將資料帶入彈窗）
  function openEditModal(oldDeptId, deptName) {
    currentOldDeptId = oldDeptId;
    // 將彈窗內的輸入框設定為該筆資料
    document.getElementById("edit_oldDeptId").value = oldDeptId; // 隱藏欄位
    document.getElementById("edit_deptId").value   = oldDeptId;
    document.getElementById("edit_deptName").value = deptName;
    // 顯示 Modal
    document.getElementById("editModal").style.display = "block";
  }

  // 嘗試關閉彈窗時，詢問是否放棄修改
  function closeEditModal() {
    if (confirm("確定要放棄修改嗎？")) {
      document.getElementById("editModal").style.display = "none";
    }
  }

  // 點擊彈窗外側也可關閉
  window.onclick = function(event) {
    const modal = document.getElementById("editModal");
    if (event.target === modal) {
      if (confirm("確定要放棄修改嗎？")) {
        modal.style.display = "none";
      }
    }
  }
  </script>
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
      <p>※ 確認資料無誤後再點選送出按鈕</p>
    </div>

    <!-- 錯誤訊息（若有） -->
    <?php if ($error_msg !== ''): ?>
      <div class="error-message">
        <?php echo htmlspecialchars($error_msg, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <!-- 新增部門表單：送到「新增部門.php」處理新增動作 -->
    <form class="add-form" action="新增部門.php" method="POST">
      <div class="form-group">
        <label for="部門代號">部門代號<span style="color: red;">*</span></label>
        <input type="text" id="部門代號" name="部門代號" placeholder="請輸入部門代號" required />
      </div>
      <div class="form-group">
        <label for="部門名稱">部門名稱<span style="color: red;">*</span></label>
        <input type="text" id="部門名稱" name="部門名稱" placeholder="請輸入部門名稱" required/>
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
              <!-- 修改（開啟彈窗） -->
              <a class="edit-btn" href="javascript:void(0);"
                 onclick="openEditModal('<?php echo $deptId; ?>', '<?php echo $deptName; ?>')">
                 修改
              </a>
              <!-- 刪除（連到清除部門.php） -->
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

    
</div>

<!-- (彈窗) 修改部門：送到「修改部門.php」 -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <!-- 叉叉按鈕（右上角） -->
    <button type="button" class="close-btn" onclick="closeEditModal()">×</button>

    <h2>修改</h2>
    <form class="edit-form" action="修改部門.php" method="POST">
      <!-- 舊部門代號 (隱藏) -->
      <input type="hidden" id="edit_oldDeptId" name="oldDeptId" value="" />

      <label for="edit_deptId">部門代號</label>
      <input type="text" id="edit_deptId" name="部門代號" value="" />

      <label for="edit_deptName">部門名稱</label>
      <input type="text" id="edit_deptName" name="部門名稱" value="" />

      <button type="submit" class="modal-submit-btn">確定修改</button>
	  <div class="form-footer">
      ※ 確認資料無誤後再點選送出按鈕
    </div>
    </form>
  </div>
</div>

</body>
</html>
<?php
// 關閉連線
if (isset($db_link_部門)) {
    $db_link_部門->close();
}
?>

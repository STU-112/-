<?php  
session_start();

if (!isset($_SESSION['帳號'])) {
    header('Location: 註冊html.php');
    exit;
}

// 1. 連線
$db_host = "localhost:3307";
$db_user = "root";
$db_pass = " ";
$db_name = "基金會";

// 2. 建立連線
$db = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($db->connect_error) die("DB連線失敗: " . $db->connect_error);
$db->set_charset("utf8mb4");

// 3. 讀取部門、職位
$pos  = $db->query("SELECT `職位名稱` FROM `職位`")->fetch_all(MYSQLI_ASSOC);
$dept = $db->query("SELECT `部門名稱` FROM `部門`")->fetch_all(MYSQLI_ASSOC);
$db->close();

// 錯誤訊息
$error_msg = isset($_GET['error']) ? htmlspecialchars($_GET['error'], ENT_QUOTES) : '';
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>新增使用者帳密</title>
  <link href="https://fonts.googleapis.com/css2?family=Segoe+UI:wght@400;600&display=swap" rel="stylesheet">
  <style>
    * { box-sizing:border-box; margin:0; padding:0; }
    body {
      font-family:'Segoe UI',sans-serif;
      background: linear-gradient(135deg, #FFF4C1 0%, #FFF8D7 50%, #FFFCEC 100%);
      display:flex;
      justify-content:center;
      align-items:start;
      padding:40px;
    }
    .card {
      background:#fff;
      border-radius:12px;
      padding:40px;
      box-shadow:0 4px 16px rgba(0,0,0,0.1);
      width:100%;
      max-width:720px;
    }
    .card h1 {
      text-align:center;
      color:#333;
      margin-bottom:30px;
      font-size:24px;
      font-weight:600;
    }
    .form-grid {
      display:grid;
      grid-template-columns:repeat(auto-fit, minmax(280px, 1fr));
      gap:20px 30px;
      margin-bottom:20px;
    }
    .form-group {
      display:flex;
      flex-direction:column;
      margin-bottom:20px;
    }
    label {
      margin-bottom:8px;
      color:#555;
      font-size:16px;
      font-weight:500;
    }
    input, select {
      padding:16px 20px;
      font-size:16px;
      min-height:48px;
      border:1px solid #ddd;
      border-radius:6px;
      transition:border-color .3s, box-shadow .3s;
    }
    input:focus, select:focus {
      border-color:#4CAF50;
      box-shadow:0 0 0 3px rgba(76,175,80,0.2);
      outline:none;
    }
    .hint {
      font-size:14px;
      color:#888;
      margin-top:6px;
    }
    .strength.weak { color:#f44336; font-size:14px; }
    .button-group {
      display:flex;
      justify-content:space-between;
      margin-top:30px;
    }
    .button-group button {
      width:48%;
      padding:16px 0;
      font-size:16px;
      font-weight:600;
      border:none;
      border-radius:6px;
      cursor:pointer;
      color:#fff;
      transition:opacity .2s;
    }
    .submit-btn { background:#28a745; }
    .submit-btn:hover { opacity:.9; }
    .reset-btn  { background:#dc3545; }
    .reset-btn:hover  { opacity:.9; }

    /* modal */
    .modal-overlay {
      position:fixed; top:0; left:0;
      width:100%; height:100%;
      background:rgba(0,0,0,0.4);
      display:none; align-items:center; justify-content:center;
      z-index:1000;
    }
    .modal-overlay.show { display:flex; }
    .modal-box {
      background:#fafae1;
      padding:24px;
      border-radius:10px;
      box-shadow:0 4px 12px rgba(0,0,0,0.15);
      text-align:center;
      max-width:320px; width:90%;
    }
    .modal-box h2 {
      margin-bottom:12px;
      color:#4CAF50;
      font-size:18px;
      font-weight:600;
    }
    .modal-box p {
      margin-bottom:20px;
      color:#333;
      font-size:15px;
    }
    .modal-box button {
      padding:8px 24px;
      border:2px solid #4CAF50;
      background:#fff;
      color:#4CAF50;
      border-radius:6px;
      font-size:14px;
      cursor:pointer;
      transition:background .3s, color .3s;
    }
    .modal-box button:hover {
      background:#4CAF50;
      color:#fff;
    }
    @media (max-width:600px) {
      .card { padding:20px; }
      .form-grid { gap:16px; }
      .button-group {
        flex-direction:column;
        gap:12px;
      }
      .button-group button { width:100%; }
    }
  </style>
</head>
<body>
  <div class="card">
    <h1>新增使用者帳密</h1>
    <form action="註冊.php" method="POST" onsubmit="return validateForm()">
      <div class="form-grid">
        <div class="form-group">
          <label for="employee_id">員工編號</label>
          <input type="text" id="employee_id" name="員工編號" placeholder="輸入員工編號" required>
        </div>
        <div class="form-group">
          <label for="name">姓名</label>
          <input type="text" id="name" name="姓名" placeholder="輸入姓名" required>
        </div>
        <div class="form-group">
          <label for="email">電子郵件</label>
          <input type="email" id="email" name="電子郵件" placeholder="輸入電子郵件" required>
        </div>
        <div class="form-group">
          <label for="phone">電話</label>
          <input type="text" id="phone" name="電話" placeholder="輸入電話號碼" required>
        </div>
        <div class="form-group">
          <label for="address">地址</label>
          <input type="text" id="address" name="地址" placeholder="輸入地址 (選填)">
        </div>
        <div class="form-group">
          <label for="department">部門</label>
          <select id="department" name="部門" required>
            <option value="">選擇部門</option>
            <?php foreach($dept as $d): ?>
              <option><?=htmlspecialchars($d['部門名稱'], ENT_QUOTES)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="position">職位</label>
          <select id="position" name="職位" required>
            <option value="">選擇職位</option>
            <?php foreach($pos as $p): ?>
              <option><?=htmlspecialchars($p['職位名稱'], ENT_QUOTES)?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label for="username">帳號</label>
          <input type="text" id="username" name="帳號" placeholder="創建帳號" required>
        </div>
      </div>

      <div class="form-group">
        <label for="password">密碼</label>
        <input
          type="password"
          id="password"
          name="密碼"
          placeholder="創建密碼"
          required
          oninput="checkPasswordLength()"
          autocomplete="new-password"
        >
        <div class="hint">密碼長度至少 6 個字符</div>
        <div id="password-strength" class="strength"></div>
      </div>
      <div class="form-group">
        <label for="password_confirm">確認密碼</label>
        <input
          type="password"
          id="password_confirm"
          name="密碼確認"
          placeholder="再次輸入密碼"
          required
          autocomplete="new-password"
        >
      </div>

      <div class="button-group">
        <button type="submit" class="submit-btn">提交</button>
        <button type="reset" class="reset-btn">清除</button>
      </div>
    </form>
  </div>

  <!-- 自訂錯誤 Modal -->
  <div id="modal" class="modal-overlay <?= $error_msg ? 'show' : '' ?>">
    <div class="modal-box">
      <h2>提示</h2>
      <p><?= $error_msg ?></p>
      <button id="modal-ok">確定</button>
    </div>
  </div>

  <script>
    function checkPasswordLength() {
      const pwd = document.getElementById('password').value;
      const disp = document.getElementById('password-strength');
      if (pwd.length < 6) {
        disp.textContent = '密碼長度不足，請至少 6 個字符';
        disp.className = 'strength weak';
      } else {
        disp.textContent = '';
        disp.className = 'strength';
      }
    }
    function validateForm() {
      const pwd = document.getElementById('password').value;
      const conf = document.getElementById('password_confirm').value;
      if (pwd.length < 6) {
        alert('密碼長度不足，至少 6 個字符，請重新輸入');
        return false;
      }
      if (pwd !== conf) {
        alert('密碼與確認密碼不一致，請再次確認');
        return false;
      }
      return true;
    }
    document.getElementById('modal-ok').addEventListener('click', () => {
      document.getElementById('modal').classList.remove('show');
      history.replaceState(null,'',location.pathname);
    });
  </script>
</body>
</html>

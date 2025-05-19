<?php  
session_start();
if (!isset($_SESSION['帳號'])) {
    header('Location: 註冊html.php');
    exit;
}

$current_user = $_SESSION['帳號'];
$servername   = 'localhost:3307';
$username     = 'root';
$password     = ' ';
$dbname       = '基金會';

// 建立連線
$db = new mysqli($servername, $username, $password, $dbname);
if ($db->connect_error) {
    die('連線錯誤: ' . $db->connect_error);
}
$db->set_charset('utf8mb4');

// 更新使用者資料（不含密碼）
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_user'])) {
    $員工編號   = $_POST['員工編號'];
    $name       = $_POST['姓名'];
    $phone      = $_POST['電話'];
    $address    = $_POST['地址'];
    $department = $_POST['部門']   ?? '';
    $position   = $_POST['職位']   ?? '';
    $permission = $_POST['權限管理'] ?? '';
    $email      = $_POST['電子郵件'];
    $account    = $_POST['帳號'];

    $sql = "UPDATE `註冊資料表`
            SET `員工編號`=?, `姓名`=?, `電話`=?, `地址`=?, `部門`=?, `職位`=?, `權限管理`=?, `電子郵件`=?
            WHERE `帳號`=?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param(
        'sssssssss',
        $員工編號, $name, $phone, $address,
        $department, $position, $permission,
        $email, $account
    );
    if ($stmt->execute()) {
        echo "<script>alert('資料更新成功！');</script>";
    } else {
        echo "<script>alert('錯誤: " . addslashes($stmt->error) . "');</script>";
    }
    $stmt->close();
}

// 刪除使用者
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {
    $account = $_POST['帳號'];
    $stmt = $db->prepare("DELETE FROM `註冊資料表` WHERE `帳號`=?");
    $stmt->bind_param('s', $account);
    if ($stmt->execute()) {
        echo "<script>alert('使用者刪除成功！');</script>";
    } else {
        echo "<script>alert('錯誤: " . addslashes($stmt->error) . "');</script>";
    }
    $stmt->close();
}

// 讀取資料
$result = $db->query("
    SELECT `員工編號`,`姓名`,`電話`,`地址`,`部門`,`職位`,`帳號`,`密碼`,`權限管理`,`電子郵件`
    FROM `註冊資料表`
");

// 準備部門/職位/權限選項
$職位選項 = '';
$res = $db->query("SELECT `職位名稱` FROM `職位`");
while ($r = $res->fetch_assoc()) {
    $職位選項 .= "<option value='".htmlspecialchars($r['職位名稱'])."'>"
                .htmlspecialchars($r['職位名稱'])."</option>";
}
$部門選項 = '';
$res = $db->query("SELECT `部門名稱` FROM `部門`");
while ($r = $res->fetch_assoc()) {
    $部門選項 .= "<option value='".htmlspecialchars($r['部門名稱'])."'>"
               .htmlspecialchars($r['部門名稱'])."</option>";
}

$db->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8"/>
  <title>帳號管理資料</title>
  <style>
    :root { --gap-sm:8px; --radius:6px; }
    body { margin:0; font-family:'Segoe UI',sans-serif; background:#f4f6f8; }
    .container { max-width:1400px; margin:20px auto; padding:0 16px; }
    .banner { background:linear-gradient(90deg,#4a90e2,#357ABD);
             color:#fff; display:flex; align-items:center;
             padding:16px; border-radius:6px 6px 0 0; }
    .banner a { color:#fff; text-decoration:none; margin-right:auto; }
    .banner span { font-size:1.1rem; }
    .table-wrapper { background:#fff; padding:16px;
                     border-radius:0 0 6px 6px;
                     box-shadow:0 2px 8px rgba(0,0,0,0.1); }
    table { width:100%; border-collapse:collapse; }
    th, td { padding:12px; text-align:center; border-bottom:1px solid #e0e0e0; }
    th { background:#4a90e2; color:#fff; }
    tr:nth-child(even){background:#f9f9f9;} tr:hover{background:#f1f1f1;}
    input, select, .email-tag, .email-input, .pwd-input, .pwd-display {
      font-size:1rem; border-radius:6px; box-sizing:border-box;
    }
    input[type="text"], input[type="email"].email-input,
    input[type="password"].pwd-input, select {
      padding:8px; width:100%; border:1px solid #ccc;
    }
    input[readonly] { background:#f5f5f5; }
    /* 密碼明碼顯示 */
    .pwd-display {
      padding:8px; width:100%; border:1px solid #ccc;
    }
    /* email 顯示 / 編輯 */
    .email-tag {
      display:inline-block; max-width:200px; overflow:hidden;
      text-overflow:ellipsis; white-space:nowrap;
      background:#e9f5ff; border:1px solid #cce5ff;
      padding:4px 8px; border-radius:6px;
    }
    .email-tag a { color:#4a90e2; text-decoration:none; }
    .email-input { display:none; }
    /* 按鈕群組 */
    .center-buttons {
      display:flex; gap:var(--gap-sm);
    }
    .btn {
      flex:1; padding:8px; font-weight:600; color:#fff;
      border:none; border-radius:6px; cursor:pointer;
      transition:transform .2s;
    }
    .btn-edit   { background:#ffc107; }
    .btn-save   { background:#28a745; }
    .btn-delete { background:#dc3545; }
    .btn:disabled { opacity:0.5; cursor:not-allowed; }
    .btn:hover { transform:scale(1.05); }
  </style>
</head>
<body>
  <div class="container">
    <div class="banner">
      <a onclick="history.back()">◀ 返回</a>
      <span>歡迎，<?= htmlspecialchars($current_user) ?>！</span>
    </div>
    <div class="table-wrapper">
      <table>
        <caption>帳號管理資料</caption>
        <tr>
<?php if($result->num_rows>0): ?>
  <?php foreach(['員工編號','姓名','電話','地址','部門','職位','帳號','密碼','權限管理','電子郵件'] as $h): ?>
    <th><?= $h ?></th>
  <?php endforeach; ?>
  <th>操作</th>
<?php endif; ?>
        </tr>
<?php if($result->num_rows>0): while($row=$result->fetch_assoc()): ?>
<tr>
  <form method="POST">
    <!-- 員工編號、姓名、電話、地址 -->
    <td><input type="text" name="員工編號" value="<?=htmlspecialchars($row['員工編號'])?>" readonly></td>
    <td><input type="text" name="姓名"     value="<?=htmlspecialchars($row['姓名'])?>"     readonly></td>
    <td><input type="text" name="電話"     value="<?=htmlspecialchars($row['電話'])?>"     readonly></td>
    <td><input type="text" name="地址"     value="<?=htmlspecialchars($row['地址'])?>"     readonly></td>

    <!-- 部門 -->
    <td>
      <input type="text" class="display-field" name="部門_display"
             value="<?=htmlspecialchars($row['部門'])?>" readonly>
      <select name="部門" style="display:none;"><?= $部門選項 ?></select>
    </td>

    <!-- 職位 -->
    <td>
      <input type="text" class="display-field" name="職位_display"
             value="<?=htmlspecialchars($row['職位'])?>" readonly>
      <select name="職位" style="display:none;"><?= $職位選項 ?></select>
    </td>

    <!-- 帳號 -->
    <td><input type="text" name="帳號" value="<?=htmlspecialchars($row['帳號'])?>" readonly></td>

    <!-- 密碼 -->
    <td>
      <input type="text" class="pwd-display" value="<?=htmlspecialchars($row['密碼'])?>" readonly>
      <input type="password" name="密碼" class="pwd-input"
             placeholder="輸入新密碼..." style="display:none;">
    </td>

    <!-- 權限管理 -->
    <td>
      <input type="text" class="display-field" name="權限管理_display"
             value="<?=htmlspecialchars($row['權限管理'])?>" readonly>
      <select name="權限管理" style="display:none;"><?= $職位選項 /* 同職位 */ ?></select>
    </td>

    <!-- 電子郵件 -->
    <td>
      <div class="email-tag" title="<?=htmlspecialchars($row['電子郵件'])?>">
        <a href="mailto:<?=htmlspecialchars($row['電子郵件'])?>">
          <?=htmlspecialchars($row['電子郵件'])?>
        </a>
      </div>
      <input type="email" name="電子郵件" class="email-input"
             value="<?=htmlspecialchars($row['電子郵件'])?>">
    </td>

    <!-- 按鈕群組 -->
    <td>
      <div class="center-buttons">
        <button type="button" class="btn btn-edit" onclick="editRow(this)">修改</button>
        <button type="submit" name="update_user" class="btn btn-save" disabled>確定</button>
        <button type="submit" name="delete_user" class="btn btn-delete"
                onclick="return confirm('確定刪除？');">清除</button>
      </div>
    </td>
  </form>
</tr>
<?php endwhile; else: ?>
<tr><td colspan="11">無資料顯示</td></tr>
<?php endif; ?>
      </table>
    </div>
  </div>

  <script>
    function editRow(btn) {
      const row = btn.closest('tr');
      // 將所有 readonly display-field 隱藏
      row.querySelectorAll('.display-field').forEach(el=>{
        el.style.display = 'none';
      });
      // 顯示對應的 select
      row.querySelectorAll('select').forEach(sel=>{
        sel.style.display = 'inline-block';
      });
      // 啟用其他 input 欄位
      row.querySelectorAll('input[readonly]').forEach(i=>{
        if (!i.classList.contains('display-field')) {
          i.removeAttribute('readonly');
        }
      });
      // 密碼欄：隱藏明碼顯示、顯示可編輯 input
      row.querySelector('.pwd-display').style.display = 'none';
      row.querySelector('.pwd-input').style.display   = 'inline-block';
      // email 欄：同理
      row.querySelector('.email-tag').style.display   = 'none';
      row.querySelector('.email-input').style.display = 'inline-block';
      // 啟用「確定」，停用自己
      row.querySelector('button[name="update_user"]').disabled = false;
      btn.disabled = true;
    }
  </script>
</body>
</html>

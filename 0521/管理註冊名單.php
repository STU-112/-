<?php  
session_start();
if (!isset($_SESSION['帳號'])) {
    header('Location: 註冊html.php');
    exit;
}
$current_user = $_SESSION['帳號'];
// DB 連線設定
$servername='localhost:3307'; $username='root'; $password=' '; $dbname='基金會';
$db=new mysqli($servername,$username,$password,$dbname);
if($db->connect_error) die('連線錯誤: '.$db->connect_error);
$db->set_charset('utf8mb4');

// 處理更新
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_user'])) {
    $account = $_POST['帳號'];
    if(isset($_POST['orig_pwd'])) {
        $orig=$_POST['orig_pwd']; $new=$_POST['new_pwd']??''; $conf=$_POST['confirm_pwd']??'';
        $p=$db->prepare("SELECT 密碼 FROM 註冊資料表 WHERE 帳號=?");
        $p->bind_param('s',$account);
        $p->execute(); $p->bind_result($current); $p->fetch(); $p->close();
        if($orig!==$current) {
            echo "<script>alert('原始密碼錯誤！');</script>";
        } elseif(strlen($new)<6) {
            echo "<script>alert('新密碼至少六碼！');</script>";
        } elseif($new!==$conf) {
            echo "<script>alert('確認密碼不符！');</script>";
        } else {
            $u=$db->prepare("UPDATE 註冊資料表 SET 密碼=? WHERE 帳號=?");
            $u->bind_param('ss',$new,$account);
            if($u->execute()) echo "<script>alert('密碼更新成功！');</script>";
            $u->close();
        }
    } else {
        $fields=['員工編號','姓名','電話','地址','部門','職位','權限管理','電子郵件'];
        $types=str_repeat('s',count($fields)+1);
        $values=[];
        foreach($fields as $f) $values[]=$_POST[$f]??'';
        $values[]=$account;
        $sql="UPDATE 註冊資料表 SET
            員工編號=?,姓名=?,電話=?,地址=?,部門=?,職位=?,權限管理=?,電子郵件=?
            WHERE 帳號=?";
        $stmt=$db->prepare($sql);
        $stmt->bind_param($types,...$values);
        if($stmt->execute()) echo "<script>alert('資料更新成功！');</script>";
        $stmt->close();
    }
}

// 刪除 使用者
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['delete_user'])) {
    $d=$db->prepare("DELETE FROM 註冊資料表 WHERE 帳號=?");
    $d->bind_param('s',$_POST['帳號']);
    if($d->execute()) echo "<script>alert('刪除成功！');</script>";
    $d->close();
}

// 讀取資料
$res=$db->query("SELECT 員工編號,姓名,電話,地址,部門,職位,帳號,密碼,權限管理,電子郵件 FROM 註冊資料表");
// 準備選項
$pos=''; foreach($db->query("SELECT 職位名稱 FROM 職位") as $r) $pos.="<option>".htmlspecialchars($r['職位名稱'])."</option>";
$dep=''; foreach($db->query("SELECT 部門名稱 FROM 部門") as $r) $dep.="<option>".htmlspecialchars($r['部門名稱'])."</option>";
$db->close();
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
<meta charset="UTF-8">
<title>帳號管理資料</title>
<style>
body{font-family:'Segoe UI',sans-serif;background:#f4f6f8;margin:0}
.container{max-width:1600px;margin:20px auto;padding:0 16px}
.banner{background:#357ABD;color:#fff;padding:16px;border-radius:6px 6px 0 0;display:flex;align-items:center}
.banner a{color:#fff;text-decoration:none;margin-right:auto}

.table-wrapper{
  background:#fff;
  padding:16px;
  border-radius:0 0 6px 6px;
  box-shadow:0 2px 8px rgba(0,0,0,0.1);
  overflow-x:auto;
}
table{width:100%;border-collapse:collapse;table-layout:auto}
th,td{
  padding:12px;
  text-align:center;
  border-bottom:1px solid #e0e0e0;
}
th{background:#4a90e2;color:#fff}
tr:nth-child(even){background:#f9f9f9}
tr:hover{background:#f1f1f1}

/* 微調欄位寬度 */
th:nth-child(1), td:nth-child(1) { min-width:140px; }   /* 員工編號 */
th:nth-child(2), td:nth-child(2) { min-width:100px; }   /* 姓名 */
th:nth-child(3), td:nth-child(3) { min-width:180px; }   /* 電話 */
th:nth-child(4), td:nth-child(4) { min-width:140px; }   /* 地址 */
th:nth-child(5), td:nth-child(5) { min-width:120px; }   /* 部門 */
th:nth-child(6), td:nth-child(6) { min-width:120px; }   /* 職位 */
th:nth-child(7), td:nth-child(7) { min-width:160px; }   /* 帳號 */
th:nth-child(8), td:nth-child(8) { min-width:100px; }   /* 密碼按鈕 */
th:nth-child(9), td:nth-child(9) { min-width:120px; }   /* 權限管理 */
th:nth-child(10), td:nth-child(10) { min-width:180px; } /* 電子郵件 */

input,select,button{font-size:1rem;border-radius:6px;box-sizing:border-box}
input[type="text"],select{padding:8px;width:100%;border:1px solid #ccc}
input[readonly]{background:#f5f5f5}

/* 按鈕樣式不變 */
.btn{padding:6px 12px;color:#fff;border:none;border-radius:6px;cursor:pointer;margin:0 4px;white-space:nowrap;}
.btn-edit{background:#ffc107}
.btn-save{background:#28a745}
.btn-delete{background:#dc3545}
.btn:disabled{opacity:0.5;cursor:not-allowed}
.btn:hover{transform:none}

.pwd-btn{background:#17a2b8}

.modal{position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.3);display:flex;justify-content:center;align-items:center;display:none}
.modal-content{background:#fff;padding:20px;border-radius:6px;width:320px;box-sizing:border-box}
.modal-content .pwd-field{position:relative;margin-bottom:8px}
.modal-content .pwd-field input{width:100%;padding:8px;border:1px solid #ccc}
.modal-content .toggle-eye{position:absolute;top:50%;right:12px;transform:translateY(-50%);cursor:pointer;font-size:1.2rem}
.modal-content input:not(.pwd-field input){width:100%;margin:8px 0;padding:8px;border:1px solid #ccc}
</style>
</head>
<body>
<div class="container">
  <div class="banner">
    <a onclick="history.back()">◀ 返回</a>
    <span>歡迎，<?=htmlspecialchars($current_user)?>！</span>
  </div>
  <div class="table-wrapper">
    <table>
      <tr>
        <?php if($res->num_rows): foreach(
          ['員工編號','姓名','電話','地址','部門','職位','帳號','密碼','權限管理','電子郵件']
          as $h): ?>
        <th><?= $h ?></th>
        <?php endforeach; ?>
        <th>操作</th>
      <?php endif; ?>
      </tr>
      <?php if($res->num_rows): while($r=$res->fetch_assoc()): ?>
      <tr>
        <form method="POST">
        <td><input type="text" name="員工編號" value="<?=htmlspecialchars($r['員工編號'])?>" readonly></td>
        <td><input type="text" name="姓名"     value="<?=htmlspecialchars($r['姓名'])?>"    readonly></td>
        <td><input type="text" name="電話"     value="<?=htmlspecialchars($r['電話'])?>"    readonly></td>
        <td><input type="text" name="地址"     value="<?=htmlspecialchars($r['地址'])?>"    readonly></td>
        <td>
          <select name="部門" disabled>
            <option><?=htmlspecialchars($r['部門'])?></option><?=$dep?>
          </select>
        </td>
        <td>
          <select name="職位" disabled>
            <option><?=htmlspecialchars($r['職位'])?></option><?=$pos?>
          </select>
        </td>
        <td><input type="text" name="帳號" value="<?=htmlspecialchars($r['帳號'])?>" readonly></td>
        <td>
          <button type="button" class="btn pwd-btn" disabled onclick="openModal('<?=htmlspecialchars($r['密碼'])?>')">
            變更密碼
          </button>
        </td>
        <td>
          <select name="權限管理" disabled>
            <option><?=htmlspecialchars($r['權限管理'])?></option><?=$pos?>
          </select>
        </td>
        <td><input type="text" name="電子郵件" value="<?=htmlspecialchars($r['電子郵件'])?>" readonly></td>
        <td>
          <button type="button" class="btn btn-edit" onclick="editRow(this)">修改</button>
          <button type="submit" name="update_user" class="btn btn-save" disabled>確定</button>
          <button type="submit" name="delete_user" class="btn btn-delete" onclick="return confirm('確定刪除？')">清除</button>
        </td>
        </form>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="11">無資料顯示</td></tr>
      <?php endif; ?>
    </table>
  </div>
</div>

<!-- 密碼修改 Modal -->
<div id="pwdModal" class="modal">
  <div class="modal-content">
    <h3>變更密碼</h3>
    <div class="pwd-field">
      <input type="password" id="orig" placeholder="原始密碼" readonly>
      <span class="toggle-eye" onclick="toggleModalPwd()">👁️</span>
    </div>
    <input type="password" id="newp" placeholder="新密碼 (6碼以上)">
    <input type="password" id="conf" placeholder="確認密碼">
    <input type="hidden" id="acc">
    <button class="btn btn-save" onclick="submitPwd()">儲存</button>
    <button class="btn btn-delete" onclick="closeModal()">取消</button>
  </div>
</div>

<script>
  let currentPw;
  function editRow(btn) {
    const tr = btn.closest('tr');
    tr.querySelectorAll('input[readonly]').forEach(i => i.removeAttribute('readonly'));
    tr.querySelectorAll('select').forEach(s => s.disabled = false);
    tr.querySelector('.pwd-btn').disabled = false;
    tr.querySelector('button[name="update_user"]').disabled = false;
    btn.disabled = true;
  }
  function openModal(pw) {
    currentPw = pw;
    const modal = document.getElementById('pwdModal');
    const orig  = document.getElementById('orig');
    orig.value = pw; orig.type = 'password';
    document.getElementById('acc').value =
      event.target.closest('tr').querySelector('input[name="帳號"]').value;
    modal.style.display = 'flex';
  }
  function closeModal() {
    document.getElementById('pwdModal').style.display = 'none';
  }
  function toggleModalPwd() {
    const o = document.getElementById('orig');
    o.type = o.type==='password'?'text':'password';
  }
  function submitPwd() {
    const orig = document.getElementById('orig').value;
    const nw   = document.getElementById('newp').value;
    const cf   = document.getElementById('conf').value;
    const acc  = document.getElementById('acc').value;
    if (orig!==currentPw) return alert('原始密碼錯誤！');
    if (nw.length<6)      return alert('新密碼至少六碼！');
    if (nw!==cf)          return alert('確認密碼不符！');
    const f = document.createElement('form'); f.method = 'POST'; f.style.display='none';
    [['orig_pwd',orig],['new_pwd',nw],['confirm_pwd',cf],['帳號',acc],['update_user',1]]
      .forEach(([n,v]) => {
        const i = document.createElement('input');
        i.name = n; i.value = v;
        f.appendChild(i);
      });
    document.body.appendChild(f);
    f.submit();
  }
</script>
</body>
</html>

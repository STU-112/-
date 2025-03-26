<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: ll1.php");
    exit;
}

// 獲取當前登入的帳號
$current_user = $_SESSION['帳號'];

// 建立資料庫連線
$servername = "localhost:3307"; 
$username = "root"; 
$password = "3307"; // 使用空白密碼
$dbname = "註冊"; 

$連接 = new mysqli($servername, $username, $password, $dbname);

// 檢查連接是否成功
if ($連接->connect_error) {
    die("資料庫連接失敗: " . $連接->connect_error);
}

// 查詢當前使用者的員工編號
$sql = "SELECT 員工編號 FROM 註冊資料表 WHERE 帳號 = ?";
$stmt = $連接->prepare($sql);
$stmt->bind_param("s", $current_user);
$stmt->execute();
$stmt->bind_result($員工編號);
$stmt->fetch();
$stmt->close();
$連接->close();
?>

<!DOCTYPE html>
<html lang="zh-Hant">
<head>
  <meta charset="UTF-8" />
  <title>預支請款表單</title>
  <?PHP include 'll1style.php'; ?>
</head>
<body>

<div class="container">
  <div class="form-container" aria-labelledby="formTitle">
    <!-- 表單提交到後端檔案：agg.php (可依需求修改) -->
    <form id="paymentForm" action="agg_chk.php" method="POST">
      <h1>財團法人台北市失親兒福利基金會</h1>

     <!-- 填表人員工編號 -->
      <div class="form-group">
  <label for="填表人">填表人：</label>
  <input type="text" id="填表人" name="填表人" value="<?php echo htmlspecialchars($員工編號); ?>" readonly>
</div>

      <!-- 單一欄位: 受款人姓名，前面顯示 Rxx -->
      <div class="form-group">
        <label for="受款人">
          <span id="recipientCodeSpan"></span>受款人姓名
        </label>
        <input type="text" id="受款人" name="受款人" placeholder="請輸入受款人姓名" required />
        <span class="error-message" id="受款人-error"></span>
        
        <!-- hidden 欄位: 把 Rxx 一併送到後端 (若需要存資料庫) -->
        <input type="hidden" id="hiddenRecipientCode" name="受款人編號" />
      </div>

      <!-- 填表日期 (今天, 唯讀) -->
      <div class="form-group">
        <label for="填表日期">填表日期：</label>
        <input type="date" id="填表日期" name="填表日期" required readonly />
        <span class="error-message" id="填表日期-error"></span>
      </div>

      <!-- 付款日期 (>= 今天) -->
      <div class="form-group">
        <label for="付款日期">付款日期：</label>
        <input type="date" id="付款日期" name="付款日期" required />
        <span class="error-message" id="付款日期-error"></span>
      </div>

      <!-- 支出項目 -->
      <div class="form-group">
        <label for="支出項目">請選擇支出項目：</label>
        <select id="支出項目" name="支出項目" onchange="updateConditionalFields()" required>
          <option value="">請選擇</option>
          <option value="活動費用">W-活動費用</option>
          <option value="獎學金">X-獎學金</option>
          <option value="經濟扶助">Y-經濟扶助</option>
          <option value="其他">Z-其他</option>
        </select>
        <span class="error-message" id="支出項目-error"></span>
      </div>

      <!-- 活動費用欄位(隱藏) -->
      <div id="活動費用欄位" class="conditional-group" style="display:none;">
        <div class="form-group">
          <label for="專案活動">(專案)活動名稱：</label>
          <select id="專案活動" name="專案活動">
            <option value="">請選擇</option>
            <option value="半日/一日型">半日/一日型</option>
            <option value="過夜型">過夜型</option>
            <option value="企業贊助活動">企業贊助活動</option>
            <option value="多次型">多次型</option>
            <option value="其他：體驗活動">其他：體驗活動</option>
          </select>
          <span class="error-message" id="專案活動-error"></span>
        </div>
        <div class="form-group">
          <label for="活動名稱">活動名稱：</label>
          <input type="text" id="活動名稱" name="活動名稱"/>
          <span class="error-message" id="活動名稱-error"></span>
        </div>
        <div class="form-group">
          <label for="專案日期">日期：</label>
          <input type="date" id="專案日期" name="專案日期"/>
          <span class="error-message" id="專案日期-error"></span>
        </div>
      </div>

      <!-- 獎學金欄位(隱藏) -->
      <div id="獎學金欄位" class="conditional-group" style="display:none;">
        <div class="form-group">
          <label for="獎學金人數">獎助學金共幾位：</label>
          <input type="number" id="獎學金人數" name="獎學金人數" min="1">
          <span class="error-message" id="獎學金人數-error"></span>
        </div>
        <div class="form-group">
          <label for="專案名稱">專案名稱：</label>
          <input type="text" id="專案名稱" name="專案名稱"/>
          <span class="error-message" id="專案名稱-error"></span>
        </div>
        <div class="form-group">
          <label for="主題">主題：</label>
          <input type="text" id="主題" name="主題"/>
          <span class="error-message" id="主題-error"></span>
        </div>
        <div class="form-group">
          <label for="獎學金日期">日期：</label>
          <input type="date" id="獎學金日期" name="獎學金日期"/>
          <span class="error-message" id="獎學金日期-error"></span>
        </div>
      </div>

      <!-- 經濟扶助欄位(隱藏) -->
      <div id="經濟扶助欄位" class="conditional-group" style="display:none;">
        <div class="form-group">
          <label for="經濟扶助">經濟扶助：</label>
          <select id="經濟扶助" name="經濟扶助">
            <option value="">請選擇</option>
            <option value="急難救助">急難救助</option>
            <option value="醫療補助">醫療補助</option>
            <option value="生活扶助">生活扶助</option>
            <option value="其他專案">其他專案</option>
          </select>
          <span class="error-message" id="經濟扶助-error"></span>
        </div>
      </div>

      <!-- 其他欄位(隱藏) -->
      <div id="其他欄位" class="conditional-group" style="display:none;">
        <div class="form-group">
          <label for="其他項目">其他項目：</label>
          <div id="其他項目">
            <label><input type="checkbox" name="其他項目[]" value="天使關懷專案"> 天使關懷專案：禮金</label><br>
            <label><input type="checkbox" name="其他項目[]" value="修繕費"> 修繕費</label><br>
            <label><input type="checkbox" name="其他項目[]" value="探訪交通差旅"> 探訪交通差旅</label><br>
            <label><input type="checkbox" name="其他項目[]" value="郵電費"> 郵電費</label><br>
            <label><input type="checkbox" name="其他項目[]" value="慰問關懷"> 慰問關懷</label><br>
            <label><input type="checkbox" name="其他項目[]" value="電信費"> 電信費</label><br>
            <label><input type="checkbox" name="其他項目[]" value="課輔鐘點費"> 課輔鐘點費</label><br>
            <label><input type="checkbox" name="其他項目[]" value="文具印刷"> 文具印刷</label><br>
            <label><input type="checkbox" name="其他項目[]" value="心輔諮商"> 心輔諮商</label><br>
            <label><input type="checkbox" name="其他項目[]" value="電腦用品"> 電腦用品</label><br>
            <label><input type="checkbox" name="其他項目[]" value="關懷站連結"> 關懷站連結</label><br>
            <label><input type="checkbox" name="其他項目[]" value="慶典福利"> 慶典福利</label><br>
            <label><input type="checkbox" name="其他項目[]" value="教育訓練"> 教育訓練</label><br>
            <label><input type="checkbox" name="其他項目[]" value="雜項購置"> 雜項購置</label><br>
          </div>
          <span class="error-message" id="其他項目-error"></span>
        </div>
      </div>
      <!-- 說明 -->
      <div class="form-group">
        <label for="說明">說明：</label>
        <textarea id="說明" name="說明" placeholder="輸入您的備註或註解..." required></textarea>
        <span class="error-message" id="說明-error"></span>
      </div>

      <!-- 金額 (轉國字) -->
      <div class="form-group">
        <label for="國字金額">金額：</label>
        <input type="number" id="國字金額" name="國字金額" placeholder="請輸入金額" min="0" required oninput="convertAmountToChinese()">
        <input type="hidden" id="國字金額_hidden" name="國字金額_hidden">
        <div id="國字金額_display" class="chinese-amount-display"></div>
        <span class="error-message" id="國字金額-error"></span>
      </div>

      <!-- 支付方式 -->
      <div class="form-group">
        <label for="支付方式">支付方式：</label>
        <select id="支付方式" name="支付方式" onchange="togglePaymentFields()" required>
          <option value="">請選擇</option>
          <option value="現金">現金</option>
          <option value="轉帳">轉帳</option>
          <option value="劃撥">劃撥</option>
          <option value="匯款">匯款</option>
          <option value="支票">支票</option>
        </select>
        <span class="error-message" id="支付方式-error"></span>
      </div>

      <!-- 現金簽收欄位(隱藏) -->
      <div id="現金簽收欄位" class="conditional-group" style="display:none;">
        <div class="form-group">
          <label for="簽收日">簽收日：</label>
          <input type="date" id="簽收日" name="簽收日" required>
          <span class="error-message" id="簽收日-error"></span>
        </div>
      </div>

      <!-- 轉帳/劃撥/匯款 欄位(隱藏) -->
      <div id="郵局欄" class="conditional-group" style="display:none;" aria-live="polite">
        <div class="form-group">
          <label for="銀行">銀行(郵局)：</label>
          <input type="text" id="銀行" name="銀行郵局" placeholder="請輸入銀行名稱" required>
          <span class="error-message" id="銀行-error"></span>
        </div>
        <div class="form-group">
          <label for="transferBankBranch">分行：</label>
          <input type="text" id="transferBankBranch" name="分行" placeholder="請輸入分行名稱" required>
          <span class="error-message" id="transferBankBranch-error"></span>
        </div>
        <div class="form-group">
          <label for="transferAccountName">戶名：</label>
          <input type="text" id="transferAccountName" name="戶名" placeholder="請輸入戶名" required>
          <span class="error-message" id="transferAccountName-error"></span>
        </div>
        <div class="form-group">
          <label for="transferAccountNumber">帳號：</label>
          <input type="text" id="transferAccountNumber" name="帳號" placeholder="請輸入帳號" required>
          <span class="error-message" id="transferAccountNumber-error"></span>
        </div>
      </div>

      <!-- 支票欄位(隱藏) -->
      <div id="支票欄位" class="conditional-group" style="display:none;" aria-live="polite">
        <div class="form-group">
          <label for="票號">票號：</label>
          <input type="text" id="票號" name="票號" placeholder="請輸入票號" required>
          <span class="error-message" id="票號-error"></span>
        </div>
        <div class="form-group">
          <label for="到期日">到期日：</label>
          <input type="date" id="到期日" name="到期日" required>
          <span class="error-message" id="到期日-error"></span>
        </div>
        <div class="form-group">
          <label for="預支金額">請輸入預支金額：</label>
          <input type="number" id="預支金額" name="預支金額" min="0" required>
          <span class="error-message" id="預支金額-error"></span>
        </div>
      </div>

      <!-- 提交按鈕 -->
      <button type="submit" id="submitBtn">提交表單</button>
    </form>
  </div>
</div>

<!-- 提交成功模態框 -->
<div id="successModal" class="modal" style="display:none;">
  <div class="modal-content">
    <h2>提交成功</h2>
    <p>您的表單已成功提交，我們將盡快處理。</p>
    <div class="modal-buttons">
      <button class="close-btn" onclick="closeSuccessModal()">確定</button>
    </div>
  </div>
</div>

<script>
// ------------------------------------------------------
// A. 受款人編號邏輯：確保「未送出就刷新，不會換編號」
//    只有「成功送出」後，下次載入才遞增
// ------------------------------------------------------
function loadOrCreateRecipientCode(){
  let currentCode = localStorage.getItem("currentCode");
  if(currentCode){
    // 若 localStorage 裏已有 currentCode => 直接用它
    return currentCode;
  } else {
    // 若沒有 => 產生一個新的 Rxx (不會立刻更新 recipientNo)
    // 取得最後的編號數字
    let lastNo = localStorage.getItem("recipientNo");
    if(!lastNo){
      lastNo = 0;
    }
    lastNo = parseInt(lastNo, 10);
    // +1 組成 Rxx
    const newNo = lastNo + 1;
    const code = 'R' + String(newNo).padStart(2,'0');
    // 暫存到 currentCode (代表此張表單用這個編號)
    localStorage.setItem("currentCode", code);
    return code;
  }
}

// ------------------------------------------------------
// B. 在標籤/隱藏欄位顯示編號
// ------------------------------------------------------
function displayRecipientCode(code){
  const span = document.getElementById('recipientCodeSpan');
  if(span){
    span.textContent = code; // Rxx
  }
  // hidden 欄位 => 用於提交到後端
  const hiddenEl = document.getElementById('hiddenRecipientCode');
  if(hiddenEl){
    hiddenEl.value = code;
  }
}

// ------------------------------------------------------
// C. 提交成功後 => 才將 currentCode 記錄到 recipientNo
//    並清除 currentCode 以便下次載入時換新碼
// ------------------------------------------------------
function finalizeRecipientCode(){
  const code = localStorage.getItem("currentCode");
  if(code){
    // code = 'Rxx' => parse xx
    const numPart = parseInt(code.replace('R',''),10) || 0;
    // 把 localStorage.recipientNo 更新為 numPart
    localStorage.setItem("recipientNo", numPart);
    // 移除 currentCode => 下次載入就會產生新碼
    localStorage.removeItem("currentCode");
  }
}

// ------------------------------------------------------
// D. 初始化：填表日期=今天(唯讀), 付款日期>=今天
// ------------------------------------------------------
function setDateConstraints(){
  const now = new Date();
  const y   = now.getFullYear();
  const m   = String(now.getMonth()+1).padStart(2,'0');
  const d   = String(now.getDate()).padStart(2,'0');
  const todayString = `${y}-${m}-${d}`;

  // 填表日期 => 設定為今天,且只讀
  const fillDateEl = document.getElementById('填表日期');
  fillDateEl.value = todayString;

  // 付款日期 => 可選(含)今天之後
  const payDateEl = document.getElementById('付款日期');
  payDateEl.setAttribute('min', todayString);
}

// ------------------------------------------------------
// E. 動態顯示/隱藏 - 支出項目/支付方式/金額轉國字 (舊功能略)
// ------------------------------------------------------
function convertAmountToChinese(){
  const amt = document.getElementById('國字金額').value;
  const disp= document.getElementById('國字金額_display');
  const hid = document.getElementById('國字金額_hidden');
  if(!amt || parseFloat(amt)<=0){
    disp.textContent='';
    hid.value='';
    return;
  }
  const val = parseInt(amt,10);
  disp.textContent = numberToChinese(val);
  hid.value        = disp.textContent;
}
function numberToChinese(num){
  if(num===0)return "零元整";
  const digits="零一二三四五六七八九";
  const units=["","十","百","千"];
  const bigUnits=["","萬","億","兆"];
  let str=num.toString(),result="",zero=false;
  const groups=[];
  while(str.length>0){
    groups.unshift(str.slice(-4));
    str=str.slice(0,-4);
  }
  groups.forEach((group,idx)=>{
    let groupResult="", isZeroGroup=true;
    for(let i=0;i<group.length;i++){
      const n=parseInt(group[i]);
      const pos=group.length-i-1;
      if(n!==0){
        if(zero){groupResult+=digits[0]; zero=false;}
        groupResult+= digits[n]+units[pos];
        isZeroGroup=false;
      } else {
        if(pos!==0) zero=true;
      }
    }
    if(!isZeroGroup){
      groupResult+= bigUnits[groups.length-idx-1];
    } else if(idx===groups.length-1){
      groupResult+= bigUnits[groups.length-idx-1];
    }
    result+=groupResult;
  });
  result=result.replace(/零+/g,"零").replace(/零$/,"");
  return result+"元整";
}

function updateConditionalFields(){
  const item = document.getElementById('支出項目').value;
  const blocks = ["活動費用欄位","獎學金欄位","經濟扶助欄位","其他欄位"];
  blocks.forEach(id=>{
    const blockEl = document.getElementById(id);
    if(!blockEl) return;
    blockEl.style.display='none';
    // 移除必填
    Array.from(blockEl.querySelectorAll('input,select,textarea')).forEach(el=>{
      el.removeAttribute('required');
      el.value='';
      if(el.type==='checkbox') el.checked=false;
    });
  });
  if(item==='活動費用'){
    document.getElementById("活動費用欄位").style.display='block';
    document.getElementById("專案活動").setAttribute('required','required');
    document.getElementById("活動名稱").setAttribute('required','required');
    document.getElementById("專案日期").setAttribute('required','required');
  }
  else if(item==='獎學金'){
    document.getElementById("獎學金欄位").style.display='block';
    document.getElementById("獎學金人數").setAttribute('required','required');
    document.getElementById("專案名稱").setAttribute('required','required');
    document.getElementById("主題").setAttribute('required','required');
    document.getElementById("獎學金日期").setAttribute('required','required');
  }
  else if(item==='經濟扶助'){
    document.getElementById("經濟扶助欄位").style.display='block';
    document.getElementById("經濟扶助").setAttribute('required','required');
  }
  else if(item==='其他'){
    document.getElementById("其他欄位").style.display='block';
  }
}

function togglePaymentFields(){
  const payWay = document.getElementById('支付方式').value;
  const blocks = ["現金簽收欄位","郵局欄","支票欄位"];
  blocks.forEach(id=>{
    const blockEl = document.getElementById(id);
    if(!blockEl) return;
    blockEl.style.display='none';
    Array.from(blockEl.querySelectorAll('input,select')).forEach(el=>{
      el.removeAttribute('required');
      el.value='';
    });
  });
  if(payWay==='現金'){
    document.getElementById('現金簽收欄位').style.display='block';
    document.getElementById('簽收日').setAttribute('required','required');
  }
  else if(['轉帳','劃撥','匯款'].includes(payWay)){
    document.getElementById('郵局欄').style.display='block';
    Array.from(document.querySelectorAll('#郵局欄 input')).forEach(el=> el.setAttribute('required','required'));
  }
  else if(payWay==='支票'){
    document.getElementById('支票欄位').style.display='block';
    Array.from(document.querySelectorAll('#支票欄位 input')).forEach(el=> el.setAttribute('required','required'));
  }
}

// ------------------------------------------------------
// F. 表單驗證 (可依需求自行增補)
// ------------------------------------------------------
function validateForm(){
  // 這裡可加更多檢查
  return true;
}

// ------------------------------------------------------
// G. 提交成功 => 顯示彈窗 & finalizeRecipientCode
// ------------------------------------------------------
function showSuccessModal(){
  sessionStorage.setItem('formSubmitted','true');
  document.getElementById('successModal').style.display='flex';
}
function closeSuccessModal(){
  document.getElementById('successModal').style.display='none';
}

// ------------------------------------------------------
// H. 綁定表單提交
// ------------------------------------------------------
document.getElementById('paymentForm').addEventListener('submit', function(e){
  e.preventDefault();
  if(validateForm()){
    const submitBtn = document.getElementById('submitBtn');
    submitBtn.disabled = true;
    submitBtn.textContent = "提交中...";

    const formData = new FormData(this);
    fetch(this.action, {method:this.method, body:formData})
      .then(res => res.text())
      .then(data => {
        // ★ 提交成功 => 顯示彈窗
        showSuccessModal();

        // ★ finalize => 讓下次產生新編號
        finalizeRecipientCode();

        // 重置表單
        this.reset();

        // 再次做初始化: 產生一個新的 currentCode (下次可繼續操作)
        const newCode = loadOrCreateRecipientCode();
        displayRecipientCode(newCode);

        setDateConstraints();
        updateConditionalFields();
        togglePaymentFields();
        document.getElementById('國字金額_display').textContent='';

        submitBtn.disabled = false;
        submitBtn.textContent = "提交表單";
      })
      .catch(err =>{
        alert('提交失敗，請稍後再試。');
        submitBtn.disabled = false;
        submitBtn.textContent = "提交表單";
      });
  }
});

// ------------------------------------------------------
// I. onload: 初始化
// ------------------------------------------------------
window.onload=function(){
  // 1) 如果當前 localStorage 有 currentCode => 用它；無則新建
  const code = loadOrCreateRecipientCode();
  // 顯示在標籤 & hidden欄位
  displayRecipientCode(code);

  // 2) 設定日期(填表=今天,付款>=今天)
  setDateConstraints();

  // 3) 隱藏/顯示對應欄位
  updateConditionalFields();
  togglePaymentFields();

  // 4) 若剛提交成功後刷新, 亦可自動彈窗
  if(sessionStorage.getItem('formSubmitted')==='true'){
    showSuccessModal();
    sessionStorage.removeItem('formSubmitted');
  }
};
</script>
</body>
</html>

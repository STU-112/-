<?php
session_start();

// 檢查是否已登入
if (!isset($_SESSION['帳號'])) {
    header("Location: 7html.php");
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
  <title>預支表單</title>
  <style>
    :root {
      --primary-color: #4A90E2;
      --secondary-color: #50E3C2;
      --accent-color-1: #F5A623;
      --accent-color-2: #9013FE;
      --background-color: #F0F4F8;
      --card-background-color: rgba(255, 255, 255, 0.95);
      --input-border-color: #CCCCCC;
      --input-focus-border-color: var(--primary-color);
      --button-background-color: var(--primary-color);
      --button-hover-background-color: #357ABD;
      --label-color: #333333;
      --text-color: #333333;
      --border-color: #DDDDDD;
      --error-color: red;
      --transition-speed: 0.3s;
      --font-family: 'Poppins', sans-serif;
      --shadow-color: rgba(0, 0, 0, 0.1);
      --hover-shadow: rgba(0, 0, 0, 0.2);
    }
    * {
      box-sizing: border-box;
      margin: 0; 
      padding: 0;
    }
    body {
      font-family: var(--font-family);
      background-color: var(--background-color);
      padding: 20px; 
      margin: 0;
      display: flex; 
      align-items: center; 
      justify-content: center;
      min-height: 100vh; 
      color: var(--text-color);
      position: relative; 
      overflow-y: auto;
    }
    .container {
      width: 100%; 
      max-width: 600px;
      padding: 40px;
      background: var(--card-background-color);
      border-radius: 20px;
      box-shadow: 0 15px 30px var(--shadow-color);
      border: 1px solid var(--border-color);
      backdrop-filter: blur(10px);
      position: relative; 
      overflow: hidden;
    }
    .container::before {
      content: ''; 
      position: absolute; 
      top: -50px; 
      right: -50px;
      width: 150px; 
      height: 150px; 
      background: var(--accent-color-1);
      border-radius: 50%; 
      opacity: 0.2;
    }
    .container::after {
      content: ''; 
      position: absolute; 
      bottom: -50px; 
      left: -50px;
      width: 150px; 
      height: 150px; 
      background: var(--accent-color-2);
      border-radius: 50%; 
      opacity: 0.2;
    }
    h1 {
      text-align: center; 
      color: var(--primary-color);
      margin-bottom: 40px; 
      font-size: 2rem; 
      font-weight: 700;
    }
    form { 
      display: flex; 
      flex-direction: column; 
      gap: 20px; 
    }
    .form-group { 
      display: flex; 
      flex-direction: column; 
      margin-bottom: 10px; 
    }
    .form-group label {
      font-weight: 600; 
      color: var(--label-color);
      margin-bottom: 5px; 
      font-size: 1rem;
    }
    .form-group input,
    .form-group select,
    .form-group textarea {
      width: 100%; 
      padding: 12px; 
      border-radius: 8px;
      border: 1px solid var(--input-border-color);
      font-size: 1rem;
      transition: border-color var(--transition-speed), box-shadow var(--transition-speed);
      background-color: #fff;
    }
    .form-group input:focus,
    .form-group select:focus,
    .form-group textarea:focus {
      outline: none;
      border-color: var(--input-focus-border-color);
      box-shadow: 0 0 5px rgba(74, 144, 226, 0.5);
    }
    .conditional-group {
      display: none; 
      margin-top: 20px; 
      padding: 20px 25px;
      background-color: #fff; 
      border-left: 5px solid var(--accent-color-1);
      border-radius: 10px; 
      box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
      transition: all var(--transition-speed) ease;
    }
    .btn-row { 
      text-align: center; 
      margin-top: 20px; 
    }
    button {
      padding: 14px 20px; 
      font-size: 1rem; 
      font-weight: 600; 
      color: #fff;
      background-color: var(--button-background-color);
      border: none; 
      border-radius: 8px; 
      cursor: pointer;
      transition: background-color var(--transition-speed), transform 0.2s;
    }
    button:hover {
      background-color: var(--button-hover-background-color);
      transform: translateY(-3px);
    }
    #recipientRow {
      display: flex; 
      align-items: center; 
      gap: 8px;
    }
    #recipientName {
      color: red; 
      font-weight: bold; 
      user-select: none; 
    }
    #國字金額_display { 
      color: blue; 
      margin-top: 5px; 
    }
    .others-checkboxes {
      display: grid; 
      grid-template-columns: repeat(2, 1fr); 
      gap: 6px 20px;
    }
    .others-checkboxes label { 
      margin-bottom: 0; 
      font-weight: normal; 
    }
    /* Modal */
    .modal {
      display: none; 
      position: fixed; 
      z-index: 1000; 
      left: 0; 
      top: 0;
      width: 100%; 
      height: 100%; 
      background-color: rgba(0,0,0,0.5);
      justify-content: center; 
      align-items: center;
    }
    @keyframes fadeIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }
    .modal-content {
      background: #fff; 
      padding: 30px; 
      border-radius: 12px; 
      text-align: center;
      box-shadow: 0 15px 30px rgba(0,0,0,0.3); 
      max-width: 400px; 
      width: 80%;
      animation: fadeIn 0.3s ease;
    }
    .modal-content h2 {
      font-size: 1.5rem; 
      margin-bottom: 10px; 
      color: var(--primary-color);
    }
    .modal-content p {
      font-size: 1rem; 
      color: #666; 
      margin-bottom: 20px;
    }
    .modal-buttons button {
      padding: 10px 20px; 
      margin: 0 10px;
      background-color: var(--button-background-color); 
      color: white; 
      border: none;
      border-radius: 5px; 
      cursor: pointer; 
      transition: background-color var(--transition-speed);
    }
    .modal-buttons button:hover {
      background-color: var(--button-hover-background-color);
    }
  </style>
</head>
<body>
  <div class="container">
    <h1>財團法人台北市失親兒福利基金會</h1>
    <!-- (移除 novalidate，讓瀏覽器檢查必填欄位) -->
    <form id="paymentForm" action="7.php" method="POST" enctype="multipart/form-data">
      <!-- 填表人 -->
          <div class="form-group">
            <label for="填表人">填表人：</label>
             <input type="text" id="填表人" name="填表人" value="<?php echo htmlspecialchars($員工編號); ?>" readonly>
          </div>

      <!-- 受款人：代號 (Rxx) + 姓名 -->
      <div class="form-group">
         <label><span id="recipientName"></span>受款人：</label>
        <div id="recipientRow">
         
          <input type="hidden" id="受款人" name="受款人">
          <input type="text" id="受款人姓名" name="受款人姓名" placeholder="請輸入受款人姓名" required>
        </div>
      </div>

      <!-- 填表日期 -->
      <div class="form-group">
        <label for="填表日期">填表日期：</label>
        <input type="date" id="填表日期" name="填表日期" required readonly>
      </div>

      <!-- 付款日期 -->
      <div class="form-group">
        <label for="付款日期">付款日期：</label>
        <input type="date" id="付款日期" name="付款日期" required>
      </div>

      <!-- 支出項目 (含前面英文字母) -->
      <div class="form-group">
        <label for="支出項目">支出項目：</label>
        <select id="支出項目" name="支出項目" required onchange="updateConditionalFields()">
          <option value="">請選擇</option>
          <option value="W活動費用">W活動費用</option>
          <option value="X獎學金">X獎學金</option>
          <option value="Y經濟扶助">Y經濟扶助</option>
          <option value="Z其他">Z其他</option>
        </select>
      </div>

      <!-- (a) 活動費用欄位 -->
      <div id="活動費用欄位" class="conditional-group">
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
        </div>
        <div class="form-group">
          <label for="活動名稱">活動名稱：</label>
          <input type="text" id="活動名稱" name="活動名稱">
        </div>
        <div class="form-group">
          <label for="專案日期">活動日期：</label>
          <input type="date" id="專案日期" name="專案日期">
        </div>
      </div>

      <!-- (b) 獎學金欄位 -->
      <div id="獎學金欄位" class="conditional-group">
        <div class="form-group">
          <label for="獎學金人數">獎助學金共幾位：</label>
          <input type="number" id="獎學金人數" name="獎學金人數" min="1">
        </div>
        <div class="form-group">
          <label for="專案名稱">專案名稱：</label>
          <input type="text" id="專案名稱" name="專案名稱">
        </div>
        <div class="form-group">
          <label for="主題">主題：</label>
          <input type="text" id="主題" name="主題">
        </div>
        <div class="form-group">
          <label for="獎學金日期">日期：</label>
          <input type="date" id="獎學金日期" name="獎學金日期">
        </div>
      </div>

      <!-- (c) 經濟扶助欄位 -->
      <div id="經濟扶助欄位" class="conditional-group">
        <div class="form-group">
          <label for="經濟扶助">經濟扶助：</label>
          <select id="經濟扶助" name="經濟扶助">
            <option value="">請選擇</option>
            <option value="急難救助">急難救助</option>
            <option value="醫療補助">醫療補助</option>
            <option value="生活扶助">生活扶助</option>
            <option value="其他專案">其他專案</option>
          </select>
        </div>
      </div>

      <!-- (d) 其他欄位 -->
      <div id="其他欄位" class="conditional-group">
        <div class="form-group">
          <label>其他項目：</label>
          <div class="others-checkboxes">
            <label><input type="checkbox" name="其他項目[]" value="天使關懷專案：禮金"> 天使關懷專案：禮金</label>
            <label><input type="checkbox" name="其他項目[]" value="探訪交通差旅"> 探訪交通差旅</label>
            <label><input type="checkbox" name="其他項目[]" value="慰問關懷"> 慰問關懷</label>
            <label><input type="checkbox" name="其他項目[]" value="修繕費"> 修繕費</label>
            <label><input type="checkbox" name="其他項目[]" value="電信費"> 電信費</label>
            <label><input type="checkbox" name="其他項目[]" value="課輔鐘點費"> 課輔鐘點費</label>
            <label><input type="checkbox" name="其他項目[]" value="心輔諮商"> 心輔諮商</label>
            <label><input type="checkbox" name="其他項目[]" value="教育訓練"> 教育訓練</label>
            <label><input type="checkbox" name="其他項目[]" value="電腦用品"> 電腦用品</label>
            <label><input type="checkbox" name="其他項目[]" value="郵電費"> 郵電費</label>
            <label><input type="checkbox" name="其他項目[]" value="文具印刷"> 文具印刷</label>
            <label><input type="checkbox" name="其他項目[]" value="關懷站連結"> 關懷站連結</label>
            <label><input type="checkbox" name="其他項目[]" value="慶典福利"> 慶典福利</label>
            <label><input type="checkbox" name="其他項目[]" value="雜項購置"> 雜項購置</label>
          </div>
        </div>
      </div>

      <!-- 說明 -->
      <div class="form-group">
        <label for="說明">說明：</label>
        <textarea id="說明" name="說明" rows="3" required></textarea>
      </div>

      <!-- 支付方式 -->
      <div class="form-group">
        <label for="支付方式">支付方式：</label>
        <select id="支付方式" name="支付方式" onchange="togglePaymentFields()" required>
          <option value="">請選擇</option>
          <option value="現金">現金</option>
          <option value="轉帳">轉帳</option>
          <option value="匯款">匯款</option>
          <option value="支票">支票</option>
          <option value="劃撥">劃撥</option>
        </select>
      </div>

      <!-- 金額 -->
      <div class="form-group">
        <label for="國字金額">金額：</label>
        <input type="number" id="國字金額" name="國字金額" min="0" required oninput="convertAmountToChinese()">
        <input type="hidden" id="國字金額_hidden" name="國字金額_hidden">
        <div id="國字金額_display"></div>
      </div>

      <!-- 現金簽收欄位 -->
      <div id="現金簽收欄位" class="conditional-group">
        <div class="form-group">
          <label for="簽收金額">簽收金額：</label>
          <input type="number" id="簽收金額" name="簽收金額" min="0">
        </div>
        <div class="form-group">
          <label for="簽收人">簽收人：</label>
          <input type="text" id="簽收人" name="簽收人" placeholder="請輸入簽收人">
        </div>
        <div class="form-group">
          <label for="簽收日">簽收日：</label>
          <input type="date" id="簽收日" name="簽收日">
        </div>
      </div>

      <!-- 轉帳/匯款/劃撥 -->
      <div id="郵局欄" class="conditional-group">
        <div class="form-group">
          <label for="銀行">銀行(郵局)：</label>
          <input type="text" id="銀行" name="銀行郵局" placeholder="請輸入銀行名稱">
        </div>
        <div class="form-group">
          <label for="transferBankBranch">分行：</label>
          <input type="text" id="transferBankBranch" name="分行" placeholder="請輸入分行名稱">
        </div>
        <div class="form-group">
          <label for="transferAccountName">戶名：</label>
          <input type="text" id="transferAccountName" name="戶名" placeholder="請輸入戶名">
        </div>
        <div class="form-group">
          <label for="transferAccountNumber">帳號：</label>
          <input type="text" id="transferAccountNumber" name="帳號" placeholder="請輸入帳號">
        </div>
      </div>

      <!-- 支票欄位 -->
      <div id="支票欄位" class="conditional-group">
        <div class="form-group">
          <label for="票號">票號：</label>
          <input type="text" id="票號" name="票號" placeholder="請輸入票號">
        </div>
        <div class="form-group">
          <label for="到期日">到期日：</label>
          <input type="date" id="到期日" name="到期日">
        </div>
        <div class="form-group">
          <label for="付款金額">付款金額：</label>
          <input type="number" id="付款金額" name="付款金額" min="0">
        </div>
      </div>

      <!-- 提交按鈕 -->
      <div class="btn-row">
        <button type="submit" id="submitBtn">提交表單</button>
      </div>
    </form>
  </div>

  <!-- 提交成功 Modal -->
  <div id="successModal" class="modal">
    <div class="modal-content">
      <h2>表單提交成功！</h2>
      <p>您的表單已成功提交，我們將盡快處理。</p>
      <div class="modal-buttons">
        <button onclick="closeSuccessModal()">確定</button>
      </div>
    </div>
  </div>

  <script>
    // 1) 受款人編號（僅傳送代號）
    function loadOrCreateRecipientCode(){
      let currentCode = localStorage.getItem("currentCode");
      if(currentCode) return currentCode;
      let lastNo = parseInt(localStorage.getItem("recipientNo") || "0", 10);
      const newNo = lastNo + 1;
      const code = 'R' + String(newNo).padStart(2, '0');
      localStorage.setItem("currentCode", code);
      return code;
    }
    function displayRecipientCode(code){
      document.getElementById('recipientName').textContent = code;
      document.getElementById('受款人').value = code;
    }
    function finalizeRecipientCode(){
      const code = localStorage.getItem("currentCode");
      if(code){
        const numPart = parseInt(code.replace('R',''), 10) || 0;
        localStorage.setItem("recipientNo", numPart);
        localStorage.removeItem("currentCode");
      }
    }

    // 2) 填表日期預設今日
    function setDateConstraints(){
      const now = new Date();
      const y = now.getFullYear();
      const m = String(now.getMonth()+1).padStart(2, '0');
      const d = String(now.getDate()).padStart(2, '0');
      const todayStr = `${y}-${m}-${d}`;
      document.getElementById('填表日期').value = todayStr;
      document.getElementById('付款日期').setAttribute('min', todayStr);
    }

    // 4) 金額轉中文
    
    function convertAmountToChinese() {
        const amountInput = document.getElementById('國字金額');
        const chineseDisplay = document.getElementById('國字金額_display');
        const hiddenInput = document.getElementById('國字金額_hidden');

        const amount = amountInput.value;

        if (amount === '' || parseFloat(amount) <= 0) {
            chineseDisplay.textContent = '';
            hiddenInput.value = '';
            return;
        }

        const integerAmount = Math.floor(parseFloat(amount));
        const chineseAmount = numberToChinese(integerAmount);
        chineseDisplay.textContent = chineseAmount;
        hiddenInput.value = chineseAmount;
    }

    // 改進的數字轉中文函式，處理「兩」的使用
    function numberToChinese(num) {
    if (num === 0) return "零元整";

    const digits = "零一二三四五六七八九";
    const units = ["", "十", "百", "千"];
    const bigUnits = ["", "萬", "億", "兆"];

    let str = num.toString();
    let result = "";
    let zero = false;

    // 將數字分成每四位一組，從右到左處理
    const groups = [];
    while (str.length > 0) {
        groups.unshift(str.slice(-4));
        str = str.slice(0, -4);
    }

    // 處理每一組四位數
    groups.forEach((group, groupIndex) => {
        let groupResult = "";
        let isZeroGroup = true;

        for (let i = 0; i < group.length; i++) {
            const n = parseInt(group[i]);
            const pos = group.length - i - 1;

            if (n !== 0) {
                if (zero) {
                    groupResult += digits[0];
                    zero = false;
                }
                if (n === 1 && pos === 1 && groupResult === "") {
                    // 如果是「十」位且數字為1，省略「一」
                    groupResult += units[pos];
                } else {
                    groupResult += digits[n] + units[pos];
                }
                isZeroGroup = false;
            } else {
                if (pos !== 0) {
                    zero = true; // 需要記錄零，但不立即輸出
                }
            }
        }

        // 處理該組的結果和大單位
        if (!isZeroGroup) {
            groupResult += bigUnits[groups.length - groupIndex - 1];
        } else if (groupIndex === groups.length - 1) {
            groupResult += bigUnits[groups.length - groupIndex - 1]; // 億或萬後的組仍需保留單位
        }

        result += groupResult;
    });

    // 去掉多餘的零
    result = result.replace(/零+/g, "零");

    // 移除末尾的零
    result = result.replace(/零$/, "");

    // 加上「元整」
    return result + "元整";
}

// 測試範例
console.log(numberToChinese(123456789)); // 一億二千三百四十五萬六千七百八十九元整
console.log(numberToChinese(123456));    // 十二萬三千四百五十六元整
console.log(numberToChinese(100200300)); // 一億零二十萬零三百元整
console.log(numberToChinese(200000001)); // 二億零一元整



    // 4) 依支出項目顯示欄位 (W活動費用 / X獎學金 / Y經濟扶助 / Z其他)
    function updateConditionalFields(){
      const item = document.getElementById('支出項目').value;
      const blocks = ["活動費用欄位","獎學金欄位","經濟扶助欄位","其他欄位"];
      blocks.forEach(id=>{
        const el = document.getElementById(id);
        el.style.display = 'none';
        Array.from(el.querySelectorAll('input,select,textarea')).forEach(ctrl=>{
          ctrl.removeAttribute('required');
          if(ctrl.type==='checkbox') ctrl.checked=false;
          else ctrl.value='';
        });
      });
      if(item === 'W活動費用'){
        document.getElementById("活動費用欄位").style.display = 'block';
        document.getElementById("專案活動").required = true;
        document.getElementById("活動名稱").required = true;
        document.getElementById("專案日期").required = true;
      } else if(item === 'X獎學金'){
        document.getElementById("獎學金欄位").style.display = 'block';
        document.getElementById("獎學金人數").required = true;
        document.getElementById("專案名稱").required = true;
        document.getElementById("主題").required = true;
        document.getElementById("獎學金日期").required = true;
      } else if(item === 'Y經濟扶助'){
        document.getElementById("經濟扶助欄位").style.display = 'block';
        document.getElementById("經濟扶助").required = true;
      } else if(item === 'Z其他'){
        document.getElementById("其他欄位").style.display = 'block';
      }
    }

    // 5) 依支付方式顯示欄位
    function togglePaymentFields(){
      const payWay = document.getElementById('支付方式').value;
      const allFields = ["現金簽收欄位","郵局欄","支票欄位"];
      allFields.forEach(id=>{
        const section = document.getElementById(id);
        section.style.display='none';
        Array.from(section.querySelectorAll('input')).forEach(ctrl=>{
          ctrl.removeAttribute('required'); 
          ctrl.value='';
        });
      });
      if(payWay === '現金'){
        document.getElementById('現金簽收欄位').style.display='block';
        document.getElementById('簽收金額').required=true;
        document.getElementById('簽收人').required=true;
        document.getElementById('簽收日').required=true;
      } else if(payWay === '轉帳' || payWay === '匯款' || payWay === '劃撥'){
        document.getElementById('郵局欄').style.display='block';
        document.getElementById('銀行').required=true;
        document.getElementById('transferBankBranch').required=true;
        document.getElementById('transferAccountName').required=true;
        document.getElementById('transferAccountNumber').required=true;
      } else if(payWay === '支票'){
        document.getElementById('支票欄位').style.display='block';
        document.getElementById('票號').required=true;
        document.getElementById('到期日').required=true;
        document.getElementById('付款金額').required=true;
      }
    }

    // 6) Modal 顯示/關閉
    function showSuccessModal(){ 
      document.getElementById('successModal').style.display='flex'; 
    }
    function closeSuccessModal(){ 
      document.getElementById('successModal').style.display='none'; 
    }

    // 7) 表單提交：先利用內建檢查，再執行自訂防呆檢查
    document.getElementById('paymentForm').addEventListener('submit',function(e){
      // 利用瀏覽器內建驗證
      if(!this.checkValidity()){
        this.reportValidity();
        e.preventDefault();
        return;
      }
      // 自訂檢查 dropdown（雖然 required 屬性已檢查，但可以額外提示）
      var expenseSelect = document.getElementById('支出項目');
      var paySelect = document.getElementById('支付方式');
      if(expenseSelect.value === ""){
        alert("請選擇支出項目");
        expenseSelect.focus();
        e.preventDefault();
        return;
      }
      if(paySelect.value === ""){
        alert("請選擇支付方式");
        paySelect.focus();
        e.preventDefault();
        return;
      }
      e.preventDefault();
      doFinalSubmit(new FormData(this));
    });

    function doFinalSubmit(formData){
      const submitBtn = document.getElementById('submitBtn');
      if(submitBtn){
        submitBtn.disabled = true;
        submitBtn.textContent = "提交中...";
      }
      fetch(document.getElementById('paymentForm').action, {
        method: 'POST', 
        body: formData
      })
      .then(res => res.text())
      .then(data => {
        if(data.indexOf('表單提交成功') !== -1){
          showSuccessModal();
          finalizeRecipientCode();
          document.getElementById('paymentForm').reset();
          // 重新初始化欄位與日期
          const newCode = loadOrCreateRecipientCode();
          displayRecipientCode(newCode);
          setDateConstraints();
          updateConditionalFields();
          togglePaymentFields();
        } else {
          alert(data);
        }
        if(submitBtn){
          submitBtn.disabled = false;
          submitBtn.textContent = "提交表單";
        }
      })
      .catch(err => {
        alert('提交失敗，請稍後再試。\n' + err);
        if(submitBtn){
          submitBtn.disabled = false;
          submitBtn.textContent = "提交表單";
        }
      });
    }

    // 8) onload 初始化
    window.onload = function(){
      const newCode = loadOrCreateRecipientCode();
      displayRecipientCode(newCode);
      setDateConstraints();
      updateConditionalFields();
      togglePaymentFields();
    };
  </script>
</body> 
</html>

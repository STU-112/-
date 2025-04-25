<?php 
session_start();                               // 啟動 Session（必須先啟動才能讀取登入資訊）
// ─── 登入驗證 ───
if (!isset($_SESSION['帳號'])) {               // 若 Session 裡沒有「帳號」→尚未登入
    header("Location: 0228html.php");          // 轉向登入頁面
    exit;                                      // 結束後續程式
}
// 取得目前登入者帳號
$current_user = $_SESSION['帳號'];             // 將登入帳號存入變數

// ─── 資料庫連線參數 ───
$servername = "localhost:3307";                // 主機位置與自訂埠號
$username   = "root";                          // 資料庫使用者
$password   = "3307";                          // 使用者密碼
$dbname     = "基金會";                         // 資料庫名稱

// 建立資料庫連線
$連接 = new mysqli($servername, $username, $password, $dbname); // 建構 mysqli 物件

// 檢查連線是否成功
if ($連接->connect_error) {                    // 若連線錯誤
    die("資料庫連接失敗: " . $連接->connect_error); // 顯示錯誤並終止
}

/* 下面抓「員工編號」讓表單自動帶入 */
$sql  = "SELECT 員工編號 FROM 註冊資料表 WHERE 帳號 = ?"; // 預備 SQL
$stmt = $連接->prepare($sql);                  // 建立預備敘述
$stmt->bind_param("s", $current_user);         // 綁定登入帳號
$stmt->execute();                              // 執行查詢
$stmt->bind_result($員工編號);                 // 將結果綁到變數
$stmt->fetch();                                // 抓取一筆資料
$stmt->close();                                // 關閉 stmt
$連接->close();                                // 關閉資料庫連線
?>

<!DOCTYPE html> <!-- 宣告 HTML5 文件 -->
<html lang="zh-Hant"> <!-- 設定語系為繁體中文 -->
<head>
  <meta charset="UTF-8" /> <!-- 編碼為 UTF-8 -->
  <title>預支表單</title> <!-- 頁面標題 -->

  <!-- 以下 CSS 僅負責樣式，依使用者需求保留原樣 -->
  <style>
    /* ……（CSS 區保持不變，已省略說明）…… */
  </style>
</head>
<body> <!-- 網頁主體開始 -->
  <div class="container"><!-- 主要卡片容器 -->
    <h1>財團法人台北市失親兒福利基金會</h1><!-- 表頭標題 -->
    <!-- 表單：使用 POST 提交，檔案上傳需設定 enctype -->
    <form id="paymentForm" action="7.php" method="POST" enctype="multipart/form-data">
      <!-- ── 填表人 ── -->
      <div class="form-group"><!-- 填表人欄位容器 -->
        <label for="填表人">填表人：</label><!-- 標籤 -->
        <input type="text" id="填表人" name="填表人" 
               value="<?php echo htmlspecialchars($員工編號); ?>" 
               readonly><!-- 以員工編號自動帶入，唯讀 -->
      </div>
      <!-- ── 受款人 ── -->
      <div class="form-group"><!-- 受款人容器 -->
        <label><span id="recipientName"></span>受款人：</label><!-- 即時顯示代號 -->
        <div id="recipientRow"><!-- 受款人欄位行 -->
          <input type="hidden" id="受款人" name="受款人"><!-- 隱藏受款人代號 -->
          <input type="text" id="受款人姓名" name="受款人姓名" 
                 placeholder="請輸入受款人姓名" required><!-- 受款人姓名 -->
        </div>
      </div>
      <!-- ── 手機號碼 ── -->
      <div class="form-group">
        <label for="手機號碼">手機號碼(受款人)：</label>
        <input type="text" id="手機號碼" name="手機號碼" 
               placeholder="請輸入手機號碼" required>
      </div>
      <!-- ── 地址 ── -->
      <div class="form-group">
        <label for="地址">地址(受款人)：</label>
        <input type="text" id="地址" name="地址" 
               placeholder="請輸入地址" required>
      </div>
      <!-- ── 填表日期 ── -->
      <div class="form-group">
        <label for="填表日期">填表日期：</label>
        <input type="date" id="填表日期" name="填表日期" required readonly>
      </div>
      <!-- ── 付款日期 ── -->
      <div class="form-group">
        <label for="付款日期">付款日期：</label>
        <input type="date" id="付款日期" name="付款日期" required>
      </div>
      <!-- ── 支出項目 ── -->
      <div class="form-group">
        <label for="支出項目">支出項目：</label>
        <select id="支出項目" name="支出項目" required 
                onchange="updateConditionalFields()"><!-- 變動時顯示不同欄位 -->
          <option value="">請選擇</option>
          <option value="W活動費用">W活動費用</option>
          <option value="X獎學金">X獎學金</option>
          <option value="Y經濟扶助">Y經濟扶助</option>
          <option value="Z其他">Z其他</option>
        </select>
      </div>

      <!-- ───────── 依支出項目出現的子欄位 ───────── -->
      <div id="活動費用欄位" class="conditional-group"><!-- (a) 活動費用 -->
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

      <div id="獎學金欄位" class="conditional-group"><!-- (b) 獎學金 -->
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

      <div id="經濟扶助欄位" class="conditional-group"><!-- (c) 經濟扶助 -->
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

      <div id="其他欄位" class="conditional-group"><!-- (d) 其他 -->
        <div class="form-group">
          <label>其他項目：</label>
          <div class="others-checkboxes"><!-- 多選核取方塊 -->
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
      <!-- ── 說明 ── -->
      <div class="form-group">
        <label for="說明">說明：</label>
        <textarea id="說明" name="說明" rows="3" required></textarea>
      </div>
      <!-- ── 支付方式 ── -->
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

      <!-- ── 現金簽收欄位 ── -->
      <div id="現金簽收欄位" class="conditional-group">
        <div class="form-group">
          <label for="簽收日">簽收日：</label>
          <input type="date" id="簽收日" name="簽收日">
        </div>
      </div>
      <!-- ── 轉帳 / 匯款 / 劃撥 ── -->
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
      <!-- ── 支票欄位 ── -->
      <div id="支票欄位" class="conditional-group">
        <div class="form-group">
          <label for="票號">票號：</label>
          <input type="text" id="票號" name="票號" placeholder="請輸入票號">
        </div>
        <div class="form-group">
          <label for="到期日">到期日：</label>
          <input type="date" id="到期日" name="到期日">
        </div>
      </div>
      <!-- ── 金額 ── -->
      <div class="form-group">
        <label for="國字金額">金額：</label>
        <input type="number" id="國字金額" name="國字金額" 
               min="0" required oninput="convertAmountToChinese()"><!-- 金額數字 -->
        <input type="hidden" id="國字金額_hidden" name="國字金額_hidden"><!-- 中文金額隱藏欄 -->
        <div id="國字金額_display"></div><!-- 顯示中文金額 -->
      </div>
      <!-- ── 提交按鈕 ── -->
      <div class="btn-row">
        <button type="submit" id="submitBtn">提交表單</button>
      </div>
    </form>
  </div>

  <!-- ── 提交成功 Modal ── -->
  <div id="successModal" class="modal"><!-- 遮罩 -->
    <div class="modal-content"><!-- 彈窗內容 -->
      <h2>表單提交成功！</h2>
      <p>您的表單已成功提交，我們將盡快處理。</p>
      <div class="modal-buttons"><!-- 按鈕區 -->
        <button onclick="closeSuccessModal()">確定</button>
      </div>
    </div>
  </div>

  <script> // ───────── 前端腳本開始 ─────────
    // 1) 產生或載入受款人代號（格式 Rxx）
    function loadOrCreateRecipientCode(){       // 取得現行或新建代號
      let currentCode = localStorage.getItem("currentCode"); // 先看有無暫存
      if(currentCode) return currentCode;       // 有暫存直接回傳
      let lastNo = parseInt(localStorage.getItem("recipientNo") || "0", 10); // 取得最後編號
      const newNo = lastNo + 1;                 // +1 生成新號碼
      const code = 'R' + String(newNo).padStart(2, '0'); // 組成 Rxx
      localStorage.setItem("currentCode", code); // 存暫存
      return code;                              // 回傳代號
    }
    function displayRecipientCode(code){        // 將代號顯示在頁面
      document.getElementById('recipientName').textContent = code; // 標籤顯示
      document.getElementById('受款人').value = code;  // 隱藏欄位賦值
    }
    function finalizeRecipientCode(){           // 表單送出後確認代號
      const code = localStorage.getItem("currentCode"); // 取暫存
      if(code){
        const numPart = parseInt(code.replace('R',''), 10) || 0; // 抽出數字
        localStorage.setItem("recipientNo", numPart); // 更新最後編號
        localStorage.removeItem("currentCode");       // 清除暫存
      }
    }
    // 2) 填表日期設定為今日；付款日不得早於今日
    function setDateConstraints(){
      const now = new Date();                   // 取今日時間
      const y = now.getFullYear();              // 年
      const m = String(now.getMonth()+1).padStart(2, '0'); // 月
      const d = String(now.getDate()).padStart(2, '0');    // 日
      const todayStr = `${y}-${m}-${d}`;        // yyyy-mm-dd
      document.getElementById('填表日期').value = todayStr; // 填表日期預設
      document.getElementById('付款日期').setAttribute('min', todayStr); // 付款不得早於今日
    }
    // 3) 金額數字 → 中文大寫
    function convertAmountToChinese(){
      const amt = document.getElementById('國字金額').value; // 讀取數值
      const disp = document.getElementById('國字金額_display'); // 顯示區
      const hid = document.getElementById('國字金額_hidden');   // 隱藏欄
      if(!amt || parseFloat(amt) <= 0){        // 若未填或≤0
        disp.textContent = ''; hid.value = ''; return;          // 清空
      }
      const val = parseInt(amt, 10);           // 整數化
      const cn = numberToChinese(val);         // 轉中文
      disp.textContent = cn + '元整';          // 顯示
      hid.value = cn + '元整';                 // 隱藏欄存值
    }
    function numberToChinese(num){             // 阿拉伯數字轉中文
      if(num === 0) return "零";
      const digits = "零一二三四五六七八九";
      const units = ["","十","百","千"];
      const bigUnits = ["","萬","億","兆"];
      let str = num.toString(), result = "", zero = false;
      const groups = [];
      while(str.length > 0){                   // 每四位一組
        groups.unshift(str.slice(-4));
        str = str.slice(0, -4);
      }
      groups.forEach((group, idx) => {         // 逐組轉換
        let groupRes = "", isZeroGroup = true;
        for(let i=0; i<group.length; i++){
          const n = parseInt(group[i]);
          const pos = group.length - i - 1;
          if(n !== 0){
            if(zero){ groupRes += digits[0]; zero=false; }
            groupRes += digits[n] + units[pos];
            isZeroGroup = false;
          } else {
            if(pos!==0) zero=true;
          }
        }
        if(!isZeroGroup){
          groupRes += bigUnits[groups.length-idx-1];
        } else if(idx===groups.length-1){
          groupRes += bigUnits[groups.length-idx-1];
        }
        result += groupRes;
      });
      result = result.replace(/零+/g, "零").replace(/零$/, "");
      return result;
    }
    // 4) 依「支出項目」顯示不同附屬欄位
    function updateConditionalFields(){
      const item = document.getElementById('支出項目').value; // 目前選項
      const blocks = ["活動費用欄位","獎學金欄位","經濟扶助欄位","其他欄位"]; // 所有區塊
      blocks.forEach(id=>{
        const el = document.getElementById(id); // 取元素
        el.style.display = 'none';             // 預設隱藏
        Array.from(el.querySelectorAll('input,select,textarea')).forEach(ctrl=>{
          ctrl.removeAttribute('required');    // 移除必填
          if(ctrl.type==='checkbox') ctrl.checked=false; // 勾選重置
          else ctrl.value='';                 // 文字重置
        });
      });
      if(item === 'W活動費用'){                // 顯示活動費用欄
        document.getElementById("活動費用欄位").style.display = 'block';
        document.getElementById("專案活動").required = true;
        document.getElementById("活動名稱").required = true;
        document.getElementById("專案日期").required = true;
      } else if(item === 'X獎學金'){           // 顯示獎學金欄
        document.getElementById("獎學金欄位").style.display = 'block';
        document.getElementById("獎學金人數").required = true;
        document.getElementById("專案名稱").required = true;
        document.getElementById("主題").required = true;
        document.getElementById("獎學金日期").required = true;
      } else if(item === 'Y經濟扶助'){         // 顯示經濟扶助欄
        document.getElementById("經濟扶助欄位").style.display = 'block';
        document.getElementById("經濟扶助").required = true;
      } else if(item === 'Z其他'){              // 顯示其他欄
        document.getElementById("其他欄位").style.display = 'block';
      }
    }
    // 5) 依「支付方式」顯示對應欄位
    function togglePaymentFields(){
      const payWay = document.getElementById('支付方式').value; // 取支付方式
      const allFields = ["現金簽收欄位","郵局欄","支票欄位"]; // 所有區塊
      allFields.forEach(id=>{
        const section = document.getElementById(id); // 元素
        section.style.display='none';              // 先隱藏
        Array.from(section.querySelectorAll('input')).forEach(ctrl=>{
          ctrl.removeAttribute('required');        // 移除必填
          ctrl.value='';                           // 清空
        });
      });
      if(payWay === '現金'){                       // 顯示現金簽收
        document.getElementById('現金簽收欄位').style.display='block';
        document.getElementById('簽收日').required=true;
      } else if(payWay === '轉帳' || payWay === '匯款' || payWay === '劃撥'){
        document.getElementById('郵局欄').style.display='block';
        document.getElementById('銀行').required=true;
        document.getElementById('transferBankBranch').required=true;
        document.getElementById('transferAccountName').required=true;
        document.getElementById('transferAccountNumber').required=true;
      } else if(payWay === '支票'){               // 顯示支票欄
        document.getElementById('支票欄位').style.display='block';
        document.getElementById('票號').required=true;
        document.getElementById('到期日').required=true;
      }
    }
    // 6) Modal 開關函式
    function showSuccessModal(){                // 顯示成功彈窗
      document.getElementById('successModal').style.display='flex';
    }
    function closeSuccessModal(){               // 關閉彈窗
      document.getElementById('successModal').style.display='none';
    }
    // 7) 表單提交流程
    document.getElementById('paymentForm').addEventListener('submit',function(e){
      if(!this.checkValidity()){                // 利用瀏覽器原生驗證
        this.reportValidity(); e.preventDefault(); return;
      }
      var expenseSelect = document.getElementById('支出項目'); // 支出項目
      var paySelect = document.getElementById('支付方式');     // 支付方式
      if(expenseSelect.value === ""){           // 未選支出項目
        alert("請選擇支出項目"); expenseSelect.focus(); e.preventDefault(); return;
      }
      if(paySelect.value === ""){               // 未選支付方式
        alert("請選擇支付方式"); paySelect.focus(); e.preventDefault(); return;
      }
      e.preventDefault();                       // 攔截預設提交
      doFinalSubmit(new FormData(this));        // 改由 fetch 非同步上傳
    });
    // 真正送出表單
    function doFinalSubmit(formData){
      const submitBtn = document.getElementById('submitBtn'); // 送出按鈕
      if(submitBtn){ submitBtn.disabled = true; submitBtn.textContent = "提交中..."; }
      fetch(document.getElementById('paymentForm').action, {
        method: 'POST', body: formData          // 送至 7.php
      })
      .then(res => res.text())                  // 取文字回應
      .then(data => {
        if(data.indexOf('表單提交成功') !== -1){ // 若成功訊息存在
          showSuccessModal();                   // 顯示成功彈窗
          finalizeRecipientCode();              // finalized 代號
          document.getElementById('paymentForm').reset(); // 清表單
          // 重新初始化
          const newCode = loadOrCreateRecipientCode();
          displayRecipientCode(newCode);
          setDateConstraints();
          updateConditionalFields();
          togglePaymentFields();
        } else { alert(data); }                 // 失敗顯示回傳訊息
        if(submitBtn){ submitBtn.disabled = false; submitBtn.textContent = "提交表單"; }
      })
      .catch(err => {                           // 例外處理
        alert('提交失敗，請稍後再試。\n' + err);
        if(submitBtn){ submitBtn.disabled = false; submitBtn.textContent = "提交表單"; }
      });
    }
    // 8) 網頁載入時初始化
    window.onload = function(){
      const newCode = loadOrCreateRecipientCode(); // 建立受款人代號
      displayRecipientCode(newCode);             // 顯示代號
      setDateConstraints();                      // 設定日期限制
      updateConditionalFields();                 // 預設隱藏/顯示欄位
      togglePaymentFields();                     // 預設隱藏/顯示支付欄位
    };
  </script>
</body> <!-- 網頁主體結束 -->
</html>
